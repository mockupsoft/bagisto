<?php

namespace MockupSoft\Companies\Repositories;

use Webkul\Core\Eloquent\Repository;

class CompanyRepository extends Repository
{
    /**
     * Specify model class name.
     * Returns Contract class; Concord resolves to actual Model.
     */
    public function model(): string
    {
        return 'MockupSoft\Companies\Contracts\Company';
    }
}
