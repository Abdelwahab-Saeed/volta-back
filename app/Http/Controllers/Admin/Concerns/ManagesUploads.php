<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

trait ManagesUploads
{
    /**
     * Store the uploaded file for $field (if any) on the public disk and delete the file it replaces.
     * Returns the path to save: the new upload, or $current when nothing was uploaded.
     */
    protected function replaceUpload(Request $request, string $field, ?string $current, string $directory): ?string
    {
        if (! $request->hasFile($field)) {
            return $current;
        }

        $path = $request->file($field)->store($directory, 'public');
        $this->deleteUpload($current);

        return $path;
    }

    protected function deleteUpload(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
