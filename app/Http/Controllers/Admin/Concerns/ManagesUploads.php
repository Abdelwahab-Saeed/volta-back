<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Services\ImageUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

trait ManagesUploads
{
    /**
     * Store the uploaded image for $field (if any) on the public disk, as WebP, and delete the file it replaces.
     * Returns the path to save: the new upload, or $current when nothing was uploaded.
     */
    protected function replaceUpload(Request $request, string $field, ?string $current, string $directory): ?string
    {
        if (! $request->hasFile($field)) {
            return $current;
        }

        $path = app(ImageUploader::class)->store($request->file($field), $directory);
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
