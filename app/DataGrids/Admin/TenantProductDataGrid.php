<?php

namespace App\DataGrids\Admin;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class TenantProductDataGrid extends DataGrid
{
    /**
     * Primary column.
     *
     * @var string
     */
    protected $primaryColumn = 'product_id';

    /**
     * Cached column existence checks.
     *
     * @var array
     */
    protected $columnCache = [];

    /**
     * Prepare query builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function prepareQueryBuilder()
    {
        try {
            $tablePrefix = DB::connection('tenant')->getTablePrefix();
            $conn = DB::connection('tenant');

            // Check if product_flat table exists
            if (! $conn->getSchemaBuilder()->hasTable('product_flat')) {
                return DB::connection('tenant')->table('product_flat')->whereRaw('1 = 0');
            }

            // Check which tables exist in tenant database
            $hasProductImages = $conn->getSchemaBuilder()->hasTable('product_images');
            $hasProductInventories = $conn->getSchemaBuilder()->hasTable('product_inventories');
            $hasProductCategories = $conn->getSchemaBuilder()->hasTable('product_categories');
            $hasCategoryTranslations = $conn->getSchemaBuilder()->hasTable('category_translations');
            $hasAttributeFamilies = $conn->getSchemaBuilder()->hasTable('attribute_families');

        // Check which columns exist in product_flat table and cache them
        $this->columnCache['product_flat.type'] = $conn->getSchemaBuilder()->hasColumn('product_flat', 'type');
        $this->columnCache['product_flat.visible_individually'] = $conn->getSchemaBuilder()->hasColumn('product_flat', 'visible_individually');
        $this->columnCache['product_flat.attribute_family_id'] = $conn->getSchemaBuilder()->hasColumn('product_flat', 'attribute_family_id');
        
        $hasTypeColumn = $this->columnCache['product_flat.type'];
        $hasVisibleIndividuallyColumn = $this->columnCache['product_flat.visible_individually'];
        $hasAttributeFamilyIdColumn = $this->columnCache['product_flat.attribute_family_id'];

        $queryBuilder = DB::connection('tenant')->table('product_flat')
            ->distinct();

        // Optional joins based on table existence
        $hasAttributeFamilyNameColumn = false;
        if ($hasAttributeFamilies && $hasAttributeFamilyIdColumn) {
            // Check if attribute_families table has 'name' column
            $hasAttributeFamilyNameColumn = $conn->getSchemaBuilder()->hasColumn('attribute_families', 'name');
            if ($hasAttributeFamilyNameColumn) {
                $queryBuilder->leftJoin('attribute_families as af', 'product_flat.attribute_family_id', '=', 'af.id');
            }
        }

        if ($hasProductInventories) {
            $queryBuilder->leftJoin('product_inventories', 'product_flat.product_id', '=', 'product_inventories.product_id');
        }

        if ($hasProductImages) {
            $queryBuilder->leftJoin('product_images', 'product_flat.product_id', '=', 'product_images.product_id');
        }

        if ($hasProductCategories) {
            $queryBuilder->leftJoin('product_categories as pc', 'product_flat.product_id', '=', 'pc.product_id');
            
            if ($hasCategoryTranslations) {
                $queryBuilder->leftJoin('category_translations as ct', function ($leftJoin) {
                    $leftJoin->on('pc.category_id', '=', 'ct.category_id')
                        ->where('ct.locale', app()->getLocale() ?: 'en');
                });
            }
        }

        // Base columns that should exist in minimal tenant product_flat
        $selects = [
            'product_flat.locale',
            'product_flat.channel',
            'product_flat.product_id',
            'product_flat.sku',
            'product_flat.name',
            'product_flat.status',
            'product_flat.price',
            'product_flat.url_key',
        ];

        // Add optional columns if they exist
        if ($hasTypeColumn) {
            $selects[] = 'product_flat.type';
        }

        if ($hasVisibleIndividuallyColumn) {
            $selects[] = 'product_flat.visible_individually';
        }

        if ($hasProductImages) {
            $selects[] = 'product_images.path as base_image';
        }

        if ($hasProductCategories) {
            $selects[] = 'pc.category_id';
            if ($hasCategoryTranslations) {
                $selects[] = 'ct.name as category_name';
            }
        }

        // Only add attribute_family if join was actually made and name column exists
        if ($hasAttributeFamilies && $hasAttributeFamilyIdColumn && $hasAttributeFamilyNameColumn) {
            $selects[] = 'af.name as attribute_family';
        } else {
            $selects[] = DB::raw("'' as attribute_family");
        }

        $queryBuilder->select($selects);

        // Add aggregated columns only if tables exist
        if ($hasProductInventories) {
            $queryBuilder->addSelect(DB::raw('COALESCE(SUM(DISTINCT '.$tablePrefix.'product_inventories.qty), 0) as quantity'));
        } else {
            $queryBuilder->addSelect(DB::raw('0 as quantity'));
        }

        if ($hasProductImages) {
            $queryBuilder->addSelect(DB::raw('COUNT(DISTINCT '.$tablePrefix.'product_images.id) as images_count'));
        } else {
            $queryBuilder->addSelect(DB::raw('0 as images_count'));
        }

        $locale = app()->getLocale() ?: 'en';
        $queryBuilder->where('product_flat.locale', $locale)
            ->groupBy('product_flat.product_id');

        $this->addFilter('product_id', 'product_flat.product_id');
        $this->addFilter('channel', 'product_flat.channel');
        $this->addFilter('locale', 'product_flat.locale');
        $this->addFilter('name', 'product_flat.name');
        
        if ($hasTypeColumn) {
            $this->addFilter('type', 'product_flat.type');
        }
        
        $this->addFilter('status', 'product_flat.status');
        
        if ($hasAttributeFamilies && $hasAttributeFamilyIdColumn && $hasAttributeFamilyNameColumn) {
            $this->addFilter('attribute_family', 'af.id');
        }

        return $queryBuilder;
        } catch (\Exception $e) {
            // If connection fails, return empty query
            report($e);
            return DB::connection('tenant')->table('product_flat')->whereRaw('1 = 0');
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
            'index'      => 'product_id',
            'label'      => trans('admin::app.catalog.products.index.datagrid.id'),
            'type'       => 'integer',
            'searchable' => false,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'name',
            'label'      => trans('admin::app.catalog.products.index.datagrid.name'),
            'type'       => 'string',
            'searchable' => true,
            'filterable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'sku',
            'label'      => trans('admin::app.catalog.products.index.datagrid.sku'),
            'type'       => 'string',
            'filterable' => true,
            'sortable'   => true,
        ]);

        // Only add type column if it exists in the database
        if (isset($this->columnCache['product_flat.type']) && $this->columnCache['product_flat.type']) {
            $this->addColumn([
                'index'      => 'type',
                'label'      => trans('admin::app.catalog.products.index.datagrid.type'),
                'type'       => 'string',
                'filterable' => true,
                'sortable'   => true,
            ]);
        }

        $this->addColumn([
            'index'      => 'status',
            'label'      => trans('admin::app.catalog.products.index.datagrid.status'),
            'type'       => 'boolean',
            'filterable' => true,
            'sortable'   => true,
            'closure'    => function ($row) {
                return $row->status ? trans('admin::app.catalog.products.index.datagrid.enabled') : trans('admin::app.catalog.products.index.datagrid.disabled');
            },
        ]);

        $this->addColumn([
            'index'      => 'price',
            'label'      => trans('admin::app.catalog.products.index.datagrid.price'),
            'type'       => 'decimal',
            'searchable' => false,
            'filterable' => false,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'quantity',
            'label'      => trans('admin::app.catalog.products.index.datagrid.quantity'),
            'type'       => 'integer',
            'searchable' => false,
            'filterable' => false,
            'sortable'   => false,
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
            'title'  => trans('admin::app.catalog.products.index.datagrid.view'),
            'method' => 'GET',
            'url'    => function ($row) {
                $tenantId = request()->route('tenant');
                return route('admin.tenants.products.show', [$tenantId, $row->product_id]);
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
        // No mass actions for super admin view
    }

}
