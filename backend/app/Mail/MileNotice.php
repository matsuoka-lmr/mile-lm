<?php

namespace App\Mail;

use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class MileNotice extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Vehicle $vehicle
    )
    {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【Mile-LM】メンテナンス通知',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $now = Carbon::now()->toImmutable();
        $vehicle = $this->vehicle;
        $customer = $vehicle->company;
        $shop = $customer->managecompany;
        $user = $vehicle->user;

        $oil_notice_days = intval(empty($vehicle->oil_notice_days) ? (empty($customer->oil_notice_days) ? $shop->oil_notice_days : $customer->oil_notice_days ) : $vehicle->oil_notice_days);
        $oil_notice_mileage = intval(empty($vehicle->oil_notice_mileage) ? (empty($customer->oil_notice_mileage) ? $shop->oil_notice_mileage : $customer->oil_notice_mileage ) : $vehicle->oil_notice_mileage);
        $oil_date = empty($vehicle->oil_date) ? null : Carbon::parse($vehicle->oil_date)->toImmutable();
        $oil_notice = empty($oil_date) ? false : ($oil_notice_days > 0 && $now->gte($oil_date->addDays($oil_notice_days))) || ($oil_notice_mileage > 0 && $vehicle->oil_miles >= $oil_notice_mileage);
        $oil_notice_settings = [];
        if ($oil_notice_mileage > 0) array_push($oil_notice_settings, number_format($oil_notice_mileage).'km');
        if ($oil_notice_days > 0) array_push($oil_notice_settings, "前回交換より $oil_notice_days 日");

        $tire_notice_days = intval(empty($vehicle->tire_notice_days) ? (empty($customer->tire_notice_days) ? $shop->tire_notice_days : $customer->tire_notice_days ) : $vehicle->tire_notice_days);
        $tire_notice_mileage = intval(empty($vehicle->tire_notice_mileage) ? (empty($customer->tire_notice_mileage) ? $shop->tire_notice_mileage : $customer->tire_notice_mileage ) : $vehicle->tire_notice_mileage);
        $tire_date = empty($vehicle->tire_date) ? null : Carbon::parse($vehicle->tire_date)->toImmutable();
        $tire_notice = empty($tire_date) ? false : ($tire_notice_days > 0 && $now->gte($tire_date->addDays($tire_notice_days))) || ($tire_notice_mileage > 0 && $vehicle->tire_miles >= $tire_notice_mileage);
        $tire_notice_settings = [];
        if ($tire_notice_mileage > 0) array_push($tire_notice_settings, number_format($tire_notice_mileage).'km');
        if ($tire_notice_days > 0) array_push($tire_notice_settings, "前回交換より $tire_notice_days 日");

        $battery_notice_days = intval(empty($vehicle->battery_notice_days) ? (empty($customer->battery_notice_days) ? $shop->battery_notice_days : $customer->battery_notice_days ) : $vehicle->battery_notice_days);
        $battery_notice_mileage = intval(empty($vehicle->battery_notice_mileage) ? (empty($customer->battery_notice_mileage) ? $shop->battery_notice_mileage : $customer->battery_notice_mileage ) : $vehicle->battery_notice_mileage);
        $battery_date = empty($vehicle->battery_date) ? null : Carbon::parse($vehicle->battery_date)->toImmutable();
        $battery_notice = empty($battery_date) ? false : ($battery_notice_days > 0 && $now->gte($battery_date->addDays($battery_notice_days))) || ($battery_notice_mileage > 0 && $vehicle->battery_miles >= $battery_notice_mileage);
        $battery_notice_settings = [];
        if ($battery_notice_mileage > 0) array_push($battery_notice_settings, number_format($battery_notice_mileage).'km');
        if ($battery_notice_days > 0) array_push($battery_notice_settings, "前回点検より $battery_notice_days 日");

        return new Content(
            text: 'mail.notice',
            with: [
                'vehicle' => $vehicle,
                'customer' => $customer,
                'shop' => $shop,
                'user' => $user,

                'oil_notice_days' => $oil_notice_days,
                'oil_notice_mileage' => $oil_notice_mileage,
                'oil_date' => empty($oil_date) ? '未設定' : $oil_date->format('Y年m月d日'),
                'oil_notice' => $oil_notice,
                'oil_notice_settings' => count($oil_notice_settings) > 0 ? implode(' または ', $oil_notice_settings) : '未設定',

                'tire_notice_days' => $tire_notice_days,
                'tire_notice_mileage' => $tire_notice_mileage,
                'tire_date' => empty($tire_date) ? '未設定' : $tire_date->format('Y年m月d日'),
                'tire_notice' => $tire_notice,
                'tire_notice_settings' => count($tire_notice_settings) > 0 ? implode(' または ', $tire_notice_settings) : '未設定',

                'battery_notice_days' => $battery_notice_days,
                'battery_notice_mileage' => $battery_notice_mileage,
                'battery_date' => empty($battery_date) ? '未設定' : $battery_date->format('Y年m月d日'),
                'battery_notice' => $battery_notice,
                'battery_notice_settings' => count($battery_notice_settings) > 0 ? implode(' または ', $battery_notice_settings) : '未設定',
            ]
        );
    }
}
