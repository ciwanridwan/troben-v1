<?php

namespace App\Http\Controllers\Admin\Trawltruck;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function registerDriver()
    {
        return view('admin.master.trawltrucks.register-driver');
    }

    public function accountDriver()
    {
        return view('admin.master.trawltrucks.account-driver');
    }

    public function trackingOrder()
    {
        return view('admin.master.trawltrucks.tracking-order');
    }

    public function suspendDriver()
    {
        return view('admin.master.trawltrucks.suspend-driver');
    }
}
