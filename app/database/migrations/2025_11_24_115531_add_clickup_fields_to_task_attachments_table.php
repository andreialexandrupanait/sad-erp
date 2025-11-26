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
        Schema::table('task_attachments', function (Blueprint $table) {
            $table->string('clickup_attachment_id')->nullable()->after('id');
            $table->string('clickup_url')->nullable()->after('file_path');
            $table->string('thumbnail_small')->nullable()->after('clickup_url');
            $table->string('thumbnail_large')->nullable()->after('thumbnail_small');

            $table->index('clickup_attachment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_attachments', function (Blueprint $table) {
            $table->dropIndex(['clickup_attachment_id']);
            $table->dropColumn(['clickup_attachment_id', 'clickup_url', 'thumbnail_small', 'thumbnail_large']);
        });
    }
};
