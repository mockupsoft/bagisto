<?php

namespace App\DataGrids\Admin;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Models\OrderAddress;

class TenantOrderDataGrid extends DataGrid
{
    protected $primaryColumn = 'id';

    public function prepareQueryBuilder()
    {
        try {
            $tablePrefix = DB::connection('tenant')->getTablePrefix();
            $conn = DB::connection('tenant');

            // Check if orders table exists
            if (! $conn->getSchemaBuilder()->hasTable('orders')) {
                return DB::connection('tenant')->table('orders')->whereRaw('1 = 0');
            }

            $queryBuilder = DB::connection('tenant')->table('orders')
            ->leftJoin('addresses as order_address_shipping', function ($leftJoin) {
                $leftJoin->on('order_address_shipping.order_id', '=', 'orders.id')
                    ->where('order_address_shipping.address_type', OrderAddress::ADDRESS_TYPE_SHIPPING);
            })
            ->leftJoin('addresses as order_address_billing', function ($leftJoin) {
                $leftJoin->on('order_address_billing.order_id', '=', 'orders.id')
                    ->where('order_address_billing.address_type', OrderAddress::ADDRESS_TYPE_BILLING);
            })
            ->leftJoin('order_payment', 'orders.id', '=', 'order_payment.order_id')
            ->select(
                'orders.id',
                DB::raw('GROUP_CONCAT('.$tablePrefix.'order_payment.method SEPARATOR "|") as method'),
                'orders.increment_id',
                'orders.base_grand_total',
                'orders.created_at',
                'channel_name',
                'channel_id',
                'status',
                'customer_email',
                'orders.cart_id as items',
                DB::raw('CONCAT('.$tablePrefix.'orders.customer_first_name, " ", '.$tablePrefix.'orders.customer_last_name) as full_name'),
                DB::raw('CONCAT('.$tablePrefix.'order_address_billing.city, ", ", '.$tablePrefix.'order_address_billing.state,", ", '.$tablePrefix.'order_address_billing.country) as location')
            )
            ->groupBy('orders.id');

        $this->addFilter('full_name', DB::raw('CONCAT('.$tablePrefix.'orders.customer_first_name, " ", '.$tablePrefix.'orders.customer_last_name)'));
        $this->addFilter('created_at', 'orders.created_at');
        $this->addFilter('status', 'orders.status');
        $this->addFilter('increment_id', 'orders.increment_id');

        return $queryBuilder;
        } catch (\Exception $e) {
            // If connection fails, return empty query
            report($e);
            return DB::connection('tenant')->table('orders')->whereRaw('1 = 0');
        }
    }

    public function prepareColumns()
    {
        $this->addColumn([
            'index'      => 'increment_id',
            'label'      => trans('admin::app.sales.orders.index.datagrid.order-id'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'              => 'status',
            'label'              => trans('admin::app.sales.orders.index.datagrid.status'),
            'type'               => 'string',
            'searchable'         => true,
            'filterable'         => true,
            'filterable_type'    => 'dropdown',
            'filterable_options' => [
                ['label' => trans('admin::app.sales.orders.index.datagrid.processing'), 'value' => Order::STATUS_PROCESSING],
                ['label' => trans('admin::app.sales.orders.index.datagrid.completed'), 'value' => Order::STATUS_COMPLETED],
                ['label' => trans('admin::app.sales.orders.index.datagrid.canceled'), 'value' => Order::STATUS_CANCELED],
                ['label' => trans('admin::app.sales.orders.index.datagrid.closed'), 'value' => Order::STATUS_CLOSED],
                ['label' => trans('admin::app.sales.orders.index.datagrid.pending'), 'value' => Order::STATUS_PENDING],
                ['label' => trans('admin::app.sales.orders.index.datagrid.pending-payment'), 'value' => Order::STATUS_PENDING_PAYMENT],
            ],
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'full_name',
            'label'      => trans('admin::app.sales.orders.index.datagrid.customer-name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'customer_email',
            'label'      => trans('admin::app.sales.orders.index.datagrid.email'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'base_grand_total',
            'label'      => trans('admin::app.sales.orders.index.datagrid.grand-total'),
            'type'       => 'decimal',
            'searchable' => false,
            'filterable' => false,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'      => trans('admin::app.sales.orders.index.datagrid.date'),
            'type'       => 'datetime',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
        ]);
    }

    public function prepareActions()
    {
        $this->addAction([
            'index'  => 'view',
            'icon'   => 'icon-view',
            'title'  => trans('admin::app.sales.orders.index.datagrid.view'),
            'method' => 'GET',
            'url'    => function ($row) {
                $tenantId = request()->route('tenant');
                return route('admin.tenants.orders.show', [$tenantId, $row->id]);
            },
        ]);
    }

    public function prepareMassActions()
    {
        // No mass actions for super admin view
    }
}
