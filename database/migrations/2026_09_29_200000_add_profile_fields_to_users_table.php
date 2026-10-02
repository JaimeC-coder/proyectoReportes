<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('position', 120)->nullable()->after('email');
            $table->string('signature_path', 2048)->nullable()->after('position');
            $table->boolean('is_active')->default(true)->after('signature_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['position', 'signature_path', 'is_active']);
        });
    }
};
