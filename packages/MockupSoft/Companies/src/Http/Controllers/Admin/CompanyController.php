<?php

namespace MockupSoft\Companies\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use MockupSoft\Companies\DataGrids\CompanyDataGrid;
use MockupSoft\Companies\Repositories\CompanyRepository;
use Webkul\Admin\Http\Controllers\Controller;

class CompanyController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected CompanyRepository $companyRepository) {}

    /**
     * Display a listing of companies.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(CompanyDataGrid::class)->process();
        }

        return view('mockupsoft-companies::index');
    }

    /**
     * Show company data for edit modal.
     */
    public function show(int $id): JsonResponse
    {
        $company = $this->companyRepository->find($id);

        if (! $company) {
            return new JsonResponse([
                'message' => trans('mockupsoft-companies::app.companies.not-found'),
            ], 404);
        }

        return new JsonResponse([
            'data' => $company,
        ]);
    }

    /**
     * Store a newly created company.
     */
    public function store(): JsonResponse
    {
        $this->validate(request(), [
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:companies,email',
            'phone'   => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
        ]);

        Event::dispatch('mockupsoft.companies.create.before');

        $company = $this->companyRepository->create(request()->only([
            'name',
            'email',
            'phone',
            'address',
        ]));

        Event::dispatch('mockupsoft.companies.create.after', $company);

        return new JsonResponse([
            'message' => trans('mockupsoft-companies::app.companies.create-success'),
        ]);
    }

    /**
     * Update the specified company.
     */
    public function update(int $id): JsonResponse
    {
        $this->validate(request(), [
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:companies,email,'.$id,
            'phone'   => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
        ]);

        Event::dispatch('mockupsoft.companies.update.before', $id);

        $company = $this->companyRepository->update(request()->only([
            'name',
            'email',
            'phone',
            'address',
        ]), $id);

        Event::dispatch('mockupsoft.companies.update.after', $company);

        return new JsonResponse([
            'message' => trans('mockupsoft-companies::app.companies.update-success'),
        ]);
    }

    /**
     * Remove the specified company.
     */
    public function destroy(int $id): JsonResponse
    {
        $company = $this->companyRepository->find($id);

        if (! $company) {
            return new JsonResponse([
                'message' => trans('mockupsoft-companies::app.companies.not-found'),
            ], 404);
        }

        try {
            Event::dispatch('mockupsoft.companies.delete.before', $id);

            $this->companyRepository->delete($id);

            Event::dispatch('mockupsoft.companies.delete.after', $id);

            return new JsonResponse([
                'message' => trans('mockupsoft-companies::app.companies.delete-success'),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => trans('mockupsoft-companies::app.companies.delete-failed'),
            ], 500);
        }
    }
}
