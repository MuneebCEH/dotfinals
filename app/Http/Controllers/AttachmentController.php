<?php

namespace App\Http\Controllers;

use App\Models\Attachment; // adjust namespace if different
use App\Models\LeadIssue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AttachmentController extends Controller
{
    /**
     * Inline-preview an attachment (best-effort anti-download).
     * Serves from storage/app/public/<file_path>.
     */
    // In your controller
    public function preview($path)
    {
        $file = storage_path('app/public/' . $path);
        abort_unless(file_exists($file), 404);

        return response()->file($file, [
            'Content-Disposition' => 'inline', // forces inline preview
            'Content-Type' => mime_content_type($file)
        ]);
    }
}
