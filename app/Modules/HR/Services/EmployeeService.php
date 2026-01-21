<?php

namespace App\Modules\HR\Services;

use App\Modules\HR\Models\Employee;

class EmployeeService
{
    /**
     * Create a new employee.
     *
     * @param  array<string, mixed>  $validated
     */
    public function create(array $validated): Employee
    {
        return Employee::create($validated);
    }

    /**
     * Update an existing employee.
     *
     * @param  array<string, mixed>  $validated
     */
    public function update(Employee $employee, array $validated): void
    {
        $employee->update($validated);
    }
}
