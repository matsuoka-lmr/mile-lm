<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class History extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'history';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'device_id', 'time', 'lat', 'lng', 'dist',
    ];

    protected $casts = [
        'time' => 'datetime',
    ];

    /**
     * Get the company associated with the device.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
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
