<?php

namespace App\Actions\Admin\Partner;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Http\Response;
use App\Models\Partners\Partner;
use App\Jobs\Users\CreateNewUser;
use App\Jobs\Users\DeleteExistingUser;
use App\Jobs\Partners\CreateNewPartner;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\Inventory\CreateManyNewInventory;
use App\Jobs\Partners\Transporter\BulkTransporter;
use App\Jobs\Partners\Warehouse\CreateNewWarehouse;
use App\Jobs\Users\Actions\VerifyExistingUser;
use Illuminate\Support\Str;

class CreatePartner
{
    use DispatchesJobs;
    /**
     * @var array
     */
    protected array $attributes;

    /**
     * @var Partner
     */
    protected Partner $partner;

    /**
     * @var User
     */
    protected User $user;

    /**
     * @var CreateManyNewInventory
     */
    protected CreateManyNewInventory $jobInventory;

    /**
     * @var BulkTransporter
     */
    protected BulkTransporter $jobTransporter;

    /**
     * @var CreateNewWarehouse
     */
    protected CreateNewWarehouse $jobWarehouse;

    /**
     * @var CreateNewUser
     */
    protected CreateNewUser $jobUser;

    /**
     * @var DeleteExistingUser
     */
    protected DeleteExistingUser $jobDeleteUser;

    /**
     * @var CreateNewPartner
     */
    protected CreateNewPartner $jobPartner;

    public function __construct($inputs = [])
    {
        Validator::make($inputs, [
            'owner.password' => ['confirmed'],
        ]);
        $this->attributes = $inputs;
        $this->partner = new Partner();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(): JsonResponse
    {
        // temp owner info same as partner info
        $this->attributes['partner']['contact_email'] = $this->attributes['owner']['email'];
        $this->attributes['partner']['contact_phone'] = $this->attributes['owner']['phone'];

        if ($this->attributes['partner']['type'] == Partner::TYPE_SALES) {
            $this->attributes['owner']['referral_code'] = strtoupper(Str::random(5));
        }

        $this->jobUser = new CreateNewUser($this->attributes['owner']);
        $this->jobPartner = new CreateNewPartner($this->attributes['partner']);

        $this->dispatch($this->jobUser);
        $this->user = $this->jobUser->user;

        try {
            $this->dispatch($this->jobPartner);
        } catch (Exception $e) {
            $this->jobDeleteUser = new DeleteExistingUser($this->user, true);
            $this->dispatch($this->jobDeleteUser);
            return (new Response(Response::RC_INVALID_DATA, ['message' => $e->getMessage()]))->json();
        }

        $this->partner = $this->jobPartner->partner;
        $this->validate_input();
        $this->partner->users()->attach($this->user->id);

        $job = new VerifyExistingUser($this->user);
        $this->dispatch($job);

        switch ($this->attributes['partner']['type']) {
            case Partner::TYPE_POOL:
                $this->create_pool();
                break;
            case Partner::TYPE_BUSINESS:
                $this->create_business();
                break;
            case Partner::TYPE_SPACE || Partner::TYPE_POS || Partner::TYPE_HEADSALES || Partner::TYPE_SALES :
                $this->create_space();
                break;
            case Partner::TYPE_TRANSPORTER:
                $this->create_transporter();
                break;
        }

        return (new Response(Response::RC_SUCCESS, $this->partner))->json();
    }

    public function validate_input()
    {
        switch ($this->attributes['partner']['type']) {
            case Partner::TYPE_POOL:
                $this->validate_pool();
                break;
            case Partner::TYPE_BUSINESS:
                $this->validate_business();
                break;
            case Partner::TYPE_SPACE || Partner::TYPE_POS || Partner::TYPE_HEADSALES || Partner::TYPE_SALES:
                $this->validate_space();
                break;
            case Partner::TYPE_TRANSPORTER:
                $this->validate_transporter();
                break;
        }
    }

    public function create_pool()
    {
        $this->validate_pool();
        $this->dispatch($this->jobWarehouse);

        foreach ($this->attributes['warehouse']['inventories'] as $key => $value) {
            $this->attributes['warehouse']['inventories'][$key]['warehouse_id'] = $this->jobWarehouse->warehouse->id;
        }

        $this->jobInventory = new CreateManyNewInventory($this->partner, $this->attributes['warehouse']['inventories']);

        $this->dispatch($this->jobInventory);
    }
    public function validate_pool()
    {
        // temp warehouse info same as partner
        $this->attributes['warehouse'] = array_merge($this->attributes['warehouse'], $this->attributes['partner']);

        $this->jobWarehouse = new CreateNewWarehouse($this->partner, $this->attributes['warehouse']);

        $this->jobInventory = new CreateManyNewInventory($this->partner, $this->attributes['warehouse']['inventories']);
    }

    public function create_transporter()
    {
        $this->validate_transporter();
        $this->dispatch($this->jobTransporter);
    }

    public function validate_transporter()
    {
        // auto verified if added by admin
        foreach ($this->attributes['transporter'] as $index => $value) {
            $this->attributes['transporter'][$index]['is_verified'] = true;
            $this->attributes['transporter'][$index]['verified_at'] = Carbon::now();
        }

        $this->jobTransporter = new BulkTransporter($this->partner, $this->attributes['transporter']);
    }

    public function create_space()
    {
        $this->validate_space();
        $this->dispatch($this->jobWarehouse);
    }

    public function validate_space()
    {
        $this->jobWarehouse = new CreateNewWarehouse($this->partner, $this->attributes['warehouse']);
    }

    public function create_business()
    {
        $this->dispatch($this->jobInventory);
        $this->dispatch($this->jobWarehouse);
        $this->dispatch($this->jobTransporter);
    }

    public function validate_business()
    {
        $this->validate_pool();
        $this->validate_space();
        $this->validate_transporter();
    }
}
