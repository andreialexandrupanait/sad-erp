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
            $table->string('currency', 3)->default('RON')->after('total_incomes');
        });

        // Backfill: set currency to the most common currency from revenues
        Client::withoutGlobalScopes()->chunk(100, function ($clients) {
            foreach ($clients as $client) {
                $currency = FinancialRevenue::withoutGlobalScopes()
                    ->where('client_id', $client->id)
                    ->selectRaw('currency, COUNT(*) as cnt')
                    ->groupBy('currency')
                    ->orderByDesc('cnt')
                    ->value('currency');

                if ($currency) {
                    $client->currency = $currency;
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
            $table->dropColumn('currency');
        });
    }
};
