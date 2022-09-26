<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Jalameta\Attachments\AttachmentResponse;

class DefaultController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function attachment(AttachmentResponse $response, Attachment $attachment)
    {
        try {
            return $response->streamOrDownload($attachment);
        } catch (\Exception $e) {
            $p = public_path('assets/tb-logo.png');
            return request()->has('stream') && (bool) request()->input('stream') === true
            ? response()->stream($p)
            : response()->download($p);
        }
    }
}
