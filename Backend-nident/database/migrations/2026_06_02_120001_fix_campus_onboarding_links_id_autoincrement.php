<?php

use App\Support\DatabaseAutoIncrementFixer;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        DatabaseAutoIncrementFixer::fix('campus_onboarding_links', 'id_onboarding');
    }

    public function down(): void
    {
        //
    }
};
