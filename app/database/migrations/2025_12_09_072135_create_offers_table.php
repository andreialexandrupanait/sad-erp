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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->foreignId('template_id')->nullable()->constrained('document_templates')->nullOnDelete();

            $table->string('offer_number')->unique(); // SAD-2025-001-offer
            $table->string('title');
            $table->longText('introduction')->nullable(); // WYSIWYG content before items
            $table->longText('terms')->nullable(); // WYSIWYG content after items

            $table->enum('status', ['draft', 'sent', 'viewed', 'accepted', 'rejected', 'expired'])->default('draft');
            $table->date('valid_until');
            $table->string('public_token', 64)->unique(); // For public link

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->decimal('total', 12, 2)->default(0);
            $table->string('currency', 3)->default('RON');

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('accepted_from_ip')->nullable();
            $table->string('verification_code', 6)->nullable();
            $table->timestamp('verification_code_expires_at')->nullable();

            $table->text('notes')->nullable(); // Internal notes
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index(['client_id', 'status']);
            $table->index('public_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
