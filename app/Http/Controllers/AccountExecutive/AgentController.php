<?php

namespace App\Http\Controllers\AccountExecutive;

use App\Http\Controllers\Controller;

class AgentController extends Controller
{
    public function index()
    {
        return view('admin.master.account-executive.agent.index');
    }

    public function detail()
    {
        return view('admin.master.account-executive.agent.detail');
    }
}
