<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Client;
use App\Models\FinancialRevenue;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->timestamp('last_invoice_at')->nullable()->after('total_incomes');
            $table->index('last_invoice_at');
        });

        // Backfill existing data
        Client::withoutGlobalScopes()->chunk(100, function ($clients) {
            foreach ($clients as $client) {
                $lastInvoiceDate = FinancialRevenue::withoutGlobalScopes()
                    ->where('client_id', $client->id)
                    ->max('occurred_at');

                if ($lastInvoiceDate) {
                    $client->last_invoice_at = $lastInvoiceDate;
                    $client->saveQuietly();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('last_invoice_at');
        });
    }
};
