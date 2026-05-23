<?php

namespace App\Http\Controllers;

use App\Models\Tool;

class DashboardController extends Controller
{
    public function index()
    {
        $tools = Tool::enabled()->get();
        return view('dashboard', compact('tools'));
    }
}
