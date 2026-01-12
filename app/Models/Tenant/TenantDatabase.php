<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantDatabase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'database_name',
        'database_host',
        'database_port',
        'database_username',
        'database_password',
        'database_prefix',
        'status',
        'last_error',
    ];

    protected $casts = [
        'database_port' => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
