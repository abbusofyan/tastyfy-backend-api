<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class CreditsImport implements ToCollection, WithHeadingRow
{
    protected $errors = [];
    protected $processedCount = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // Adding 2 because of 0-based index and header row

            try {
                if (!isset($row['phone']) || empty($row['phone'])) {
                    $this->errors[] = "Row {$rowNumber}: Phone number is missing";
                    continue;
                }

                if (!isset($row['credits']) || !is_numeric($row['credits'])) {
                    $this->errors[] = "Row {$rowNumber}: Invalid credit amount";
                    continue;
                }

                $phone = '+65' . trim($row['phone']);
                $credit = (float) trim($row['credits']);

                $user = User::where('phone', $phone)->first();

                if (!$user) {
                    $this->errors[] = "Row {$rowNumber}: User with phone {$phone} not found";
                    continue;
                }

                if (!$user->customer) {
                    $this->errors[] = "Row {$rowNumber}: No customer record found for {$phone}";
                    continue;
                }

                session(['transaction-type' => 'manual_adjustment']);
                $user->customer->increment('credit_balance', $credit);
                $this->processedCount++;
            } catch (\Exception $e) {
                $this->errors[] = "Row {$rowNumber}: Unexpected error - " . $e->getMessage();
            }
        }

        return [
            'processed' => $this->processedCount,
            'errors' => $this->errors
        ];
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getProcessedCount()
    {
        return $this->processedCount;
    }
}
