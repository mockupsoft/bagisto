<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Domain;
use App\Models\Tenant\TenantDatabase;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'store_name',
        'slug',
        'subdomain',
        'status',
        'plan',
        'provisioning_started_at',
        'provisioning_finished_at',
        'onboarding_completed_at',
        'last_error',
    ];

    protected $casts = [
        'provisioning_started_at' => 'datetime',
        'provisioning_finished_at' => 'datetime',
        'onboarding_completed_at' => 'datetime',
    ];

    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    public function primaryDomain()
    {
        return $this->hasOne(Domain::class)->where('is_primary', true);
    }

    public function database()
    {
        return $this->hasOne(TenantDatabase::class);
    }
}
