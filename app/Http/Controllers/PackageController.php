<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        // 'package.index' does not exist (was a 500); the page lives in manage_shop_package.blade.php
        return view('manage_shop_package');
    }
}
