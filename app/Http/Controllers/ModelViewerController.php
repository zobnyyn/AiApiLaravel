<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ModelViewerController extends Controller
{
    public function index()
    {
        return view('model-viewer');
    }
}
