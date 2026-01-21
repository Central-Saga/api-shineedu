<?php

namespace App\Modules\HR\Http\Controllers\Api\V2;

use App\Modules\HR\Models\Employee;
use App\Modules\HR\Http\Requests\EmployeeIndexRequest;
use App\Modules\HR\Http\Requests\StoreEmployeeRequest;
use App\Modules\HR\Http\Requests\UpdateEmployeeRequest;
use App\Modules\HR\Http\Resources\EmployeeResource;
use App\Modules\HR\Repositories\EmployeeRepositoryInterface;
use App\Modules\HR\Services\EmployeeService;
use App\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class EmployeeController
{
    public function __construct(
        protected EmployeeRepositoryInterface $repository,
        protected EmployeeService $service
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(EmployeeIndexRequest $request): JsonResponse
    {
        $employees = $this->repository->paginate($request->validated());

        return ApiResponse::paginated(
            EmployeeResource::collection($employees),
            $employees,
            'Data karyawan berhasil diambil'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = $this->service->create($request->validated());

        return ApiResponse::created(
            new EmployeeResource($employee->load('user')),
            'Karyawan berhasil ditambahkan'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee): JsonResponse
    {
        $employee->load('user');

        return ApiResponse::ok(
            new EmployeeResource($employee),
            'Detail karyawan berhasil diambil'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $this->service->update($employee, $request->validated());

        return ApiResponse::ok(
            new EmployeeResource($employee->fresh()->load('user')),
            'Data karyawan berhasil diperbarui'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();

        return ApiResponse::ok(null, 'Karyawan berhasil dihapus');
    }
}
