<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    //
    public function index() {
        $shop = Auth::user();

        if (! $shop) {
            return response('Unauthorized', 401);
        }
        
        return view("welcome", [
            'llm_generated' => !empty($shop->llm_generated_at),
            'llm_generated_at' => $shop->llm_generated_at,
        ]);
    }
}
