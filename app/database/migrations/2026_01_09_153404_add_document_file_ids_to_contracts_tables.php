<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('active_draft_file_id')
                ->nullable()
                ->after('pdf_path')
                ->constrained('document_files')
                ->nullOnDelete();
            $table->foreignId('active_signed_file_id')
                ->nullable()
                ->after('active_draft_file_id')
                ->constrained('document_files')
                ->nullOnDelete();
        });

        Schema::table('contract_annexes', function (Blueprint $table) {
            $table->foreignId('active_draft_file_id')
                ->nullable()
                ->after('pdf_path')
                ->constrained('document_files')
                ->nullOnDelete();
            $table->foreignId('active_signed_file_id')
                ->nullable()
                ->after('active_draft_file_id')
                ->constrained('document_files')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('active_draft_file_id');
            $table->dropConstrainedForeignId('active_signed_file_id');
        });

        Schema::table('contract_annexes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('active_draft_file_id');
            $table->dropConstrainedForeignId('active_signed_file_id');
        });
    }
};
