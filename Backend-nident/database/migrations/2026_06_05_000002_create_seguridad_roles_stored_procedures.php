<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS USP_SEGURIDAD_ROLES_OBTENER_LISTA');
        DB::unprepared(<<<'SQL'
CREATE PROCEDURE USP_SEGURIDAD_ROLES_OBTENER_LISTA(
  IN id_perfil_ INT
)
BEGIN
  SELECT
    ROW_NUMBER() OVER (ORDER BY SP.id_roles) AS RowIndex,
    SP.id_roles,
    SPU.id_roles_perfil,
    SP.nombre,
    SP.created_at,
    SP.Activo
  FROM seguridad_roles SP
  INNER JOIN seguridad_roles_perfil SPU ON SP.id_roles = SPU.id_roles
  WHERE SPU.id_perfil = id_perfil_;
END
SQL);

        DB::unprepared('DROP PROCEDURE IF EXISTS USP_SEGURIDAD_ROLES_OBTENER_CHECK');
        DB::unprepared(<<<'SQL'
CREATE PROCEDURE USP_SEGURIDAD_ROLES_OBTENER_CHECK(
  IN id_perfil_ INT
)
BEGIN
  SELECT
    ROW_NUMBER() OVER (ORDER BY DM.id_roles) AS RowIndex,
    DM.id_roles,
    DM.nombre,
    DM.created_at,
    DM.Activo
  FROM seguridad_roles DM
  WHERE DM.id_roles NOT IN (
    SELECT SP.id_roles
    FROM seguridad_roles_perfil SP
    WHERE SP.id_perfil = id_perfil_
  );
END
SQL);

        DB::unprepared('DROP PROCEDURE IF EXISTS USP_SEGURIDAD_ROLES_ASIGNAR');
        DB::unprepared(<<<'SQL'
CREATE PROCEDURE USP_SEGURIDAD_ROLES_ASIGNAR(
  IN id_perfil_ INT,
  IN cadena_id_roles TEXT
)
BEGIN
  DECLARE count INT DEFAULT 0;
  DECLARE valor1_ VARCHAR(255);

  read_loop: LOOP
    SET count = count + 1;
    SET valor1_ = SPLIT_STR(cadena_id_roles, '|', count);

    IF valor1_ = '' THEN
      LEAVE read_loop;
    END IF;

    INSERT INTO seguridad_roles_perfil (id_perfil, id_roles, created_at)
    SELECT id_perfil_, valor1_, NOW()
    WHERE NOT EXISTS (
      SELECT 1
      FROM seguridad_roles_perfil
      WHERE id_perfil = id_perfil_ AND id_roles = valor1_
    );
  END LOOP read_loop;
END
SQL);

        DB::unprepared('DROP PROCEDURE IF EXISTS USP_SEGURIDAD_ROLES_OBTENER_ELIMINAR');
        DB::unprepared(<<<'SQL'
CREATE PROCEDURE USP_SEGURIDAD_ROLES_OBTENER_ELIMINAR(
  IN id_roles_perfil_ INT
)
BEGIN
  DELETE FROM seguridad_roles_perfil WHERE id_roles_perfil = id_roles_perfil_;
END
SQL);

        DB::unprepared('DROP PROCEDURE IF EXISTS USP_SEGURIDAD_ROLES_ELIMINAR_MULTIPLE');
        DB::unprepared(<<<'SQL'
CREATE PROCEDURE USP_SEGURIDAD_ROLES_ELIMINAR_MULTIPLE(
  IN cadena_id_roles_perfil TEXT
)
BEGIN
  DECLARE count INT DEFAULT 0;
  DECLARE valor1_ VARCHAR(255);

  read_loop: LOOP
    SET count = count + 1;
    SET valor1_ = SPLIT_STR(cadena_id_roles_perfil, '|', count);

    IF valor1_ = '' THEN
      LEAVE read_loop;
    END IF;

    DELETE FROM seguridad_roles_perfil WHERE id_roles_perfil = valor1_;
  END LOOP read_loop;
END
SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS USP_SEGURIDAD_ROLES_ELIMINAR_MULTIPLE');
        DB::unprepared('DROP PROCEDURE IF EXISTS USP_SEGURIDAD_ROLES_OBTENER_ELIMINAR');
        DB::unprepared('DROP PROCEDURE IF EXISTS USP_SEGURIDAD_ROLES_ASIGNAR');
        DB::unprepared('DROP PROCEDURE IF EXISTS USP_SEGURIDAD_ROLES_OBTENER_CHECK');
        DB::unprepared('DROP PROCEDURE IF EXISTS USP_SEGURIDAD_ROLES_OBTENER_LISTA');
    }
};
