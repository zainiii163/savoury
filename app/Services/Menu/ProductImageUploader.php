<?php

namespace App\Services\Menu;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImageUploader
{
    public function store(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (! in_array($extension, $allowed, true)) {
            $extension = 'jpg';
        }

        $filename = Str::uuid()->toString().'.'.$extension;

        return $file->storeAs('products', $filename, 'public');
    }

    public function delete(?string $path): void
    {
        if ($path === null || $path === '' || filter_var($path, FILTER_VALIDATE_URL)) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}
