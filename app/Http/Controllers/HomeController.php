<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    //
    public function index() {
        $shop = auth()->user();

        if (! $shop) {
            return response('Unauthorized', 401);
        }
        return view("welcome");
    }
}
