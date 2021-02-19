<?php

namespace App\Http\Controllers\Admin\Customers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        return view('admin.customers.index');
    }
}
