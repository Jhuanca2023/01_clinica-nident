<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Actualiza los documentos legales (web_terminos) con contenido
 * propio de Lucdesoft — empresa de servicios tecnológicos, Lima · Perú.
 *
 * id_footer_document:
 *   2 → Términos y Condiciones
 *   3 → Política de Privacidad
 */
return new class extends Migration
{
    // ─────────────────────────────────────────────────────────────────────────
    //  POLÍTICA DE PRIVACIDAD
    // ─────────────────────────────────────────────────────────────────────────
    private function politicaPrivacidad(): string
    {
        return <<<'HTML'
<h2 style="color:#1a365d;font-family:Arial,sans-serif;margin-bottom:6px">Política de Privacidad</h2>
<p style="color:#718096;font-size:13px;margin-bottom:24px">Última actualización: Mayo 2026</p>

<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px">
En <strong>Lucdesoft</strong> valoramos profundamente la privacidad de nuestros clientes y visitantes. Esta Política de Privacidad describe
cómo recopilamos, usamos y protegemos la información personal que usted nos proporciona al utilizar nuestro sitio web
<strong>rossety.pe</strong> y nuestros servicios tecnológicos.
</p>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">1. Responsable del Tratamiento</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px">
<strong>Lucdesoft</strong> — Empresa de desarrollo de software y servicios tecnológicos.<br>
RUC: (en trámite)<br>
Dirección: Lima, Perú<br>
Correo: <a href="mailto:contacto@rossety.pe" style="color:#3182ce">contacto@rossety.pe</a><br>
WhatsApp: <a href="https://wa.me/51970048451" style="color:#3182ce">+51 970 048 451</a>
</p>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">2. Información que Recopilamos</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:8px">Recopilamos información cuando usted:</p>
<ul style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px;padding-left:20px">
  <li>Completa el formulario de cotización o contacto</li>
  <li>Se registra como usuario en nuestra plataforma</li>
  <li>Contrata alguno de nuestros servicios tecnológicos</li>
  <li>Navega por nuestro sitio web (datos de sesión y cookies)</li>
  <li>Se suscribe a nuestro boletín de novedades</li>
</ul>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px">
Los datos que podemos recopilar incluyen: nombre completo, correo electrónico, número de teléfono/WhatsApp,
nombre de empresa, descripción del proyecto y datos de navegación (IP, dispositivo, páginas visitadas).
</p>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">3. Finalidad del Tratamiento</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:8px">Usamos su información para:</p>
<ul style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px;padding-left:20px">
  <li>Atender consultas y gestionar cotizaciones de proyectos</li>
  <li>Prestar los servicios de desarrollo web, software a medida y consultoría tecnológica contratados</li>
  <li>Enviar el detalle de su cotización y propuestas técnicas</li>
  <li>Mejorar la experiencia de usuario en nuestro sitio</li>
  <li>Enviar comunicaciones sobre actualizaciones de proyectos en curso</li>
  <li>Cumplir con obligaciones legales y contractuales</li>
  <li>Enviar contenido informativo o promocional solo si usted lo autorizó expresamente</li>
</ul>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">4. Base Legal del Tratamiento</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px">
El tratamiento de sus datos se realiza bajo las siguientes bases legales: <strong>(a)</strong> ejecución de un contrato de servicios tecnológicos;
<strong>(b)</strong> consentimiento explícito del titular; <strong>(c)</strong> interés legítimo de Lucdesoft para mejorar sus servicios;
y <strong>(d)</strong> cumplimiento de obligaciones legales conforme a la Ley N.º 29733, Ley de Protección de Datos Personales del Perú.
</p>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">5. Cookies y Tecnologías de Seguimiento</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px">
Nuestro sitio utiliza cookies técnicas (imprescindibles para el funcionamiento), cookies analíticas (para entender el uso del sitio)
y cookies de preferencias. Puede gestionar o rechazar las cookies no esenciales desde el panel de configuración de cookies
que aparece en su primera visita o desde la configuración de su navegador. La desactivación de cookies técnicas puede
afectar el funcionamiento del sitio.
</p>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">6. Compartición de Datos con Terceros</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:8px">
Lucdesoft <strong>no vende</strong> su información personal a terceros. Podemos compartir datos con:
</p>
<ul style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px;padding-left:20px">
  <li><strong>Proveedores de hosting y nube</strong> necesarios para prestar el servicio</li>
  <li><strong>Herramientas de análisis</strong> (Google Analytics u otras) de forma anonimizada</li>
  <li><strong>Autoridades competentes</strong> cuando sea requerido por ley</li>
  <li><strong>Subcontratistas técnicos</strong> bajo acuerdos de confidencialidad, solo para ejecutar su proyecto</li>
</ul>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">7. Plazos de Conservación</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px">
Conservamos sus datos durante el tiempo necesario para prestar el servicio contratado y hasta 5 años después del fin de la relación
contractual, con el fin de atender posibles reclamaciones o auditorías. Los datos de contacto y cotizaciones no convertidas
se conservan un máximo de 2 años.
</p>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">8. Sus Derechos (ARCO)</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:8px">
Conforme a la Ley N.º 29733, usted tiene derecho a:
</p>
<ul style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px;padding-left:20px">
  <li><strong>Acceso:</strong> conocer qué datos tenemos sobre usted</li>
  <li><strong>Rectificación:</strong> corregir datos inexactos o desactualizados</li>
  <li><strong>Cancelación:</strong> solicitar la eliminación de sus datos</li>
  <li><strong>Oposición:</strong> oponerse a un tratamiento específico</li>
</ul>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px">
Para ejercer estos derechos escriba a <a href="mailto:contacto@rossety.pe" style="color:#3182ce">contacto@rossety.pe</a>
indicando su nombre, el derecho que desea ejercer y adjuntando copia de su documento de identidad.
Responderemos en un plazo máximo de 20 días hábiles.
</p>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">9. Seguridad de la Información</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px">
Implementamos medidas técnicas y organizativas para proteger su información: cifrado HTTPS/TLS en todas las
comunicaciones, autenticación de dos factores en sistemas internos, acceso restringido a datos personales y copias de
seguridad periódicas. No obstante, ningún sistema es 100% seguro; en caso de brecha de seguridad le notificaremos
conforme a la normativa vigente.
</p>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">10. Transferencias Internacionales</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px">
Algunos de nuestros proveedores de infraestructura (servidores en la nube) pueden estar ubicados fuera del Perú.
En tales casos garantizamos que existen salvaguardas adecuadas (contratos de procesamiento de datos o equivalentes)
que protegen su información conforme a estándares internacionales.
</p>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">11. Cambios en esta Política</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px">
Podemos actualizar esta Política de Privacidad periódicamente. Le notificaremos cambios significativos mediante un aviso
visible en nuestro sitio web o por correo electrónico si somos su proveedor activo. La fecha de última actualización
aparece al inicio de este documento.
</p>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">12. Contacto</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:24px">
Si tiene preguntas sobre esta política o el tratamiento de sus datos, contáctenos:<br>
📧 <a href="mailto:contacto@rossety.pe" style="color:#3182ce">contacto@rossety.pe</a><br>
💬 <a href="https://wa.me/51970048451" style="color:#3182ce">WhatsApp +51 970 048 451</a><br>
🌐 <a href="https://rossety.pe" style="color:#3182ce">rossety.pe</a>
</p>

<hr style="border:none;border-top:1px solid #e2e8f0;margin:24px 0">
<p style="font-family:Arial,sans-serif;font-size:12px;color:#a0aec0;text-align:center">
© 2026 Lucdesoft — Todos los derechos reservados · Lima, Perú
</p>
HTML;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  TÉRMINOS Y CONDICIONES
    // ─────────────────────────────────────────────────────────────────────────
    private function terminosCondiciones(): string
    {
        return <<<'HTML'
<h2 style="color:#1a365d;font-family:Arial,sans-serif;margin-bottom:6px">Términos y Condiciones de Servicio</h2>
<p style="color:#718096;font-size:13px;margin-bottom:24px">Última actualización: Mayo 2026</p>

<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px">
Bienvenido a <strong>Lucdesoft</strong>. Al contratar nuestros servicios o utilizar nuestro sitio web <strong>rossety.pe</strong>,
usted acepta los presentes Términos y Condiciones en su totalidad. Le recomendamos leerlos detenidamente antes de iniciar cualquier proyecto.
</p>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">1. Identificación del Prestador</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px">
<strong>Lucdesoft</strong> — Empresa especializada en desarrollo de software, páginas web, aplicaciones móviles y soluciones tecnológicas a medida.<br>
Correo: <a href="mailto:contacto@rossety.pe" style="color:#3182ce">contacto@rossety.pe</a><br>
WhatsApp: <a href="https://wa.me/51970048451" style="color:#3182ce">+51 970 048 451</a><br>
Lima, Perú
</p>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">2. Servicios Ofrecidos</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:8px">Lucdesoft ofrece, entre otros:</p>
<ul style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px;padding-left:20px">
  <li>Desarrollo de páginas web profesionales y landing pages</li>
  <li>Tiendas virtuales (e-commerce) con panel administrativo</li>
  <li>Sistemas de gestión empresarial a medida (ERP, CRM, inventarios)</li>
  <li>Aplicaciones web y móviles (React, Laravel, Flutter)</li>
  <li>Plataformas SaaS y sistemas multi-tenant</li>
  <li>Mantenimiento, soporte técnico y actualización de proyectos existentes</li>
  <li>Consultoría tecnológica y auditoría de sistemas</li>
  <li>Hosting y dominios (como servicio adicional)</li>
</ul>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">3. Proceso de Contratación</h3>
<ol style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px;padding-left:20px">
  <li>El cliente solicita una cotización a través del formulario web, WhatsApp o correo electrónico.</li>
  <li>Lucdesoft elabora una propuesta técnica y económica según los requerimientos indicados.</li>
  <li>El cliente aprueba la propuesta y realiza el pago inicial acordado (generalmente 50 % del total).</li>
  <li>Se inicia el desarrollo conforme al alcance, plazos y entregables definidos en la propuesta.</li>
  <li>Se realizan revisiones y ajustes dentro del alcance acordado durante el proyecto.</li>
  <li>Al finalizar, el cliente realiza el pago del saldo restante y recibe los accesos/entregables finales.</li>
</ol>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">4. Precios y Pagos</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px">
Los precios se expresan en <strong>soles peruanos (S/)</strong> o dólares americanos (USD) según lo indicado en la propuesta.
El pago inicial (adelanto) es requerido para iniciar el proyecto y no es reembolsable una vez iniciado el desarrollo.
Aceptamos transferencias bancarias, Yape, Plin y otros medios acordados. Los precios no incluyen IGV salvo indicación expresa.
</p>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">5. Plazos de Entrega</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px">
Los plazos de entrega estimados se indican en la propuesta. Estos plazos pueden verse afectados por:
retraso en la entrega de materiales por parte del cliente (textos, imágenes, accesos, feedback),
cambios de alcance solicitados durante el proyecto, o situaciones de fuerza mayor. Lucdesoft comunicará
oportunamente cualquier variación en los plazos. El incumplimiento del pago inicial detiene el inicio del proyecto;
el incumplimiento del pago final detiene la entrega y publicación.
</p>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">6. Propiedad Intelectual</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px">
Una vez completado el pago total, el cliente recibe la titularidad del código fuente y los diseños desarrollados
exclusivamente para su proyecto, salvo los componentes de terceros sujetos a licencias propias (frameworks, librerías
open source, plugins, etc.). Lucdesoft conserva el derecho de mencionar el proyecto en su portafolio, salvo acuerdo
de confidencialidad. El cliente garantiza poseer los derechos sobre los materiales que entrega a Lucdesoft (imágenes,
textos, marcas), siendo responsable de cualquier infracción de derechos de terceros.
</p>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">7. Cambios de Alcance</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px">
Las modificaciones que excedan el alcance original de la propuesta (nuevas funcionalidades, cambios estructurales
de diseño, integraciones no contempladas) serán cotizadas como trabajo adicional. Los ajustes menores dentro del
alcance acordado son incluidos sin costo adicional durante la fase de desarrollo.
</p>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">8. Garantía y Soporte Post-Entrega</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px">
Lucdesoft ofrece una garantía de <strong>30 días</strong> después de la entrega final para corregir errores o bugs
del sistema que sean atribuibles al desarrollo realizado. Esta garantía no cubre modificaciones por terceros,
errores por uso incorrecto, ni nuevas funcionalidades. El soporte técnico continuo (después del período de garantía)
puede contratarse por separado.
</p>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">9. Confidencialidad</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px">
Ambas partes se comprometen a mantener la confidencialidad de la información sensible del proyecto (modelos de negocio,
datos de clientes, accesos a sistemas, estrategias). Esta obligación se mantiene vigente durante y después de la
relación contractual.
</p>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">10. Limitación de Responsabilidad</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px">
Lucdesoft no se hace responsable de: pérdidas de datos por falta de copias de seguridad del cliente, daños por
uso indebido del sistema entregado, interrupciones del servicio de hosting de terceros, ni lucro cesante derivado
de fallas del sistema. La responsabilidad máxima de Lucdesoft ante cualquier reclamación estará limitada al monto
pagado por el servicio en cuestión.
</p>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">11. Cancelación del Proyecto</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px">
<strong>Por parte del cliente:</strong> si el cliente cancela el proyecto una vez iniciado el desarrollo, el adelanto
pagado no es reembolsable. Si el desarrollo supera el 50 %, se facturará proporcionalmente el trabajo realizado.<br>
<strong>Por parte de Lucdesoft:</strong> en casos excepcionales (fuerza mayor, incumplimiento de pago), Lucdesoft
puede cancelar el proyecto devolviendo la parte proporcional del adelanto correspondiente al trabajo no realizado.
</p>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">12. Uso Aceptable del Sitio</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px">
Queda prohibido utilizar <strong>rossety.pe</strong> para actividades ilegales, envío de spam, intento de acceso
no autorizado a sistemas, distribución de malware o cualquier acción que perjudique a Lucdesoft o a terceros.
</p>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">13. Ley Aplicable y Jurisdicción</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px">
Estos términos se rigen por las leyes de la República del Perú. Cualquier controversia será resuelta,
en primera instancia, mediante negociación directa entre las partes. De no llegarse a un acuerdo,
las partes se someten a la jurisdicción de los tribunales de Lima, renunciando a cualquier otro fuero.
</p>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">14. Modificaciones</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:16px">
Lucdesoft se reserva el derecho de modificar estos Términos y Condiciones en cualquier momento.
Los cambios serán publicados en esta página con la fecha de actualización. El uso continuado del sitio o
de nuestros servicios tras la publicación de cambios implica su aceptación.
</p>

<h3 style="color:#2b6cb0;font-family:Arial,sans-serif;font-size:15px;margin:20px 0 8px">15. Contacto</h3>
<p style="font-family:Arial,sans-serif;font-size:14px;color:#2d3748;line-height:1.8;margin-bottom:24px">
Para consultas sobre estos términos:<br>
📧 <a href="mailto:contacto@rossety.pe" style="color:#3182ce">contacto@rossety.pe</a><br>
💬 <a href="https://wa.me/51970048451" style="color:#3182ce">WhatsApp +51 970 048 451</a><br>
🌐 <a href="https://rossety.pe" style="color:#3182ce">rossety.pe</a>
</p>

<hr style="border:none;border-top:1px solid #e2e8f0;margin:24px 0">
<p style="font-family:Arial,sans-serif;font-size:12px;color:#a0aec0;text-align:center">
© 2026 Lucdesoft — Todos los derechos reservados · Lima, Perú
</p>
HTML;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  UP
    // ─────────────────────────────────────────────────────────────────────────
    public function up(): void
    {
        if (!Schema::hasTable('web_terminos')) {
            return;
        }

        $now = now();

        // ── Términos y Condiciones (id_footer_document = 2) ───────────────
        $existe = DB::table('web_terminos')
            ->where('id_footer_document', 2)
            ->exists();

        if ($existe) {
            DB::table('web_terminos')
                ->where('id_footer_document', 2)
                ->update([
                    'titulo'      => 'Términos y Condiciones',
                    'descripcion' => $this->terminosCondiciones(),
                    'updated_at'  => $now,
                ]);
        } else {
            DB::table('web_terminos')->insert([
                'id_footer_document' => 2,
                'titulo'             => 'Términos y Condiciones',
                'descripcion'        => $this->terminosCondiciones(),
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        // ── Política de Privacidad (id_footer_document = 3) ──────────────
        $existe3 = DB::table('web_terminos')
            ->where('id_footer_document', 3)
            ->exists();

        if ($existe3) {
            DB::table('web_terminos')
                ->where('id_footer_document', 3)
                ->update([
                    'titulo'      => 'Política de Privacidad',
                    'descripcion' => $this->politicaPrivacidad(),
                    'updated_at'  => $now,
                ]);
        } else {
            DB::table('web_terminos')->insert([
                'id_footer_document' => 3,
                'titulo'             => 'Política de Privacidad',
                'descripcion'        => $this->politicaPrivacidad(),
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  DOWN (restaura a vacío)
    // ─────────────────────────────────────────────────────────────────────────
    public function down(): void
    {
        if (!Schema::hasTable('web_terminos')) {
            return;
        }
        DB::table('web_terminos')
            ->whereIn('id_footer_document', [2, 3])
            ->update(['titulo' => '', 'descripcion' => '', 'updated_at' => now()]);
    }
};
