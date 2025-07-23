<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Trackimo;
use Illuminate\Support\Facades\Log;

class TrackimoAuthTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trackimo:test-auth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Trackimo API authentication.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Attempting to test Trackimo API authentication...');

        try {
            $trackimoService = app(Trackimo::class);
            $devices = $trackimoService->getDevices();
            $this->info('Successfully authenticated with Trackimo API.');
            $this->info('Number of devices found: ' . count($devices));
            Log::info('TrackimoAuthTest: Successfully authenticated and fetched devices.', ['devices_count' => count($devices)]);
        } catch (\Exception $e) {
            $this->error('Failed to authenticate with Trackimo API: ' . $e->getMessage());
            Log::error('TrackimoAuthTest: Failed to authenticate.', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }

        return 0;
    }
}
