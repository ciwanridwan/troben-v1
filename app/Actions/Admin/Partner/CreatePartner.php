<?php

namespace App\Actions\Admin\Partner;

use App\Http\Response;
use App\Jobs\Inventory\CreateManyNewInventory;
use App\Jobs\Partners\CreateNewPartner;
use App\Jobs\Users\CreateNewUser;
use Illuminate\Foundation\Bus\DispatchesJobs;

class CreatePartner
{
    use DispatchesJobs;
    /**
     * @var array
     */
    protected array $attributes;

    function __construct($inputs = [])
    {
        $this->attributes = $inputs;
    }

    public function create()
    {

        $jobUser = new CreateNewUser($this->attributes['owner']);
        $jobPartner = new CreateNewPartner($this->attributes['partner']);


        $this->dispatch($jobUser);
        $this->dispatch($jobPartner);

        $partner = $jobPartner->partner;
        $user = $jobUser->user;
        $partner->users()->attach($user->id);

        $jobInventory = new CreateManyNewInventory($partner, $this->attributes['inventory']);
        $this->dispatch($jobInventory);

        return (new Response(Response::RC_SUCCESS, $partner))->json();
    }

    public function create_pool()
    {
        # code...
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
}
