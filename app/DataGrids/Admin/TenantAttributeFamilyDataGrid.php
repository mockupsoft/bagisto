<?php

namespace App\DataGrids\Admin;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class TenantAttributeFamilyDataGrid extends DataGrid
{
    /**
     * Primary column.
     *
     * @var string
     */
    protected $primaryColumn = 'id';

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        try {
            $conn = DB::connection('tenant');
            $hasAttributeFamilies = $conn->getSchemaBuilder()->hasTable('attribute_families');

            if (! $hasAttributeFamilies) {
                // Return empty query builder if table doesn't exist
                return DB::connection('tenant')->table('attribute_families')->whereRaw('1 = 0');
            }

            $queryBuilder = DB::connection('tenant')->table('attribute_families')
                ->select(
                    'id',
                    'code',
                    'name',
                    'created_at',
                    'updated_at'
                );

            $this->addFilter('id', 'attribute_families.id');
            $this->addFilter('code', 'attribute_families.code');
            $this->addFilter('name', 'attribute_families.name');

            return $queryBuilder;
        } catch (\Exception $e) {
            // If connection fails, return empty query
            report($e);
            return DB::connection('tenant')->table('attribute_families')->whereRaw('1 = 0');
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
            'index'      => 'id',
            'label'      => trans('admin::app.catalog.families.index.datagrid.id'),
            'type'       => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'code',
            'label'      => trans('admin::app.catalog.families.index.datagrid.code'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('admin::app.catalog.families.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'      => 'Created At',
            'type'       => 'datetime',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
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
