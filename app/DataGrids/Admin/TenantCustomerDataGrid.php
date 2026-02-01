<?php

namespace App\DataGrids\Admin;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class TenantCustomerDataGrid extends DataGrid
{
    protected $primaryColumn = 'customer_id';

    public function prepareQueryBuilder()
    {
        try {
            $tablePrefix = DB::connection('tenant')->getTablePrefix();
            $conn = DB::connection('tenant');

            // Check if customers table exists
            if (! $conn->getSchemaBuilder()->hasTable('customers')) {
                return DB::connection('tenant')->table('customers')->whereRaw('1 = 0');
            }

            $queryBuilder = DB::connection('tenant')->table('customers')
            ->leftJoin('addresses', function ($join) {
                $join->on('customers.id', '=', 'addresses.customer_id')
                    ->where('addresses.address_type', '=', 'customer');
            })
            ->leftJoin('orders', 'customers.id', '=', 'orders.customer_id')
            ->leftJoin('customer_groups', 'customers.customer_group_id', '=', 'customer_groups.id')
            ->addSelect(
                'customers.id as customer_id',
                'customers.email',
                'customers.phone',
                'customers.gender',
                'customers.status',
                'customers.is_suspended',
                'customer_groups.name as group',
                'customers.channel_id',
            )
            ->addSelect(DB::raw('COUNT(DISTINCT '.$tablePrefix.'addresses.id) as address_count'))
            ->addSelect(DB::raw('COUNT(DISTINCT '.$tablePrefix.'orders.id) as order_count'))
            ->addSelect(DB::raw('CONCAT('.$tablePrefix.'customers.first_name, " ", '.$tablePrefix.'customers.last_name) as full_name'))
            ->groupBy('customers.id');

        $this->addFilter('customer_id', 'customers.id');
        $this->addFilter('email', 'customers.email');
        $this->addFilter('full_name', DB::raw('CONCAT('.$tablePrefix.'customers.first_name, " ", '.$tablePrefix.'customers.last_name)'));
        $this->addFilter('group', 'customer_groups.name');
        $this->addFilter('phone', 'customers.phone');
        $this->addFilter('status', 'customers.status');

        return $queryBuilder;
        } catch (\Exception $e) {
            // If connection fails, return empty query
            report($e);
            return DB::connection('tenant')->table('customers')->whereRaw('1 = 0');
        }
    }

    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'customer_id',
            'label'      => trans('admin::app.customers.customers.index.datagrid.id'),
            'type'       => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'full_name',
            'label'      => trans('admin::app.customers.customers.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'email',
            'label'      => trans('admin::app.customers.customers.index.datagrid.email'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'phone',
            'label'      => trans('admin::app.customers.customers.index.datagrid.phone'),
            'type'       => 'string',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'status',
            'label'      => trans('admin::app.customers.customers.index.datagrid.status'),
            'type'       => 'boolean',
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                return $row->status ? trans('admin::app.customers.customers.index.datagrid.active') : trans('admin::app.customers.customers.index.datagrid.inactive');
            },
        ]);

        $this->addColumn([
            'index'      => 'order_count',
            'label'      => trans('admin::app.customers.customers.index.datagrid.orders'),
            'type'       => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable'   => false,
        ]);
    }

    public function prepareActions()
    {
        $this->addAction([
            'index'  => 'view',
            'icon'   => 'icon-view',
            'title'  => trans('admin::app.customers.customers.index.datagrid.view'),
            'method' => 'GET',
            'url'    => function ($row) {
                // Extract tenant ID from request
                $tenantId = request()->route('tenant');
                return route('admin.tenants.customers.show', [$tenantId, $row->customer_id]);
            },
        ]);
    }

    public function prepareMassActions()
    {
        // No mass actions for super admin view
    }
}
