<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  if (!Schema::hasTable('web_footer')) return;
  $row = DB::table('web_footer')->where('id_footer', 1)->first();
  if (!$row || (!str_contains(strtolower((string)$row->texto_copyright), 'valento') && !str_contains(strtolower((string)$row->logo_footer), 'valento'))) return;
  DB::table('web_footer')->where('id_footer', 1)->update([
   'descripcion_footer'=>'Detalles únicos para cada ocasión. Ramos, peluches, cajas y accesorios hechos con amor para sorprender a quien más quieres.',
   'logo_footer'=>'rossety/assets/images/inicio/logo-principal.svg',
   'titulo_sobre_nosotros'=>'Categorías', 'titulo_escribenos'=>'Enlaces', 'titulo_llamanos'=>'Contáctanos',
   'links_tienda'=>'[{"label":"Ramos y Detalles","url":"/tienda?cat=ramos"},{"label":"Peluches","url":"/tienda?cat=peluches"},{"label":"Cajas de Regalo","url":"/tienda?cat=cajas"},{"label":"Accesorios","url":"/tienda?cat=accesorios"},{"label":"Hot Wheels y Motos","url":"/tienda?cat=hotwheels"},{"label":"Globos y Complementos","url":"/tienda?cat=complementos"},{"label":"Temporada Girasoles","url":"/tienda?cat=girasoles"}]',
   'links_legales'=>'[{"label":"Inicio","url":"/"},{"label":"Tienda","url":"/tienda"},{"label":"Nosotros","url":"/nosotros"},{"label":"Contacto","url":"/contacto"},{"label":"Métodos de pago","url":"/contacto#pagos"},{"label":"Preguntas frecuentes","url":"/contacto#faq"}]',
   'horarios_data'=>'[{"dia":"Lun - Sáb","hora":"9:00 - 20:00"},{"dia":"Domingos","hora":"con pedido previo"}]',
   'metodos_pago'=>'["BCP","Yape"]',
   'texto_copyright'=>'© '.date('Y').' Detalles Rossety. Todos los derechos reservados.',
   'footer_cta_subtitulo'=>'Detalles que enamoran, momentos que perduran.',
  ]);
 }
 public function down(): void { /* Keep merchant content when rolling back. */ }
};
