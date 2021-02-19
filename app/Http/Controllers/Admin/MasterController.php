<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.master.index');
    }
    public function charge_district(Request $request)
    {
        return view('admin.master.charge.district.index');
    }
}
