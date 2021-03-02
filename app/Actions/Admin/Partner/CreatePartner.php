<?php

namespace App\Actions\Admin\Partner;

use App\Http\Response;
use App\Jobs\Inventory\CreateManyNewInventory;
use App\Jobs\Partners\CreateNewPartner;
use App\Jobs\Users\CreateNewUser;
use App\Jobs\Users\DeleteExistingUser;
use App\Models\Partners\Partner;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Arr;

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

    function __construct($inputs = [])
    {
        $this->attributes = $inputs;
        $this->partner = new Partner();
    }

    public function create()
    {
        // temp owner info same as partner info
        $this->attributes['partner']['contact_email'] = $this->attributes['owner']['email'];
        $this->attributes['partner']['contact_phone'] = $this->attributes['owner']['phone'];

        $this->validate_input();

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
        $this->partner->users()->attach($this->user->id);

        switch ($this->attributes['partner']['type']) {
            case Partner::TYPE_POOL:
                $this->create_pool();
                break;
            case Partner::TYPE_BUSINESS:
                $this->create_business();
                break;
            case Partner::TYPE_SPACE:
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

        $this->jobUser = new CreateNewUser($this->attributes['owner']);
        $this->jobPartner = new CreateNewPartner($this->attributes['partner']);

        switch ($this->attributes['partner']['type']) {
            case Partner::TYPE_POOL:
                $this->validate_pool();
                break;
            case Partner::TYPE_BUSINESS:
                $this->validate_business();
                break;
            case Partner::TYPE_SPACE:
                $this->validate_space();
                break;
            case Partner::TYPE_TRANSPORTER:
                $this->validate_transporter();
                break;
        }
    }

    public function create_pool()
    {
        $this->jobInventory = new CreateManyNewInventory($this->partner, $this->attributes['inventory']);
        $this->dispatch($this->jobInventory);
    }
    public function validate_pool()
    {
        $this->jobInventory = new CreateManyNewInventory($this->partner, $this->attributes['inventory']);
    }

    public function create_transporter()
    {
        # code...
    }

    public function create_business()
    {
        # code...
    }

    public function create_space()
    {
        # code...
    }

    public function validate_transporter()
    {
        # code...
    }

    public function validate_business()
    {
        # code...
    }

    public function validate_space()
    {
        # code...
    }
}
