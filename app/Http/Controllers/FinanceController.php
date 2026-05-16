<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function index()
    {
        return view('finance.index'); // Return the finance index view
    }
}
