<?php

/**
 * SimplEAD ERP - Migration Filler Script
 * This script automatically fills all migration files with complete table structures
 *
 * Usage: php fill_migrations.php
 */

$migrationsPath = __DIR__ . '/database/migrations/';

// Offers Table
$offersFile = glob($migrationsPath . '*_create_offers_table.php')[0] ?? null;
if ($offersFile) {
    $offersContent = <<<'PHP'
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('offer_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->date('valid_until')->nullable();
            $table->enum('status', ['draft', 'sent', 'approved', 'rejected', 'expired'])->default('draft');
            $table->date('sent_date')->nullable();
            $table->date('approved_date')->nullable();
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'client_id', 'status']);
        });
    }
PHP;

    $content = file_get_contents($offersFile);
    $content = preg_replace(
        '/public function up\(\): void\s*\{[^}]*\}/',
        $offersContent,
        $content
    );
    file_put_contents($offersFile, $content);
    echo "✓ Offers migration updated\n";
}

// Contracts Table
$contractsFile = glob($migrationsPath . '*_create_contracts_table.php')[0] ?? null;
if ($contractsFile) {
    $contractsContent = <<<'PHP'
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('offer_id')->nullable()->constrained()->onDelete('set null');
            $table->string('contract_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
            $table->integer('version')->default(1);
            $table->date('signed_date')->nullable();
            $table->string('pdf_path')->nullable();
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'client_id', 'status']);
        });
    }
PHP;

    $content = file_get_contents($contractsFile);
    $content = preg_replace(
        '/public function up\(\): void\s*\{[^}]*\}/',
        $contractsContent,
        $content
    );
    file_put_contents($contractsFile, $content);
    echo "✓ Contracts migration updated\n";
}

// Annexes Table
$annexesFile = glob($migrationsPath . '*_create_annexes_table.php')[0] ?? null;
if ($annexesFile) {
    $annexesContent = <<<'PHP'
    public function up(): void
    {
        Schema::create('annexes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->string('annex_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['annex', 'amendment'])->default('annex');
            $table->integer('version')->default(1);
            $table->date('signed_date')->nullable();
            $table->string('pdf_path')->nullable();
            $table->text('changes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'contract_id']);
        });
    }
PHP;

    $content = file_get_contents($annexesFile);
    $content = preg_replace(
        '/public function up\(\): void\s*\{[^}]*\}/',
        $annexesContent,
        $content
    );
    file_put_contents($annexesFile, $content);
    echo "✓ Annexes migration updated\n";
}

// Subscriptions Table
$subscriptionsFile = glob($migrationsPath . '*_create_subscriptions_table.php')[0] ?? null;
if ($subscriptionsFile) {
    $subscriptionsContent = <<<'PHP'
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('plan_name');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->date('start_date');
            $table->date('next_renewal_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'paused', 'cancelled', 'expired'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'client_id', 'status']);
        });
    }
PHP;

    $content = file_get_contents($subscriptionsFile);
    $content = preg_replace(
        '/public function up\(\): void\s*\{[^}]*\}/',
        $subscriptionsContent,
        $content
    );
    file_put_contents($subscriptionsFile, $content);
    echo "✓ Subscriptions migration updated\n";
}

// Access Credentials Table
$credentialsFile = glob($migrationsPath . '*_create_access_credentials_table.php')[0] ?? null;
if ($credentialsFile) {
    $credentialsContent = <<<'PHP'
    public function up(): void
    {
        Schema::create('access_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('platform');
            $table->string('username')->nullable();
            $table->text('password')->nullable(); // Will be encrypted
            $table->string('url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->integer('access_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'client_id']);
        });
    }
PHP;

    $content = file_get_contents($credentialsFile);
    $content = preg_replace(
        '/public function up\(\): void\s*\{[^}]*\}/',
        $credentialsContent,
        $content
    );
    file_put_contents($credentialsFile, $content);
    echo "✓ Access Credentials migration updated\n";
}

// Files Table
$filesFile = glob($migrationsPath . '*_create_files_table.php')[0] ?? null;
if ($filesFile) {
    $filesContent = <<<'PHP'
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->morphs('fileable'); // Polymorphic relation (client_id, contract_id, etc.)
            $table->string('name');
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable(); // in bytes
            $table->string('folder')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'fileable_type', 'fileable_id']);
        });
    }
PHP;

    $content = file_get_contents($filesFile);
    $content = preg_replace(
        '/public function up\(\): void\s*\{[^}]*\}/',
        $filesContent,
        $content
    );
    file_put_contents($filesFile, $content);
    echo "✓ Files migration updated\n";
}

// Expenses Table
$expensesFile = glob($migrationsPath . '*_create_expenses_table.php')[0] ?? null;
if ($expensesFile) {
    $expensesContent = <<<'PHP'
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->date('expense_date');
            $table->string('category')->nullable();
            $table->string('vendor')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('receipt_path')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'expense_date', 'category']);
        });
    }
PHP;

    $content = file_get_contents($expensesFile);
    $content = preg_replace(
        '/public function up\(\): void\s*\{[^}]*\}/',
        $expensesContent,
        $content
    );
    file_put_contents($expensesFile, $content);
    echo "✓ Expenses migration updated\n";
}

// Revenues Table
$revenuesFile = glob($migrationsPath . '*_create_revenues_table.php')[0] ?? null;
if ($revenuesFile) {
    $revenuesContent = <<<'PHP'
    public function up(): void
    {
        Schema::create('revenues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->date('revenue_date');
            $table->string('source')->nullable(); // e.g., subscription, contract, one-time
            $table->string('payment_method')->nullable();
            $table->string('invoice_number')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'client_id', 'revenue_date']);
        });
    }
PHP;

    $content = file_get_contents($revenuesFile);
    $content = preg_replace(
        '/public function up\(\): void\s*\{[^}]*\}/',
        $revenuesContent,
        $content
    );
    file_put_contents($revenuesFile, $content);
    echo "✓ Revenues migration updated\n";
}

// Audit Logs Table
$auditFile = glob($migrationsPath . '*_create_audit_logs_table.php')[0] ?? null;
if ($auditFile) {
    $auditContent = <<<'PHP'
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action'); // e.g., created, updated, deleted, viewed
            $table->string('model_type'); // e.g., App\Models\Client
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'user_id', 'model_type', 'created_at']);
        });
    }
PHP;

    $content = file_get_contents($auditFile);
    $content = preg_replace(
        '/public function up\(\): void\s*\{[^}]*\}/',
        $auditContent,
        $content
    );
    file_put_contents($auditFile, $content);
    echo "✓ Audit Logs migration updated\n";
}

// System Settings Table
$settingsFile = glob($migrationsPath . '*_create_system_settings_table.php')[0] ?? null;
if ($settingsFile) {
    $settingsContent = <<<'PHP'
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, json, integer
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'key']);
        });
    }
PHP;

    $content = file_get_contents($settingsFile);
    $content = preg_replace(
        '/public function up\(\): void\s*\{[^}]*\}/',
        $settingsContent,
        $content
    );
    file_put_contents($settingsFile, $content);
    echo "✓ System Settings migration updated\n";
}

echo "\n";
echo "========================================\n";
echo "All migrations updated successfully!\n";
echo "========================================\n";
echo "\n";
echo "Next step: Run migrations\n";
echo "  docker compose exec erp_app php artisan migrate\n";
echo "\n";
