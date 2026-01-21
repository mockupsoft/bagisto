<?php

namespace Webkul\Attribute\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TenantScopedConnection;
use Webkul\Attribute\Contracts\AttributeTranslation as AttributeTranslationContract;

class AttributeTranslation extends Model implements AttributeTranslationContract
{
    use TenantScopedConnection;

    public $timestamps = false;

    protected $fillable = ['name'];
}
