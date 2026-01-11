<?php

namespace MockupSoft\Companies\Repositories;

use Webkul\Core\Eloquent\Repository;

class CompanyRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'MockupSoft\Companies\Models\Company';
    }
}
