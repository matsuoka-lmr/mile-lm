<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Device extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'devices';

    protected $primaryKey = '_id';
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'measure_interval'
    ];

    protected $casts = [
        'last_loc_at' => 'datetime',
        'last_measure_at' => 'datetime',
    ];

    /**
     * Get the company associated with the device.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }


    /**
     * Get the vehicle associated with the device.
     */
    public function vehicle(): HasOne
    {
        return $this->hasOne(Vehicle::class, 'device_id', '_id');
    }

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y/m/d H:m:s');
    }
}
