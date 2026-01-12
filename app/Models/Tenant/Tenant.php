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
        'slug',
        'status',
        'plan',
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
