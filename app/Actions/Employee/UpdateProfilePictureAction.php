<?php

namespace App\Actions\Employee;

use App\Models\Employee;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UpdateProfilePictureAction
{
    /**
     * Resize avatar ke 400x400 px menggunakan PHP GD dan simpan.
     */
    public function execute(Employee $employee, UploadedFile $file): string
    {
        $mime = $file->getMimeType();
        $sourcePath = $file->getRealPath();

        // 1. Buat image source resource berdasarkan tipe MIME
        $sourceImage = match ($mime) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default => null,
        };

        $fileName = 'avatars/'.$employee->id.'_'.Str::random(10).'.jpg';
        $destinationPath = storage_path('app/public/'.$fileName);

        // Buat folder jika belum ada
        if (! is_dir(storage_path('app/public/avatars'))) {
            mkdir(storage_path('app/public/avatars'), 0755, true);
        }

        if ($sourceImage) {
            $origWidth = imagesx($sourceImage);
            $origHeight = imagesy($sourceImage);
            $targetSize = 400;

            // Buat canvas kotak 400x400
            $targetCanvas = imagecreatetruecolor($targetSize, $targetSize);

            // Perhitungan crop square tengah
            $cropSize = min($origWidth, $origHeight);
            $srcX = (int) (($origWidth - $cropSize) / 2);
            $srcY = (int) (($origHeight - $cropSize) / 2);

            imagecopyresampled(
                $targetCanvas,
                $sourceImage,
                0, 0,
                $srcX, $srcY,
                $targetSize, $targetSize,
                $cropSize, $cropSize
            );

            // Simpan sebagai JPEG kualitas 85%
            imagejpeg($targetCanvas, $destinationPath, 85);

            imagedestroy($sourceImage);
            imagedestroy($targetCanvas);
        } else {
            // Fallback penyimpanan standar jika GD gagal
            $file->storeAs('public/avatars', basename($fileName));
        }

        // Hapus avatar lama jika ada
        if ($employee->avatar_url) {
            $oldPath = str_replace('storage/', 'public/', $employee->avatar_url);
            Storage::delete($oldPath);
        }

        $publicUrl = 'storage/'.$fileName;
        $employee->update(['avatar_url' => $publicUrl]);

        return $publicUrl;
    }
}
