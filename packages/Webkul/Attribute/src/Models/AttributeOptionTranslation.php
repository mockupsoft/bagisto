<?php

namespace Webkul\Attribute\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\TenantScopedConnection;
use Webkul\Attribute\Contracts\AttributeOptionTranslation as AttributeOptionTranslationContract;

class AttributeOptionTranslation extends Model implements AttributeOptionTranslationContract
{
    use TenantScopedConnection;

    public $timestamps = false;

    protected $fillable = ['label'];
}
