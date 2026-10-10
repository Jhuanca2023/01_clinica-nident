<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  if (!Schema::hasColumn('web_footer', 'metodos_pago')) return;
  foreach (DB::table('web_footer')->select('id_footer','metodos_pago')->get() as $row) {
   $items = json_decode($row->metodos_pago ?? '[]', true);
   $logos = array_values(array_filter(is_array($items) ? $items : [], static fn($p) => is_array($p) && !empty($p['imagen'])));
   DB::table('web_footer')->where('id_footer',$row->id_footer)->update(['metodos_pago'=>json_encode($logos, JSON_UNESCAPED_UNICODE)]);
  }
 }
 public function down(): void { /* Preserve merchant-uploaded logos. */ }
};
