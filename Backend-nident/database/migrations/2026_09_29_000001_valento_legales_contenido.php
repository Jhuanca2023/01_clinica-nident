<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Legales Valento (web_terminos): reemplaza el texto genérico heredado de la
 * plantilla (tienda de polos premium, teléfono +51 900 000 000, correos de
 * Liam Vercelli) por Términos y Política de Privacidad propios de Valento Stile
 * S.A.C.: venta mayorista, personalización con logo y las funciones reales del
 * sitio (cuentas, login con Google, pagos en línea, WhatsApp, libro de
 * reclamaciones, cookies).
 *
 * id_footer_document: 2 → Términos y Condiciones, 3 → Política de Privacidad.
 *
 * Idempotente: no toca un documento que ya contiene el marcador MARCADOR.
 * El título y la fecha los pinta resources/views/web/pages/liam/legal.blade.php;
 * aquí solo va el cuerpo (h2 / p / ul, sin estilos en línea).
 */
return new class extends Migration
{
    private const TABLA = 'web_terminos';

    private const MARCADOR = '<!-- valento-legal-v2 -->';

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLA) || ! Schema::hasColumn(self::TABLA, 'descripcion')) {
            return;
        }

        $documentos = [
            2 => ['titulo' => 'Términos y Condiciones', 'html' => $this->terminos()],
            3 => ['titulo' => 'Política de Privacidad', 'html' => $this->privacidad()],
        ];

        foreach ($documentos as $id => $doc) {
            $fila = DB::table(self::TABLA)->where('id_footer_document', $id)->first();
            if ($fila && str_contains((string) $fila->descripcion, self::MARCADOR)) {
                continue;
            }

            $valores = ['descripcion' => self::MARCADOR . "\n" . $doc['html']];
            if (Schema::hasColumn(self::TABLA, 'titulo')) {
                $valores['titulo'] = $doc['titulo'];
            }
            if (Schema::hasColumn(self::TABLA, 'updated_at')) {
                $valores['updated_at'] = now();
            }

            if ($fila) {
                DB::table(self::TABLA)->where('id_footer_document', $id)->update($valores);
            } else {
                $valores['id_footer_document'] = $id;
                if (Schema::hasColumn(self::TABLA, 'Activo')) {
                    $valores['Activo'] = 'S';
                }
                if (Schema::hasColumn(self::TABLA, 'created_at')) {
                    $valores['created_at'] = now();
                }
                DB::table(self::TABLA)->insert($valores);
            }
        }
    }

    public function down(): void
    {
        // Contenido legal: no se revierte (el anterior era de la plantilla).
    }

    private function terminos(): string
    {
        return <<<'HTML'
<p>Estos Términos y Condiciones regulan el uso del sitio web <a href="https://rossety.pe">rossety.pe</a> (el «Sitio») y las compras y cotizaciones que se realicen a través de él o de sus canales vinculados (WhatsApp y correo electrónico). Al navegar, registrarse, solicitar una cotización o comprar en el Sitio, usted declara haber leído y aceptado estos términos. Si no está de acuerdo, le pedimos no utilizar el Sitio.</p>

<h2>1. Titular del sitio</h2>
<ul>
<li><strong>Razón social:</strong> Valento Stile S.A.C.</li>
<li><strong>RUC:</strong> 20608473159</li>
<li><strong>Domicilio fiscal:</strong> Calle Los Flamencos 318, Urb. Santa Catalina, La Victoria, Lima – Perú</li>
<li><strong>Correo:</strong> <a href="mailto:ventas@valentostile.pe">ventas@valentostile.pe</a></li>
<li><strong>WhatsApp:</strong> <a href="https://wa.me/51964381275">+51 964 381 275</a></li>
</ul>
<p>En adelante, «Valento», «nosotros» o «la empresa».</p>

<h2>2. Quiénes somos y a quién vendemos</h2>
<p>Valento comercializa ropa laboral, uniformes, textil promocional, vestuario deportivo y prendas de protección personal, con stock permanente y opción de personalización con el logo de su empresa. Atendemos a empresas, profesionales, instituciones y colectividades (venta mayorista) y también a personas naturales que compran a través de la tienda en línea.</p>
<p>Cuando el cliente sea un consumidor final, en los términos de la Ley N.° 29571, Código de Protección y Defensa del Consumidor, se aplicarán los derechos que dicha ley le reconoce, los cuales prevalecen sobre cualquier disposición de este documento que resulte contraria.</p>

<h2>3. Uso del sitio y cuenta de usuario</h2>
<p>Puede navegar el catálogo sin registrarse. Para comprar o hacer seguimiento de sus pedidos puede crear una cuenta con correo y contraseña o ingresar con su cuenta de Google. Usted se compromete a:</p>
<ul>
<li>Proporcionar información veraz, completa y actualizada (incluidos DNI o RUC cuando se soliciten para la emisión del comprobante).</li>
<li>Mantener la confidencialidad de su contraseña y avisarnos si detecta un uso no autorizado de su cuenta.</li>
<li>No usar el Sitio para fines ilícitos, fraudulentos o que afecten su funcionamiento o seguridad.</li>
</ul>
<p>Podemos suspender cuentas o anular pedidos cuando existan indicios razonables de fraude, suplantación de identidad o uso indebido.</p>

<h2>4. Productos, fotografías y tallas</h2>
<p>Las fotografías y descripciones del catálogo son referenciales. Pueden existir ligeras variaciones de tono por la configuración de cada pantalla, la iluminación o el lote de producción de la tela, que no constituyen defecto. Ponemos a su disposición una <a href="/guia-de-tallas">guía de tallas</a>; le recomendamos revisarla antes de comprar, sobre todo en pedidos por volumen.</p>
<p>Todos los productos están sujetos a disponibilidad de stock. Si luego de confirmado un pedido algún producto no estuviera disponible, le ofreceremos un modelo, talla o color equivalente o la devolución del importe correspondiente, a su elección.</p>

<h2>5. Precios y cotizaciones</h2>
<ul>
<li>Los precios del Sitio están expresados en soles (S/) e incluyen el IGV, salvo que se indique lo contrario.</li>
<li>Los pedidos por volumen, con personalización o con condiciones especiales se atienden mediante <strong>cotización</strong>. La cotización indica precios, cantidades, plazo de entrega y condiciones de pago, y tiene la vigencia que se señale en ella.</li>
<li>Las promociones y descuentos (incluidos los códigos o beneficios del popup promocional) tienen la vigencia y condiciones publicadas en cada campaña, están sujetos a stock y no son acumulables salvo indicación expresa.</li>
<li>Si por un error evidente un producto se publica con un precio manifiestamente equivocado, le informaremos antes de despachar y usted podrá confirmar la compra al precio correcto o anularla con devolución total de lo pagado.</li>
</ul>

<h2>6. Pedidos y medios de pago</h2>
<p>Puede comprar directamente en el Sitio o enviar su pedido por WhatsApp desde el carrito. Un pedido se considera confirmado cuando el pago ha sido aprobado (o, en pedidos por cotización, cuando se cumplen las condiciones de pago acordadas) y le enviamos la confirmación correspondiente.</p>
<p>Aceptamos los medios de pago habilitados en el checkout, como tarjetas de crédito y débito procesadas por pasarelas de pago certificadas y transferencias o depósitos bancarios. Valento no almacena los datos completos de su tarjeta: estos son tratados directamente por la pasarela de pago. Cuando el pago sea por transferencia, el pedido se procesará una vez verificado el abono; puede adjuntar el comprobante desde su cuenta.</p>
<p>Emitimos boleta o factura electrónica según los datos proporcionados al momento de la compra.</p>

<h2>7. Personalización con logo</h2>
<ul>
<li>Antes de producir, le enviaremos una prueba digital (y, cuando se acuerde, una muestra física) que deberá aprobar por escrito. Una vez aprobada, la producción se realizará conforme a ella.</li>
<li>Usted declara ser titular o contar con autorización para usar los logotipos, marcas, textos e imágenes que nos entregue para personalizar, y asume la responsabilidad frente a reclamos de terceros por su uso.</li>
<li>Podemos rechazar diseños que infrinjan derechos de terceros o que tengan contenido ilícito u ofensivo.</li>
<li>Los plazos de producción de pedidos personalizados se cuentan desde la aprobación del diseño y el cumplimiento de las condiciones de pago de la cotización.</li>
</ul>

<h2>8. Envíos y entrega</h2>
<p>Realizamos envíos a Lima y provincias mediante operadores logísticos. El costo de envío se calcula en el checkout según el destino o se indica en la cotización. El plazo estimado se informa al confirmar el pedido y puede variar por feriados, campañas o causas ajenas a nuestro control; en ese caso le avisaremos oportunamente.</p>
<p>Al recibir el pedido, le pedimos verificar que el paquete llegue cerrado y que el contenido corresponda a lo solicitado. Si detecta daños, faltantes o diferencias, comuníquelo dentro de las 48 horas siguientes a la entrega, con fotografías, a <a href="mailto:ventas@valentostile.pe">ventas@valentostile.pe</a> o por WhatsApp.</p>

<h2>9. Cambios, devoluciones y garantía</h2>
<p>Los cambios y devoluciones se rigen por nuestra <a href="/devoluciones">Política de Devoluciones</a> y la garantía por nuestra <a href="/garantia">Política de Garantía</a>. En general:</p>
<ul>
<li>Aceptamos cambios de productos sin uso, con etiquetas y en su empaque original, dentro del plazo indicado en la Política de Devoluciones.</li>
<li>Los productos personalizados (bordados, estampados o con logo) se fabrican a pedido, por lo que no admiten cambio ni devolución, salvo defecto de fabricación o error nuestro respecto de la prueba aprobada.</li>
<li>La garantía cubre defectos de fabricación. No cubre el desgaste natural, el lavado o planchado inadecuado ni daños por uso indebido.</li>
</ul>

<h2>10. Libro de Reclamaciones</h2>
<p>Conforme a la normativa vigente, ponemos a su disposición un <a href="/libro-reclamaciones">Libro de Reclamaciones virtual</a>. Daremos respuesta a su reclamo o queja en el plazo legal. La presentación de un reclamo no impide acudir a otras vías de solución ni interponer una denuncia ante el Indecopi.</p>

<h2>11. Propiedad intelectual</h2>
<p>La marca Valento, el logotipo, los textos, fotografías, diseños y demás contenidos del Sitio pertenecen a Valento Stile S.A.C. o se usan con autorización de sus titulares. No está permitido reproducirlos, distribuirlos o modificarlos sin nuestra autorización previa y por escrito.</p>

<h2>12. Responsabilidad</h2>
<p>Hacemos lo posible para que el Sitio funcione de forma continua y segura, pero no garantizamos que esté libre de interrupciones o errores. No seremos responsables por demoras o incumplimientos causados por hechos fuera de nuestro control razonable (caso fortuito o fuerza mayor), como desastres naturales, cortes de servicio, paralizaciones o disposiciones de autoridad. Nada en estos términos limita los derechos que la ley reconoce a los consumidores.</p>

<h2>13. Datos personales y cookies</h2>
<p>Tratamos sus datos personales conforme a nuestra <a href="/politica-privacidad">Política de Privacidad</a>, que forma parte de estos términos.</p>

<h2>14. Cambios a estos términos</h2>
<p>Podemos actualizar estos Términos y Condiciones. La versión vigente es la publicada en el Sitio, con su fecha de actualización. Los cambios no afectan los pedidos ya confirmados.</p>

<h2>15. Ley aplicable</h2>
<p>Estos términos se rigen por las leyes de la República del Perú. Cualquier controversia se someterá a los jueces y tribunales de Lima, sin perjuicio de los derechos del consumidor de acudir al Indecopi o a la autoridad que corresponda.</p>

<h2>16. Contacto</h2>
<p>Para cualquier consulta sobre estos términos, escríbanos a <a href="mailto:ventas@valentostile.pe">ventas@valentostile.pe</a> o por WhatsApp al <a href="https://wa.me/51964381275">+51 964 381 275</a>.</p>
HTML;
    }

    private function privacidad(): string
    {
        return <<<'HTML'
<p>En Valento Stile S.A.C. protegemos sus datos personales. Esta Política explica qué datos recogemos en el sitio web <a href="https://rossety.pe">rossety.pe</a> (el «Sitio») y en nuestros canales de atención, para qué los usamos y cómo puede ejercer sus derechos, conforme a la Ley N.° 29733, Ley de Protección de Datos Personales, y su Reglamento.</p>

<h2>1. Responsable del tratamiento</h2>
<ul>
<li><strong>Razón social:</strong> Valento Stile S.A.C.</li>
<li><strong>RUC:</strong> 20608473159</li>
<li><strong>Domicilio:</strong> Calle Los Flamencos 318, Urb. Santa Catalina, La Victoria, Lima – Perú</li>
<li><strong>Correo para privacidad:</strong> <a href="mailto:privacidad@valentostile.pe">privacidad@valentostile.pe</a></li>
</ul>

<h2>2. Qué datos recogemos</h2>
<p>Solo recogemos los datos necesarios según cómo interactúe con nosotros:</p>
<ul>
<li><strong>Cuenta de usuario:</strong> nombre, correo y contraseña (guardada cifrada). Si ingresa con Google, recibimos su nombre, correo y foto de perfil según los permisos que usted autorice.</li>
<li><strong>Compras y cotizaciones:</strong> nombres y apellidos, DNI o RUC, razón social, teléfono, correo, dirección de envío (departamento, provincia, distrito), productos solicitados, detalles de personalización y comprobantes de pago que usted adjunte.</li>
<li><strong>Pagos:</strong> el resultado de la transacción y su identificador. Los datos de la tarjeta los procesa directamente la pasarela de pago; Valento no los almacena.</li>
<li><strong>Formulario de contacto y WhatsApp:</strong> nombre, correo, teléfono, asunto y el contenido de su mensaje.</li>
<li><strong>Libro de Reclamaciones:</strong> los datos que exige la normativa (identificación, domicilio, contacto, detalle del reclamo o queja y archivos adjuntos).</li>
<li><strong>Promociones y novedades:</strong> correo y/o teléfono y la preferencia que marque, si se suscribe o completa el popup promocional.</li>
<li><strong>Navegación:</strong> dirección IP, tipo de navegador y dispositivo, páginas visitadas y cookies (ver punto 8).</li>
</ul>

<h2>3. Para qué usamos sus datos</h2>
<ul>
<li>Gestionar su cuenta, procesar sus pedidos y cotizaciones, coordinar la producción, el envío y la entrega.</li>
<li>Emitir boletas y facturas electrónicas y cumplir obligaciones legales y tributarias.</li>
<li>Atender consultas, reclamos, quejas, cambios y garantías.</li>
<li>Prevenir fraudes y mantener la seguridad del Sitio.</li>
<li>Con su consentimiento previo: enviarle promociones, novedades y ofertas por correo o WhatsApp. Puede retirar este consentimiento en cualquier momento desde el enlace de baja de los correos o escribiéndonos.</li>
</ul>
<p>El tratamiento para las finalidades de los cuatro primeros puntos es necesario para la relación comercial o para cumplir la ley. El envío de publicidad es opcional y no condiciona su compra.</p>

<h2>4. Con quién compartimos sus datos</h2>
<p>No vendemos sus datos. Solo los compartimos, en la medida necesaria, con:</p>
<ul>
<li>Pasarelas de pago y entidades financieras, para procesar sus pagos.</li>
<li>Empresas de courier y transporte, para entregar sus pedidos.</li>
<li>Proveedores de alojamiento web, correo electrónico y servicios tecnológicos que nos dan soporte, bajo obligación de confidencialidad.</li>
<li>Google, cuando usted elige iniciar sesión con su cuenta de Google.</li>
<li>Autoridades competentes (por ejemplo, SUNAT o Indecopi), cuando la ley lo exija.</li>
</ul>
<p>Algunos de estos proveedores pueden almacenar información en servidores fuera del Perú. En esos casos exigimos niveles de protección adecuados conforme a la normativa aplicable.</p>

<h2>5. Cuánto tiempo conservamos sus datos</h2>
<ul>
<li>Datos de cuenta: mientras su cuenta esté activa o hasta que solicite su eliminación.</li>
<li>Datos de pedidos y comprobantes: durante el plazo exigido por la normativa tributaria y contable.</li>
<li>Libro de Reclamaciones: por el plazo mínimo que establece su reglamento.</li>
<li>Datos para publicidad: hasta que retire su consentimiento.</li>
</ul>
<p>Vencidos esos plazos, los datos se eliminan o se anonimizan.</p>

<h2>6. Sus derechos</h2>
<p>Usted puede ejercer en cualquier momento sus derechos de <strong>acceso, rectificación, cancelación y oposición</strong> (derechos ARCO), así como revocar su consentimiento, escribiendo a <a href="mailto:privacidad@valentostile.pe">privacidad@valentostile.pe</a> con el asunto «Derechos ARCO», indicando su nombre, el derecho que desea ejercer y adjuntando copia de su documento de identidad. Le responderemos dentro de los plazos legales.</p>
<p>Si considera que no hemos atendido su solicitud, puede presentar un reclamo ante la Autoridad Nacional de Protección de Datos Personales del Ministerio de Justicia y Derechos Humanos.</p>

<h2>7. Seguridad</h2>
<p>Aplicamos medidas técnicas y organizativas razonables para proteger sus datos: conexión cifrada (HTTPS), contraseñas almacenadas de forma cifrada, acceso restringido al panel de administración y procesamiento de pagos a través de pasarelas certificadas. Ningún sistema es completamente infalible; si detectamos un incidente que afecte sus datos, actuaremos conforme a la ley.</p>

<h2>8. Cookies</h2>
<p>El Sitio usa cookies propias necesarias para su funcionamiento (sesión, carrito de compras, seguridad y recordar sus preferencias, como el cierre del popup promocional). Con su consentimiento, también podemos usar cookies de medición para entender cómo se usa el Sitio. Puede aceptar, configurar o rechazar las cookies no esenciales desde el aviso de cookies, y eliminarlas en cualquier momento desde la configuración de su navegador.</p>

<h2>9. Menores de edad</h2>
<p>El Sitio está dirigido a mayores de 18 años. No recogemos a sabiendas datos de menores de edad; si detectamos que lo hemos hecho, los eliminaremos.</p>

<h2>10. Cambios a esta política</h2>
<p>Podemos actualizar esta Política de Privacidad. La versión vigente es la publicada en el Sitio, con su fecha de actualización. Si el cambio es relevante, se lo informaremos por los medios disponibles.</p>

<h2>11. Contacto</h2>
<p>Para cualquier consulta sobre el tratamiento de sus datos, escríbanos a <a href="mailto:privacidad@valentostile.pe">privacidad@valentostile.pe</a>.</p>
HTML;
    }
};
