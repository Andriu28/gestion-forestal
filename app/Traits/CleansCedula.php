<?php

namespace App\Traits;

trait CleansCedula
{
    public function cleanCedula($value)
    {
        $cleaned = preg_replace('/[^a-zA-Z0-9]/', '', $value);
        $cleaned = strtoupper($cleaned);
        if (preg_match('/^([VEPJG])(\d+)$/', $cleaned, $matches)) {
            return $matches[1] . $matches[2];
        }
        return $cleaned;
    }

    public function updatedCedula($value)
    {
        $this->cedula = $this->cleanCedula($value);
    }
}