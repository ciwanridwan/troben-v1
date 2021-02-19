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
        return $response->streamOrDownload($attachment);
    }
}
