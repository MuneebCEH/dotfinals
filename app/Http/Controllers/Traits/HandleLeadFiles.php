<?php

namespace App\Http\Controllers\Traits;

use App\Models\Lead;
use Illuminate\Support\Facades\Storage;

trait HandleLeadFiles
{
    /**
     * Generate and store a text report for a lead
     */
    protected function storeTextReport(Lead $lead): string
    {
        $content = $lead->generateTextReport();
        $filename = $this->generateTextReportFilename($lead);
        $path = 'leads/reports/' . $filename;
        
        Storage::disk('public')->put($path, $content);
        
        return $path;
    }

    /**
     * Generate a filename for the text report
     */
    protected function generateTextReportFilename(Lead $lead): string
    {
        $base = trim($lead->first_name . ' ' . $lead->surname) ?: 'Lead';
        $base = str_replace(' ', '_', $base);
        return $base . '_' . now()->format('Y-m-d_H-i-s') . '.pdf';
    }
}
