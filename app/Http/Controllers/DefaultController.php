<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

    public function changePassword(Request $request)
    {
        return view('antd::auth.reset');
    }

    public function checkUsername(Request $request)
    {
        $username = $request->get('username');

        $c = User::where('email', 'ILIKE', $username)->orWhere('username', 'ILIKE', $username)->first();

        return response()->json(['status' => !is_null($c), 'search' => $username]);
    }

    public function changePasswordGuest(Request $request)
    {
        $username = $request->get('username');
        $oldPassword = $request->get('password_old');
        $newPassword = $request->get('password_new');
        $newConfirmPassword = $request->get('password_new_confirm');

        $c = User::where('email', 'ILIKE', $username)->orWhere('username', 'ILIKE', $username)->firstOrFail();
        if (! Hash::check($oldPassword, $c->password)) {
            return response()->json(['status' => false, 'msg' => 'Old password not matching']);
        }

        if ($newPassword != $newConfirmPassword) {
            return response()->json(['status' => false, 'msg' => 'New password confirmation not matching']);
        }

        $c->password = $newPassword;
        $c->save();

        return response()->json(['status' => true, 'msg' => 'New password setup']);
    }
}
