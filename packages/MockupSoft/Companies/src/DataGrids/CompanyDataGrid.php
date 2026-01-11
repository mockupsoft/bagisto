<?php

namespace MockupSoft\Companies\DataGrids;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class CompanyDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        return DB::table('companies')
            ->select(
                'id',
                'name',
                'email',
                'phone',
                'created_at'
            );
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
            'label'      => trans('mockupsoft-companies::app.datagrid.id'),
            'type'       => 'integer',
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('mockupsoft-companies::app.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'email',
            'label'      => trans('mockupsoft-companies::app.datagrid.email'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'phone',
            'label'      => trans('mockupsoft-companies::app.datagrid.phone'),
            'type'       => 'string',
            'filterable' => true,
            'sortable'   => false,
        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'      => trans('mockupsoft-companies::app.datagrid.created-at'),
            'type'       => 'datetime',
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
        if (bouncer()->hasPermission('mockupsoft.companies.edit')) {
            $this->addAction([
                'index'  => 'edit',
                'icon'   => 'icon-edit',
                'title'  => trans('mockupsoft-companies::app.datagrid.edit'),
                'method' => 'GET',
                'url'    => fn ($row) => route('mockupsoft.companies.show', $row->id),
            ]);
        }

        if (bouncer()->hasPermission('mockupsoft.companies.delete')) {
            $this->addAction([
                'index'  => 'delete',
                'icon'   => 'icon-delete',
                'title'  => trans('mockupsoft-companies::app.datagrid.delete'),
                'method' => 'DELETE',
                'url'    => fn ($row) => route('mockupsoft.companies.delete', $row->id),
            ]);
        }
    }
}
