<?php

namespace App\DataGrids\Admin;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class TenantDataGrid extends DataGrid
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
        $queryBuilder = DB::table('tenants')
            ->leftJoin('tenant_databases', 'tenants.id', '=', 'tenant_databases.tenant_id')
            ->leftJoin('domains', function ($join) {
                $join->on('tenants.id', '=', 'domains.tenant_id')
                    ->where('domains.is_primary', '=', 1);
            })
            ->select(
                'tenants.id',
                'tenants.name',
                'tenants.slug',
                'tenants.status',
                'tenants.store_name',
                'tenants.last_error',
                'tenants.created_at',
                'tenant_databases.status as db_status',
                'domains.domain as primary_domain',
                'domains.verified_at as domain_verified_at',
            );

        $this->addFilter('id', 'tenants.id');
        $this->addFilter('name', 'tenants.name');
        $this->addFilter('status', 'tenants.status');
        $this->addFilter('db_status', 'tenant_databases.status');

        return $queryBuilder;
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
            'label'      => 'ID',
            'type'       => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => 'Name',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'slug',
            'label'      => 'Slug',
            'type'       => 'string',
            'searchable' => true,
            'filterable' => false,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'status',
            'label'      => 'Status',
            'type'       => 'string',
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                ['label' => 'Active', 'value' => 'active'],
                ['label' => 'Inactive', 'value' => 'inactive'],
                ['label' => 'Provisioning', 'value' => 'provisioning'],
                ['label' => 'Failed', 'value' => 'failed'],
            ],
            'sortable'   => true,
            'closure'    => function ($row) {
                return ucfirst($row->status ?? '-');
            },
        ]);

        $this->addColumn([
            'index'      => 'db_status',
            'label'      => 'DB Status',
            'type'       => 'string',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => false,
            'closure'    => function ($row) {
                return $row->db_status ? ucfirst($row->db_status) : '-';
            },
        ]);

        $this->addColumn([
            'index'      => 'primary_domain',
            'label'      => 'Primary Domain',
            'type'       => 'string',
            'searchable' => false,
            'filterable' => false,
            'sortable'   => false,
            'closure'    => function ($row) {
                if (!$row->primary_domain) {
                    return '-';
                }
                $verified = $row->domain_verified_at ? ' (Verified)' : ' (Not Verified)';
                return $row->primary_domain . $verified;
            },
        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'      => 'Created',
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
        $this->addAction([
            'index'  => 'view',
            'icon'   => 'icon-view',
            'title'  => 'View',
            'method' => 'GET',
            'url'    => function ($row) {
                return route('admin.tenants.show', $row->id);
            },
        ]);
    }

    /**
     * Prepare mass actions.
     *
     * @return void
     */
    public function prepareMassActions()
    {
        // No mass actions for now
    }
}
