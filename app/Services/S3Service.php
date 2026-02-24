<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class S3Service
{
    /**
     * Sube una imagen a S3 y retorna la URL pública
     */
    public static function uploadImage(UploadedFile $file, string $folder = 'yape-comprobantes'): string
    {
        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        
        $path = $file->storeAs($folder, $fileName, 's3');
        
        // Construir la URL pública de S3
        $region = env('AWS_DEFAULT_REGION');
        $bucket = env('AWS_BUCKET');
        
        return "https://{$bucket}.s3.{$region}.amazonaws.com/{$path}";
    }
    
    /**
     * Elimina una imagen de S3
     */
    public static function deleteImage(string $path): bool
    {
        return Storage::disk('s3')->delete($path);
    }
    
    /**
     * Verifica si una imagen existe en S3
     */
    public static function imageExists(string $path): bool
    {
        return Storage::disk('s3')->exists($path);
    }
}
