<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'vehicle';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'device_id',
        'user_id',
        'model',
        'number',
        'inspection_date',
        'emails',
        'oil_date',
        'oil_mileage',
        'tire_date',
        'tire_mileage',
        'battery_date',
        'battery_mileage',
        'oil_notice_days',
        'oil_notice_mileage',
        'tire_notice_days',
        'tire_notice_mileage',
        'battery_notice_days',
        'battery_notice_mileage'
    ];

    protected $casts = [
        'attach_at' => 'datetime',
        'device_id' => 'integer',
        'oil_mileage' => 'float',
        'tire_mileage' => 'float',
        'battery_mileage' => 'float',
    ];


    /**
     * Get the company associated with the device.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the device associated with the vehicle.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id', '_id')->withCasts(['_id' => 'string']);
    }

    /**
     * Get the user associated with the vehicle.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
