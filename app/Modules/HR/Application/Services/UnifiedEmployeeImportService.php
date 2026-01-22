<?php

namespace App\Modules\HR\Application\Services;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class UnifiedEmployeeImportService
{
    protected array $config = [
        'user_mode' => 'update', // skip|update
        'employee_mode' => 'update', // update|skip
        'overwrite_password' => false,
    ];

    /**
     * Set configuration for the import.
     */
    public function setConfig(array $config): self
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    /**
     * Import from CSV file using chunking for memory safety.
     *
     * @param mixed $file Absolute path to the CSV file or UploadedFile object
     * @return array Summary report of the import process
     */
    public function import($file): array
    {
        try {
            $import = new UnifiedEmployeeImport($this->config);

            // Use Excel::import to trigger the chunking logic.
            // Passing the file object directly allows extension detection.
            Excel::import($import, $file);

            $results = $import->getResults();

            // Log summary for production monitoring
            Log::info("CSV Import Completed", [
                'total_rows' => $results['total_rows'],
                'inserted' => $results['users_inserted'] + $results['employees_inserted'],
                'updated' => $results['users_updated'] + $results['employees_updated'],
                'errors_count' => count($results['errors'])
            ]);

            return $results;
        } catch (\Exception $e) {
            Log::error("CSV Import Failed: " . $e->getMessage());
            return [
                'total_rows' => 0,
                'users_inserted' => 0,
                'users_updated' => 0,
                'employees_inserted' => 0,
                'employees_updated' => 0,
                'rows_skipped' => 0,
                'errors' => ["Critical Import Error: " . $e->getMessage()],
                'logs' => [],
            ];
        }
    }
}
