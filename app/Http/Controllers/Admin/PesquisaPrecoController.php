<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class PesquisaPrecoController extends Controller
{
    public function index(): View
    {
        return view('Admin.PesquisaPreco.index');
    }
}
