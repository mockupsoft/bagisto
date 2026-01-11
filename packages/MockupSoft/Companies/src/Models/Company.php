<?php

namespace MockupSoft\Companies\Models;

use Illuminate\Database\Eloquent\Model;
use MockupSoft\Companies\Contracts\Company as CompanyContract;

class Company extends Model implements CompanyContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'companies';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
    ];
}
