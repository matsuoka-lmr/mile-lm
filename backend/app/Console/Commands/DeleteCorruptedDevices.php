<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Device;
use Illuminate\Support\Facades\DB;

class DeleteCorruptedDevices extends Command
{
    protected $signature = 'device:delete-corrupted';
    protected $description = 'Delete devices with non-numeric or null IDs.';

    public function handle()
    {
        $this->info('Starting deletion of corrupted device data...');

        // 1. Delete devices where ID is not a number
        $nonNumericDeleted = Device::whereRaw(['$where' => '!/^[0-9]+$/.test(this.id)'])->delete();
        $this->info("Deleted {$nonNumericDeleted} devices with non-numeric IDs.");

        // 2. Delete devices where ID is null
        $nullIdDeleted = Device::whereNull('id')->delete();
        $this->info("Deleted {$nullIdDeleted} devices with null IDs.");

        $this->info('Corrupted device data deletion complete.');
    }
}
