<?php

namespace App\DataGrids\Admin;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class TenantAttributeDataGrid extends DataGrid
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
            
            // Check if attributes table exists - wrap in try-catch in case hasTable itself fails
            $hasAttributes = false;
            try {
                $hasAttributes = $conn->getSchemaBuilder()->hasTable('attributes');
            } catch (\Exception $e) {
                // If hasTable check fails, assume table doesn't exist
                $hasAttributes = false;
            }
            
            if (! $hasAttributes) {
                // Table doesn't exist, return empty query using selectRaw with subquery
                // Don't try table('attributes') as it will fail
                return DB::connection('tenant')
                    ->query()
                    ->selectRaw('NULL as id, NULL as code, NULL as admin_name, NULL as type, NULL as is_required, NULL as is_unique, NULL as value_per_locale, NULL as value_per_channel, NULL as created_at')
                    ->from(DB::raw('(SELECT 1) as dummy'))
                    ->whereRaw('1 = 0');
            }

            // Table exists, proceed with normal query
            $queryBuilder = DB::connection('tenant')->table('attributes')
                ->select(
                    'id',
                    'code',
                    'admin_name',
                    'type',
                    'is_required',
                    'is_unique',
                    'value_per_locale',
                    'value_per_channel',
                    'created_at'
                );

            $this->addFilter('id', 'attributes.id');
            $this->addFilter('code', 'attributes.code');
            $this->addFilter('type', 'attributes.type');
            $this->addFilter('is_required', 'attributes.is_required');
            $this->addFilter('is_unique', 'attributes.is_unique');

            return $queryBuilder;
        } catch (\Exception $e) {
            // If connection fails or table doesn't exist, return empty query
            report($e);
            // Return empty query using selectRaw with subquery
            return DB::connection('tenant')
                ->query()
                ->selectRaw('NULL as id, NULL as code, NULL as admin_name, NULL as type, NULL as is_required, NULL as is_unique, NULL as value_per_locale, NULL as value_per_channel, NULL as created_at')
                ->from(DB::raw('(SELECT 1) as dummy'))
                ->whereRaw('1 = 0');
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
            'label'      => trans('admin::app.catalog.attributes.index.datagrid.id'),
            'type'       => 'integer',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'code',
            'label'      => trans('admin::app.catalog.attributes.index.datagrid.code'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'admin_name',
            'label'      => trans('admin::app.catalog.attributes.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'              => 'type',
            'label'              => trans('admin::app.catalog.attributes.index.datagrid.type'),
            'type'               => 'string',
            'searchable'         => true,
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => [
                ['label' => trans('admin::app.catalog.attributes.index.datagrid.text'), 'value' => 'text'],
                ['label' => trans('admin::app.catalog.attributes.index.datagrid.textarea'), 'value' => 'textarea'],
                ['label' => trans('admin::app.catalog.attributes.index.datagrid.price'), 'value' => 'price'],
                ['label' => trans('admin::app.catalog.attributes.index.datagrid.boolean'), 'value' => 'boolean'],
                ['label' => trans('admin::app.catalog.attributes.index.datagrid.select'), 'value' => 'select'],
                ['label' => trans('admin::app.catalog.attributes.index.datagrid.multiselect'), 'value' => 'multiselect'],
                ['label' => trans('admin::app.catalog.attributes.index.datagrid.date-time'), 'value' => 'datetime'],
                ['label' => trans('admin::app.catalog.attributes.index.datagrid.date'), 'value' => 'date'],
                ['label' => trans('admin::app.catalog.attributes.index.datagrid.image'), 'value' => 'image'],
                ['label' => trans('admin::app.catalog.attributes.index.datagrid.file'), 'value' => 'file'],
                ['label' => trans('admin::app.catalog.attributes.index.datagrid.checkbox'), 'value' => 'checkbox'],
            ],
            'sortable' => true,
        ]);

        $this->addColumn([
            'index'      => 'is_required',
            'label'      => trans('admin::app.catalog.attributes.index.datagrid.required'),
            'type'       => 'boolean',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                if ($row->is_required) {
                    return trans('admin::app.catalog.attributes.index.datagrid.true');
                }

                return trans('admin::app.catalog.attributes.index.datagrid.false');
            },
        ]);

        $this->addColumn([
            'index'      => 'is_unique',
            'label'      => trans('admin::app.catalog.attributes.index.datagrid.unique'),
            'type'       => 'boolean',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                return $row->is_unique 
                    ? trans('admin::app.catalog.attributes.index.datagrid.true')
                    : trans('admin::app.catalog.attributes.index.datagrid.false');
            },
        ]);

        $this->addColumn([
            'index'      => 'value_per_locale',
            'label'      => trans('admin::app.catalog.attributes.index.datagrid.locale-based'),
            'type'       => 'boolean',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                if ($row->value_per_locale) {
                    return trans('admin::app.catalog.attributes.index.datagrid.true');
                }

                return trans('admin::app.catalog.attributes.index.datagrid.false');
            },
        ]);

        $this->addColumn([
            'index'      => 'value_per_channel',
            'label'      => trans('admin::app.catalog.attributes.index.datagrid.channel-based'),
            'type'       => 'boolean',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                if ($row->value_per_channel) {
                    return trans('admin::app.catalog.attributes.index.datagrid.true');
                }

                return trans('admin::app.catalog.attributes.index.datagrid.false');
            },
        ]);

        $this->addColumn([
            'index'           => 'created_at',
            'label'           => trans('admin::app.catalog.attributes.index.datagrid.created-at'),
            'type'            => 'date',
            'searchable'      => true,
            'filterable'      => true,
            'filterable_type' => 'date_range',
            'sortable'        => true,
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
