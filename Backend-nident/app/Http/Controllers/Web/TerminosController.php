<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Session;

class TerminosController  extends Controller {

    public function web_terminos(Request $request) {
        $terminosData = DB::table('web_terminos')->whereIn('id_footer_document', [$request->id_footer_document])->get();
        return view('web.pages.terminos.ajax.informacionDocumentos')
        ->with(compact('terminosData'));
    }

}