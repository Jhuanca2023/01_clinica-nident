<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$sliders = DB::table('web_slider')->get();
echo json_encode($sliders, JSON_PRETTY_PRINT);
