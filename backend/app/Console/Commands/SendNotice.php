<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\MileNotice;
use App\Models\Vehicle;
use Carbon\CarbonImmutable;
use Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class SendNotice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check miles and send notice';

    private function checkNotice(Vehicle $vehicle, CarbonImmutable $now) {
        // $inspection_notice_days = env('INSPECTION_NOTICE_DAYS', 60);
        // 車検日チェック?
        // if (!empty($vehicle->inspection_date)) {
        //     if ($now->gte(Carbon::parse($vehicle->inspection_date)->addYears(2)->subDays($inspection_notice_days))) return true;
        // }

        $customer = $vehicle->company;
        $shop = $customer->managecompany;

        if (!empty($vehicle->oil_date)) {
            $oil_notice_days = intval(empty($vehicle->oil_notice_days) ? (empty($customer->oil_notice_days) ? $shop->oil_notice_days : $customer->oil_notice_days ) : $vehicle->oil_notice_days);
            if ($oil_notice_days > 0 && $now->gte(Carbon::parse($vehicle->oil_date)->addDays($oil_notice_days))) return true;
            $oil_notice_mileage = intval(empty($vehicle->oil_notice_mileage) ? (empty($customer->oil_notice_mileage) ? $shop->oil_notice_mileage : $customer->oil_notice_mileage ) : $vehicle->oil_notice_mileage);
            if ($oil_notice_mileage > 0 && $vehicle->oil_miles >= $oil_notice_mileage) return true;
        }

        if (!empty($vehicle->tire_date)) {
            $tire_notice_days = intval(empty($vehicle->tire_notice_days) ? (empty($customer->tire_notice_days) ? $shop->tire_notice_days : $customer->tire_notice_days ) : $vehicle->tire_notice_days);
            if ($tire_notice_days > 0 && $now->gte(Carbon::parse($vehicle->tire_date)->addDays($tire_notice_days))) return true;
            $tire_notice_mileage = intval(empty($vehicle->tire_notice_mileage) ? (empty($customer->tire_notice_mileage) ? $shop->tire_notice_mileage : $customer->tire_notice_mileage ) : $vehicle->tire_notice_mileage);
            if ($tire_notice_mileage > 0 && $vehicle->tire_miles >= $tire_notice_mileage) return true;
        }

        if (!empty($vehicle->battery_date)) {
            $battery_notice_days = intval(empty($vehicle->battery_notice_days) ? (empty($customer->battery_notice_days) ? $shop->battery_notice_days : $customer->battery_notice_days ) : $vehicle->battery_notice_days);
            if ($battery_notice_days > 0 && $now->gte(Carbon::parse($vehicle->battery_date)->addDays($battery_notice_days))) return true;
            $battery_notice_mileage = intval(empty($vehicle->battery_notice_mileage) ? (empty($customer->battery_notice_mileage) ? $shop->battery_notice_mileage : $customer->battery_notice_mileage ) : $vehicle->battery_notice_mileage);
            if ($battery_notice_mileage > 0 && $vehicle->battery_mileage >= $battery_notice_mileage * 1000) return true;
        }

        return false;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        Log::debug("[Batch]SendNotice started.");
        $vehicles = Vehicle::whereNotNull('device_id')->get();
        $now = Carbon::now()->toImmutable();
        foreach($vehicles as $vehicle) try {
            if ($this->checkNotice($vehicle, $now)) {
                $emails = empty($vehicle->emails) ? (empty($vehicle->company->email) ? $vehicle->company->managecompany->email : $vehicle->company->email) : $vehicle->emails;
                if (!empty($emails)) {
                    foreach (explode(',', $emails) as $email) {
                        Mail::to($email)->send(new MileNotice($vehicle));
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error("SendNotice failed!(vehicle:{$vehicle->id}, device:{$vehicle->device_id})");
            Log::debug($e);
        }
    }
}
