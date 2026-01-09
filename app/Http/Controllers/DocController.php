<?php

namespace App\Http\Controllers;

class DocController extends Controller
{
    public function index()
    {
        return view('docs.index');
    }
}
