<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\WebContactoMensaje;

$count = WebContactoMensaje::count();
echo "Total messages in web_contacto_mensaje: {$count}\n";
foreach (WebContactoMensaje::orderBy('id', 'desc')->limit(5)->get() as $m) {
    echo "ID: {$m->id} | Name: {$m->nombre} | Email: {$m->email} | Subject: {$m->asunto} | Message: {$m->mensaje} | Date: {$m->created_at}\n";
}
