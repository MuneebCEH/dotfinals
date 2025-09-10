<?php

namespace App\Http\Controllers;

class AttachmentController extends Controller
{
    private function decodePath(string $encoded): string
    {
        // URL-safe base64 decode
        $decoded = base64_decode(strtr($encoded, '-_', '+/'), true);
        abort_unless($decoded !== false, 404);
        return ltrim($decoded, '/');
    }

    public function stream(string $encoded)
    {
        $relPath = $this->decodePath($encoded);
        $file = storage_path('app/public/' . $relPath);
        abort_unless(is_file($file), 404);

        return response()->file($file, [
            'Content-Type' => mime_content_type($file),
            'Content-Disposition' => 'inline',
        ]);
    }

    public function preview(string $encoded)
    {
        $relPath = $this->decodePath($encoded);
        $fileUrl = route('attachments.stream', ['encoded' => strtr(base64_encode($relPath), '+/', '-_')]);
        return view('attachments.preview', compact('fileUrl'));
    }
}
