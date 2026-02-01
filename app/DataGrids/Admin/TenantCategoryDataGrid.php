<?php

namespace App\DataGrids\Admin;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class TenantCategoryDataGrid extends DataGrid
{
    /**
     * Primary column.
     *
     * @var string
     */
    protected $primaryColumn = 'category_id';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        try {
            $conn = DB::connection('tenant');
            
            // Check if categories table exists
            if (! $conn->getSchemaBuilder()->hasTable('categories')) {
                return DB::connection('tenant')->table('categories')->whereRaw('1 = 0');
            }

            $hasCategoryTranslations = $conn->getSchemaBuilder()->hasTable('category_translations');

            $queryBuilder = DB::connection('tenant')->table('categories')
                ->select(
                    'categories.id as category_id',
                    'categories.position',
                    'categories.status',
                );

            if ($hasCategoryTranslations) {
                $locale = app()->getLocale() ?: 'en';
                $queryBuilder->leftJoin('category_translations', function ($join) use ($locale) {
                    $join->on('categories.id', '=', 'category_translations.category_id')
                        ->where('category_translations.locale', '=', $locale);
                })
                ->addSelect('category_translations.name')
                ->addSelect(DB::raw("COALESCE(category_translations.locale, '') as locale"));
                $queryBuilder->groupBy('categories.id', 'categories.position', 'categories.status', 'category_translations.name', 'category_translations.locale');
            } else {
                $queryBuilder->addSelect(DB::raw("'' as name"));
                $queryBuilder->addSelect(DB::raw("'' as locale"));
                $queryBuilder->groupBy('categories.id', 'categories.position', 'categories.status');
            }

            $this->addFilter('category_id', 'categories.id');
            $this->addFilter('status', 'categories.status');

            return $queryBuilder;
        } catch (\Exception $e) {
            // If connection fails, return empty query
            report($e);
            return DB::connection('tenant')->table('categories')->whereRaw('1 = 0');
        }
    }

    /**
     * Prepare columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'category_id',
            'label'      => trans('admin::app.catalog.categories.index.datagrid.id'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('admin::app.catalog.categories.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'position',
            'label'      => trans('admin::app.catalog.categories.index.datagrid.position'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'              => 'status',
            'label'              => trans('admin::app.catalog.categories.index.datagrid.status'),
            'type'               => 'boolean',
            'filterable'         => true,
            'filterable_options' => [
                [
                    'label' => trans('admin::app.catalog.categories.index.datagrid.active'),
                    'value' => 1,
                ],
                [
                    'label' => trans('admin::app.catalog.categories.index.datagrid.inactive'),
                    'value' => 0,
                ],
            ],
            'sortable'   => true,
            'closure'    => function ($row) {
                if ($row->status) {
                    return '<span class="badge badge-md badge-success">'.trans('admin::app.catalog.categories.index.datagrid.active').'</span>';
                }

                return '<span class="badge badge-md badge-danger">'.trans('admin::app.catalog.categories.index.datagrid.inactive').'</span>';
            },
        ]);
    }

    /**
     * Prepare actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        // No actions for super admin view
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {
        // No mass actions for super admin view
    }
}
