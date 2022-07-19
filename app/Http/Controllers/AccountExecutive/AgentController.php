<?php

namespace App\Http\Controllers\AccountExecutive;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function index()
    {
        return view('admin.master.account-executive.agent.index');
    }

    public function detail(Request $request, $userId, $period)
    {
        return view('admin.master.account-executive.agent.detail');
    }
}
