<?php

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

if (!function_exists("change_format_number")) {
    /**
     * To change format number from request input
     * @param numeric $phone
     * @return numeric @phone
     */
    function change_format_number($phone)
    {
        if (substr($phone, 0, 2) === '08') {
            $phone = preg_replace('/^0/', '+62', $phone);
        }

        return $phone;
    }
}

if (!function_exists('generateUrl')) {
    function generateUrl(string $path, int $hours = 1)
    {
        $fileurl = Storage::disk('s3')->temporaryUrl($path, Carbon::now()->addHours($hours));

        return $fileurl;
    }
}

if (!function_exists('handleUpload')) {
    function handleUpload(UploadedFile $file, string $path)
    {
        $original = $file->getClientOriginalName();

        if (!$file->isValid()) {
            throw new \Exception(sprintf("Upload file %s is not valid", $original), 100103);
        }

        try {
            $filename = sprintf('%s-%s.%s', date('ymd'), md5(microtime(true)), $file->extension());
            Storage::disk('s3')->putFileAs($path, $file, $filename);
            $filepath = sprintf('%s/%s', $path, $filename);
        } catch (\Exception $e) {
            report($e);
            $filepath = 'nopic.png';
        }

        return $filepath;
    }
}
