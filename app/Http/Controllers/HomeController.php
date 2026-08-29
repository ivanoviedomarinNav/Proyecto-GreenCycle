<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Muestra la página de inicio.
     */
    public function index(): View
    {
        return view('home');
    }
}
