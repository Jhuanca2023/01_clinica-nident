# Permisos por rol — TreeView sin SP

## Implementación actual

- **Servicio:** `app/Services/Seguridad/SeguridadMenuTreeviewService.php`
- **Controlador:** `app/Http/Controllers/Api/SistemaMenuTreeviewController.php`
- **Rutas:** `GET /api/roles/listar_treeview`, `POST /api/roles/agregar_permisos`

Basado en el enfoque de AC-Concretos (`listar_treeviewv2` con Query Builder), adaptado a valentostile:

- Módulos (`mod_{id}`)
- Menús (`m{id}`) con `id_menu_padre` opcional
- Objetos de menú (`o{id_menu_objetos}`)
- Objetos de módulo (`om{id_modulo_objetos}`)

## Eliminado (legacy)

- Tabla temporal `datatreeview`
- Procedimiento `USP_SEGURIDAD_PERFIL_MENU_LISTAR_TREEVIEW`

Migración: `2026_05_21_130000_drop_datatreeview_and_treeview_sp.php`

## Despliegue

```bash
php artisan migrate --path=database/migrations/2026_05_21_130000_drop_datatreeview_and_treeview_sp.php
```

Los scripts en `database/scripts/*treeview*.sql` quedan solo como referencia histórica (marcados DEPRECADO).
