<?php

namespace App\View\Composers;

use App\Models\WebFooter;
use App\Traits\ChecksAdminRole;
use Illuminate\View\View;

/**
 * CustomizerComposer
 *
 * Inyecta automáticamente las variables del personalizador de tema
 * en todas las vistas que usan el layout `web.base`.
 *
 * Variables compartidas:
 *  - $isAdmin          (bool)         — true si el usuario es administrador
 *  - $customizerStyles (string|null)  — JSON de estilos guardados en BD, o null
 */
class CustomizerComposer
{
    use ChecksAdminRole;

    public function compose(View $view): void
    {
        $view->with([
            'isAdmin'          => $this->isAdminUser(),
            'customizerStyles' => $this->loadCustomizerStyles(),
        ]);
    }

    /**
     * Carga los estilos del customizador desde la base de datos.
     * Retorna null si no existen o si el JSON está corrupto.
     */
    private function loadCustomizerStyles(): ?string
    {
        $footer = WebFooter::find(1);

        if (! $footer || empty($footer->customizer_styles)) {
            return null;
        }

        $decoded = json_decode($footer->customizer_styles, true);

        return (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
            ? $footer->customizer_styles
            : null;
    }
}
