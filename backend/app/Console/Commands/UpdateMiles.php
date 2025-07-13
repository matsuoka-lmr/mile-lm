<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Trackimo;
use Location\Coordinate;
use Location\Distance\Vincenty;
use App\Models\Device;
use App\Models\History;
use App\Models\Vehicle;
use Log;
use Illuminate\Support\Carbon;

class UpdateMiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Call external API and update miles';

    private function updateHistories(Vehicle $vehicle) {
        $device = $vehicle->device;
        if (!$device) {
            throw new \Exception("Invalid device: not exsits!(id:{$vehicle->device_id})");
        }
        if ($device->status == 'deleted') return;
        $oil = strtotime($vehicle->oil_date);
        $tire = strtotime($vehicle->tire_date);
        $battery = strtotime($vehicle->battery_date);
        $oil_meter = empty($vehicle->oil_mileage) ? 0.0 : $vehicle->oil_mileage * 1000.0;
        $tire_meter = empty($vehicle->tire_mileage) ? 0.0 : $vehicle->tire_mileage * 1000.0;
        $battery_meter = empty($vehicle->battery_mileage) ? 0.0 : $vehicle->battery_mileage * 1000.0;
        $lastHist = History::where('device_id', $device->id)->latest('time')->first();
        $attach_at = $vehicle->attach_at->getTimestamp();
        $from = (empty($device->last_measure_at) ? (empty($lastHist) ? $device->created_at : $lastHist->time) : $device->last_measure_at)->getTimestamp();

        assert(!empty($from));

        $count = 0;
        $now = Carbon::now();
        $calc = new Vincenty();
        $lastLoc = empty($lastHist) ? false : new Coordinate($lastHist->lat, $lastHist->lng);

        $time = 0;
        $trackimo = app(Trackimo::class);
        do {
            $count = 0;
            foreach($trackimo->getHistories($device->id, $from, $now->getTimestamp()) as $detail) {
                $count++;
                $loc = new Coordinate($detail['lat'], $detail['lng']);
                $dist = empty($lastLoc) ? 0 : $calc->getDistance($lastLoc, $loc);
                $lastLoc = $loc;

                $time = $detail['time'];
                $hist = new History([
                    'device_id' => $device->id,
                    'time' => Carbon::createFromTimestampUTC($time),
                    'lat' => $detail['lat'],
                    'lng' => $detail['lng'],
                    'dist' => $dist
                ]);
                $hist->save();

                if ($time >= $from) $from = $time + 1;
                if ($time > $attach_at) {
                    if ($oil && ($time > $oil)) $oil_meter += $dist;
                    if ($tire && ($time > $tire)) $tire_meter += $dist;
                    if ($battery && ($time > $battery)) $battery_meter += $dist;
                }
            }
        } while ($count);
        if ($oil && ($time > $oil)) $vehicle->oil_mileage = $oil_meter / 1000;
        if ($tire && ($time > $tire)) $vehicle->tire_mileage = $tire_meter / 1000;
        if ($battery && ($time > $battery)) $vehicle->battery_mileage = $battery_meter / 1000;
        $device->last_measure_at = $now;
        $device->save();
        $vehicle->save();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        Log::debug("[Batch]UpdateMiles started.");
        $vehicles = Vehicle::whereNotNull('device_id')->get();
        foreach($vehicles as $vehicle) try {
            $this->updateHistories($vehicle);
        } catch (\Throwable $e) {
            Log::error("updateHistories failed!(vehicle:{$vehicle->id}, device:{$vehicle->device_id})");
            Log::debug($e);
        }
    }
}
