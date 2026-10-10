<?php

namespace App\Livewire;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Gloudemans\Shoppingcart\Facades\Cart;
use Session;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class OpenPedidos extends Component
{
    public $search = "holaaaa";

    public function updatingSearch()
    {
       // dd("ddddddd");
        // $this->search++;
    }
    public function render()
    {
        
        return view('livewire.open-pedidos');
    }
}
