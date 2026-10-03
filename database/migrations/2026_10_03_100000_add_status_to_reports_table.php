<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // draft = borrador (editable, sin PDF) · final = finalizado (inmutable, con PDF)
            $table->string('status', 20)->default('final')->after('code')->index();
            $table->timestamp('finalized_at')->nullable()->after('pdf_path');
        });

        // Los borradores aún no tienen correlativo: se asigna al finalizar.
        Schema::table('reports', function (Blueprint $table) {
            $table->string('code', 30)->nullable()->change();
        });

        // Los informes que ya existían están finalizados.
        DB::table('reports')->whereNull('finalized_at')->update(['finalized_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        DB::table('reports')->where('status', 'draft')->delete();

        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'finalized_at']);
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->string('code', 30)->nullable(false)->change();
        });
    }
};
