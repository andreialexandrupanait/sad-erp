<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Converts contract numbers from old format (CTR-2026-16) to simple format (16).
     */
    public function up(): void
    {
        // Get all contracts with old format
        $contracts = DB::table('contracts')
            ->where('contract_number', 'LIKE', 'CTR-%')
            ->get(['id', 'contract_number']);

        foreach ($contracts as $contract) {
            // Extract the last numeric part (e.g., CTR-2026-16 -> 16)
            if (preg_match('/(\d+)$/', $contract->contract_number, $matches)) {
                $newNumber = sprintf('%02d', intval($matches[1]));

                DB::table('contracts')
                    ->where('id', $contract->id)
                    ->update(['contract_number' => $newNumber]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * Note: This is a best-effort rollback - the original prefix/year info is lost.
     */
    public function down(): void
    {
        // Get all contracts with simple numeric format
        $contracts = DB::table('contracts')
            ->whereRaw("contract_number REGEXP '^[0-9]+$'")
            ->get(['id', 'contract_number', 'organization_id', 'created_at']);

        foreach ($contracts as $contract) {
            $year = date('Y', strtotime($contract->created_at));
            $org = DB::table('organizations')->find($contract->organization_id);
            $prefix = 'CTR';
            if ($org && $org->code) {
                $prefix = 'CTR-' . $org->code;
            }

            $oldNumber = sprintf('%s-%d-%s', $prefix, $year, $contract->contract_number);

            DB::table('contracts')
                ->where('id', $contract->id)
                ->update(['contract_number' => $oldNumber]);
        }
    }
};
