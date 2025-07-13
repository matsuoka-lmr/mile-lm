<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::withoutForeignKeyConstraints(function () {
            Schema::create('company', function (Blueprint $table) {
                $table->comment('会社');
                $table->id()->comment('会社ID');
                $table->tinyInteger('type')->comment('会社種別(0:マモエル, 1:タイヤショップ, 2:顧客)');
                $table->foreignId('manage_company_id')->nullable()->constrained('company')->comment('管理会社の会社ID');
                $table->foreignId('manage_user_id')->nullable()->constrained('user')->comment('管理会社の担当ユーザーID');
                $table->string('name')->comment('会社名');
                $table->string('email')->nullable()->comment('メールアドレス');
                $table->string('phone')->nullable()->comment('電話番号');
                $table->string('address')->nullable()->comment('住所');
                $table->string('memo')->nullable()->comment('備考');
                $table->integer('oil_notice_days')->nullable()->comment('オイル交換通知目安(日数)');
                $table->integer('oil_notice_mileage')->nullable()->comment('オイル交換通知目安(km)');
                $table->integer('tire_notice_days')->nullable()->comment('タイヤ交換通知目安(日数)');
                $table->integer('tire_notice_mileage')->nullable()->comment('タイヤ交換通知目安(km)');
                $table->integer('battery_notice_days')->nullable()->comment('バッテリー交換通知目安(日数)');
                $table->integer('battery_notice_mileage')->nullable()->comment('バッテリー交換通知目安(km)');
                $table->timestamps();
                $table->tinyInteger('status')->default(0)->comment('状態(0:通常, 9:削除済)');
            });

            Schema::create('user', function (Blueprint $table) {
                $table->comment('ユーザー');
                $table->id()->comment('ユーザーID');
                $table->foreignId('company_id')->nullable()->constrained('company')->comment('所属会社ID');
                $table->tinyInteger('role')->comment('ロール(1:マモエルユーザー, 9:マモエル管理者, 11:タイヤショップユーザー, 19:タイヤショップ管理者, 21:顧客ユーザー, 29:顧客管理者, 99:システム管理者)');
                $table->string('email')->unique()->comment('メールアドレス');
                $table->string('name')->comment('名前');
                $table->string('password')->comment('ログイン暗証番号(HASH値)');
                $table->string('phone')->nullable()->comment('電話番号');
                $table->dateTime('last_login_at')->nullable()->comment('最終ログイン日時');
                $table->timestamps();
                $table->tinyInteger('status')->default(0)->comment('状態(0:通常, 9:削除済)');
            });

            Schema::create('device', function (Blueprint $table) {
                $table->comment('デバイス');
                $table->unsignedInteger('id')->primary()->comment('デバイスID');
                $table->foreignId('company_id')->nullable()->constrained('company')->comment('会社ID(タイヤ会社)');
                $table->string('name')->comment('デバイス名');
                $table->integer('battery')->comment('バッテリー');
                $table->double('lat')->comment('最終位置緯度');
                $table->double('lng')->comment('最終位置経度');
                $table->dateTime('last_loc_at')->comment('最終位置日時');
                $table->integer('settings_id')->comment('TrackimoSettingsID');
                $table->integer('minimal_interval')->comment('最小測位間隔(秒)');
                $table->integer('measure_interval')->comment('測位間隔(秒)');
                $table->dateTime('last_measure_at')->nullable()->comment('最終履歴更新日時');
                $table->timestamps();
                $table->string('status')->nullable()->comment('状態');
            });

            Schema::create('vehicle', function (Blueprint $table) {
                $table->comment('車両');
                $table->id()->comment('車両ID');
                $table->foreignId('company_id')->constrained('company')->comment('会社ID(顧客)');
                $table->foreignId('device_id')->nullable()->constrained('device')->comment('デバイスID');
                $table->foreignId('user_id')->nullable()->constrained('user')->comment('担当者ID');
                $table->dateTime('attach_at')->nullable()->comment('GPS装着日時');
                $table->string('model')->comment('車種');
                $table->string('number')->comment('車両ナンバー');
                $table->date('inspection_date')->nullable()->comment('車検日');
                $table->string('emails')->nullable()->comment('通知メールアドレス(カンマ区切り)※1フェーズのみ利用');
                $table->dateTime('oil_date')->nullable()->comment('オイル交換日');
                $table->double('oil_mileage')->comment('オイル交換後走行距離(km)');
                $table->dateTime('tire_date')->nullable()->comment('タイヤ交換日');
                $table->double('tire_mileage')->comment('タイヤ交換後走行距離(km)');
                $table->dateTime('battery_date')->nullable()->comment('バッテリー交換日');
                $table->double('battery_mileage')->comment('バッテリー交換後走行距離(km)');
                $table->integer('oil_notice_days')->nullable()->comment('オイル交換通知目安(日数)');
                $table->integer('oil_notice_mileage')->nullable()->comment('オイル交換通知目安(km)');
                $table->integer('tire_notice_days')->nullable()->comment('タイヤ交換通知目安(日数)');
                $table->integer('tire_notice_mileage')->nullable()->comment('タイヤ交換通知目安(km)');
                $table->integer('battery_notice_days')->nullable()->comment('バッテリー交換通知目安(日数)');
                $table->integer('battery_notice_mileage')->nullable()->comment('バッテリー交換通知目安(km)');
                $table->timestamps();
                $table->tinyInteger('status')->default(0)->comment('状態(0:通常, 9:削除済)');
            });
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::withoutForeignKeyConstraints(function () {
            Schema::dropIfExists('vehicle');
            Schema::dropIfExists('device');
            Schema::dropIfExists('user');
            Schema::dropIfExists('company');
        // });
    }
}
