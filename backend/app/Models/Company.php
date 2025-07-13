<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'company';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'manage_company_id',
        'manage_user_id',
        'name',
        'phone',
        'address',
        'memo',
        'oil_notice_days',
        'oil_notice_mileage',
        'tire_notice_days',
        'tire_notice_mileage',
        'battery_notice_days',
        'battery_notice_mileage'
    ];

    /**
     * Get users.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }


    /**
     * Get managedcompanies.
     */
    public function managedcompanies(): HasMany
    {
        return $this->hasMany(Company::class, 'manage_company_id');
    }

    /**
     * Get the Company in charge of.
     */
    public function managecompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'manage_company_id');
    }


    /**
     * Get the User in charge of.
     */
    public function manageuser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manage_user_id');
    }
}
