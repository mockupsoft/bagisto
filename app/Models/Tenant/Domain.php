<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'domain',
        'type',
        'is_primary',
        'verified_at',
        'verification_token',
        'verification_method',
        'verification_started_at',
        'last_checked_at',
        'last_failure_reason',
        'verification_value',
        'created_by_id',
        'note',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'verified_at' => 'datetime',
        'verification_started_at' => 'datetime',
        'last_checked_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
