<?php

namespace App\Modules\HR\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EmployeeRepositoryInterface
{
    /**
     * Paginate employees with optional filters, search, and sort.
     *
     * @param  array<string, mixed>  $params
     */
    public function paginate(array $params): LengthAwarePaginator;
}
