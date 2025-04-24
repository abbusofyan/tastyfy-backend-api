<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class InstructionsController extends Controller
{
    //
    public function index()
    {
        // $data = [
        //     'title' => 'Instructions',
        //     'description' => 'List of all instructions',
        //     // Add more data as needed
        // ];

        return Inertia::render('Instructions/Index');
    }
}
