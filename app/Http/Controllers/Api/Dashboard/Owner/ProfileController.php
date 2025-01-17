<?php

namespace App\Http\Controllers\Api\Dashboard\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Owner\UpdatePasswordRequest;
use App\Http\Requests\Dashboard\Owner\UpdateProfileRequest;
use App\Http\Resources\Api\Partner\Owner\InfoProfileResource;
use App\Http\Response;
use App\Models\Partners\BankAccount;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function info(Request $request)
    {
        $owner = $request->user();

        return $this->jsonSuccess(InfoProfileResource::make($owner));
    }
    /**
     * Update profile owner of partner
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $request->validated();
        $user = $request->user();
        $avatar = null;

        $phoneNumber = change_format_number($request->phone);

        if ($request->avatar) {
            $avatar = handleUpload($request->avatar, 'avatar/users');
        }

        User::query()->where('id', $user->id)->update([
            'email' => $request->email ?? $user->email,
            'phone' =>   $phoneNumber ?? $user->phone,
            'avatar' =>   $avatar ?? $user->avatar,
        ]);

        if (!is_null($user->bankOwner)) {
            $user->bankOwner->update([
                'bank_id' => $request->bank_id ?? $user->bankOwner->bank_id,
                'account_name' => $request->bank_account_name ?? $user->bankOwner->account_name,
                'account_number' => $request->bank_account_number ?? $user->bankOwner->account_number,
            ]);
        } else {
            BankAccount::create([
                "user_id" => $user->id,
                "bank_id" => $request->bank_id,
                "account_name" => $request->bank_account_name,
                "account_number" => $request->bank_account_number,
            ]);
        }

        $user->partners->first()->update([
            'address' => $request->address ?? $user->partners->first()->address
        ]);

        return (new Response(Response::RC_UPDATED))->json();
    }

    /**
     * Update password owner account
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $request->validated();
        $user = $request->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return (new Response(Response::RC_BAD_REQUEST, ['Message' => 'Old Password Does Not Match']))->json();
        }

        $user->update(['password' => $request->new_password]);
        return (new Response(Response::RC_UPDATED))->json();
    }
}
