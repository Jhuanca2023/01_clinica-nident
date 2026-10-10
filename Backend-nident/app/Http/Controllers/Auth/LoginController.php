<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/ope_pedidos/listar_pedidos';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /** Vista tienda Valento (temp02), no el layout genérico de laravel/ui. */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }
}
