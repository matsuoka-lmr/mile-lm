<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Log;

class CheckMongoConnection extends Command
{
    protected $signature = 'check:mongo';
    protected $description = 'Check MongoDB connection and fetch a user.';

    public function handle()
    {
        Log::info('Attempting to connect to MongoDB and fetch a user...');
        try {
            $user = User::first(); // 任意のユーザーを取得
            if ($user) {
                Log::info('Successfully fetched a user from MongoDB: ' . $user->email);
            } else {
                Log::warning('No users found in MongoDB.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to connect to MongoDB or fetch user: ' . $e->getMessage());
        }
        Log::info('MongoDB connection check completed.');
    }
}
