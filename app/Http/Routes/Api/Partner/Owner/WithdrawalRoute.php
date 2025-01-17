<?php

namespace App\Http\Routes\Api\Partner\Owner;

use App\Http\Controllers\Api\Partner\Owner\WithdrawalController;
use Jalameta\Router\BaseRoute;

class WithdrawalRoute extends BaseRoute
{
    protected $prefix = 'partner/owner/withdrawal';

    protected $name = 'api.partner.owner.withdrawal';

    protected $middleware = [
        'partner.role:owner',
        'partner.scope.role:owner',
    ];
    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->get($this->prefix(), [
            'as' => $this->name,
            'uses' => $this->uses('index'),
        ]);

        $this->router->get($this->prefix('account/bank'), [
            'as' => $this->name('getAccountBank'),
            'uses' => $this->uses('getAccountBank')
        ]);

        $this->router->get($this->prefix('bank'), [
            'as' => $this->name('getBank'),
            'uses' => $this->uses('getBank')
        ]);

        $this->router->post($this->prefix('store'), [
            'as' => $this->name('store'),
            'uses' => $this->uses('store'),
        ]);



        $this->router->get($this->prefix('detail/{withdrawal_hash}'), [
            'as' => $this->name('detail'),
            'uses' => $this->uses('detail')
        ]);

        // $this->router->post($this->prefix('/attachment_transfer/{withdrawal_hash}'),[
        //     'as' => $this->name('attachmentTransfer'),
        //     'uses' => $this->uses('attachmentTransfer')
        // ]);

        $this->router->get($this->prefix('detail/{withdrawal_hash}/{receipt}'), [
            'as' => $this->name('detailReceipt'),
            'uses' => $this->uses('detailReceipt')
        ]);

        $this->router->get($this->prefix('export/{withdrawal_hash}'), [
            'as' => $this->name('export'),
            'uses' => $this->uses('export')
        ]);
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return WithdrawalController::class;
    }
}
