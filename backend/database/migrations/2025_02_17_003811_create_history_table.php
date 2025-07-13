<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('history', function (Blueprint $table) {
            $table->comment('デバイス位置履歴');
            $table->id()->comment('デバイス位置履歴ID');
            $table->foreignId('device_id')->nullable()->constrained('device')->comment('デバイスID');
            $table->dateTime('time')->nullable()->comment('測位日時');
            $table->double('lat')->comment('緯度');
            $table->double('lng')->comment('経度');
            $table->double('dist')->comment('距離(m)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history');
    }
};
