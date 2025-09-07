<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public function viewHome()
    {
        if (auth()->check()) {
            return view('dashboard'); // or any page
        } else{
            return view('login'); // login/home page
            }
    }

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
