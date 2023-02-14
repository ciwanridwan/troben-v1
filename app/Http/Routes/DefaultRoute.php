<?php

namespace App\Http\Routes;

use App\Models\Attachment;
use Jalameta\Router\BaseRoute;
use App\Http\Controllers\DefaultController;

class DefaultRoute extends BaseRoute
{
    /**
     * Route path prefix.
     *
     * @var string
     */
    protected $prefix = '/';

    /**
     * Registered route name.
     *
     * @var string
     */
    protected $name = 'home';

    /**
     * Register routes handled by this class.
     *
     * @return void
     */
    public function register()
    {
        $this->router->bind('attachment_uuid', fn ($id) => Attachment::query()->findOrFail($id));

        $this->router->get($this->prefix, [
            'as' => $this->name,
            'uses' => fn () => redirect('auth/login')
        ]);

        $this->router->get($this->prefix('change-password'), [
            'as' => $this->name('changepassword'),
            'uses' => $this->uses('changePassword'),
        ]);

        $this->router->get($this->prefix('attachment/{attachment_uuid}'), [
            'as' => $this->name('attachment'),
            'uses' => $this->uses('attachment'),
        ]);

	$this->router->get($this->prefix('app-log2'), [
            'as' => $this->name('applog'),
            'uses' => $this->uses('index', \Rap2hpoutre\LaravelLogViewer\LogViewerController::class),
        ]);

        $this->router->view('debug', 'antd::auth.login');
    }

    /**
     * Controller used by this route.
     *
     * @return string
     */
    public function controller()
    {
        return DefaultController::class;
    }
}
