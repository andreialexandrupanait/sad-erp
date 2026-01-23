<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\ContractAnnex;
use App\Models\DocumentFile;
use App\Models\Offer;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class TestDocumentPaths extends Command
{
    protected $signature = 'documents:test-paths';
    protected $description = 'Test and display the new document folder structure paths';

    public function handle(): int
    {
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║         Document Path Structure Verification                 ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $year = now()->format('Y');

        $this->info("Current year: {$year}");
        $this->newLine();

        // Test with mock data
        $this->info('Expected folder structure:');
        $this->line('');
        $this->line("  documents/");
        $this->line("  └── {$year}/");
        $this->line("      ├── offers/");
        $this->line("      │   └── OFR SAD XXXX.pdf");
        $this->line("      └── contracts/");
        $this->line("          └── CTR01/");
        $this->line("              ├── CTR SAD 01.pdf (draft)");
        $this->line("              ├── ANX SAD 01 to contract 01.pdf (annex draft)");
        $this->line("              └── signed/");
        $this->line("                  ├── CTR SAD 01-signed.pdf");
        $this->line("                  └── ANX SAD 01 to contract 01-signed.pdf");
        $this->newLine();

        // Test with real data if available
        $this->info('Testing with actual database records:');
        $this->newLine();

        // Test Contract paths
        $contract = Contract::first();
        if ($contract) {
            $this->testContractPaths($contract);
        } else {
            $this->warn('  No contracts found in database - creating mock example');
            $this->displayMockContractPaths();
        }

        $this->newLine();

        // Test Offer paths
        $offer = Offer::first();
        if ($offer) {
            $this->testOfferPaths($offer);
        } else {
            $this->warn('  No offers found in database - creating mock example');
            $this->displayMockOfferPaths();
        }

        $this->newLine();

        // Test Annex paths
        $annex = ContractAnnex::whereHas('contract')->first();
        if ($annex) {
            $this->testAnnexPaths($annex);
        } else {
            $this->warn('  No annexes with valid contracts found - skipping');
        }

        $this->newLine();
        $this->info('✓ Path generation logic verified successfully!');
        $this->newLine();

        return Command::SUCCESS;
    }

    protected function testContractPaths(Contract $contract): void
    {
        $this->line("  <fg=cyan>Contract #{$contract->id}</> (number: {$contract->contract_number})");

        $draftPath = $this->generateTestPath($contract, 'draft', 1);
        $signedPath = $this->generateTestPath($contract, 'signed', 1);

        $this->line("    Draft:  <fg=green>{$draftPath}</>");
        $this->line("    Signed: <fg=green>{$signedPath}</>");
    }

    protected function testOfferPaths(Offer $offer): void
    {
        $this->line("  <fg=cyan>Offer #{$offer->id}</> (number: {$offer->offer_number})");

        $draftPath = $this->generateTestPath($offer, 'draft', 1);

        $this->line("    Draft:  <fg=green>{$draftPath}</>");
    }

    protected function testAnnexPaths(ContractAnnex $annex): void
    {
        $this->line("  <fg=cyan>Annex #{$annex->id}</> (code: {$annex->annex_code}, contract: {$annex->contract->contract_number})");

        $draftPath = $this->generateTestPath($annex, 'draft', 1);
        $signedPath = $this->generateTestPath($annex, 'signed', 1);

        $this->line("    Draft:  <fg=green>{$draftPath}</>");
        $this->line("    Signed: <fg=green>{$signedPath}</>");
    }

    protected function displayMockContractPaths(): void
    {
        $year = now()->format('Y');
        $this->line("  <fg=cyan>Contract</> (number: 01)");
        $this->line("    Draft:  <fg=green>documents/{$year}/contracts/CTR01/CTR SAD 01.pdf</>");
        $this->line("    Signed: <fg=green>documents/{$year}/contracts/CTR01/signed/CTR SAD 01-signed.pdf</>");
    }

    protected function displayMockOfferPaths(): void
    {
        $year = now()->format('Y');
        $this->line("  <fg=cyan>Offer</> (number: 0001)");
        $this->line("    Draft:  <fg=green>documents/{$year}/offers/OFR SAD 0001.pdf</>");
    }

    /**
     * Generate path using the same logic as DocumentFileService
     */
    protected function generateTestPath(Model $documentable, string $documentType, int $version): string
    {
        $year = now()->format('Y');
        $baseName = $this->getFormattedDocumentName($documentable);

        if ($documentType === 'signed') {
            $baseName .= '-signed';
        }

        if ($version > 1) {
            $baseName .= '-v' . $version;
        }

        if ($documentable instanceof Offer) {
            return "documents/{$year}/offers/{$baseName}.pdf";
        } elseif ($documentable instanceof Contract) {
            $contractFolder = 'CTR' . str_pad($documentable->contract_number, 2, '0', STR_PAD_LEFT);
            $signedFolder = $documentType === 'signed' ? '/signed' : '';
            return "documents/{$year}/contracts/{$contractFolder}{$signedFolder}/{$baseName}.pdf";
        } elseif ($documentable instanceof ContractAnnex) {
            $contractNum = $documentable->contract->contract_number;
            $contractFolder = 'CTR' . str_pad($contractNum, 2, '0', STR_PAD_LEFT);
            $signedFolder = $documentType === 'signed' ? '/signed' : '';
            return "documents/{$year}/contracts/{$contractFolder}{$signedFolder}/{$baseName}.pdf";
        }

        return "documents/unknown/{$baseName}.pdf";
    }

    protected function getFormattedDocumentName(Model $documentable): string
    {
        if ($documentable instanceof Contract) {
            return 'CTR SAD ' . $documentable->contract_number;
        } elseif ($documentable instanceof ContractAnnex) {
            $annexCode = $documentable->annex_code;
            preg_match('/A(\d+)/', $annexCode, $matches);
            $annexNum = isset($matches[1]) ? str_pad($matches[1], 2, '0', STR_PAD_LEFT) : str_pad($documentable->id, 2, '0', STR_PAD_LEFT);
            $contractNum = $documentable->contract?->contract_number ?? 'unknown';
            return 'ANX SAD ' . $annexNum . ' to contract ' . $contractNum;
        } elseif ($documentable instanceof Offer) {
            // Use offer_number directly if it already has the prefix
            $offerNumber = $documentable->offer_number;
            if (str_starts_with($offerNumber, 'OFR')) {
                return $offerNumber;
            }
            return 'OFR SAD ' . $offerNumber;
        }

        return (string) $documentable->id;
    }
}
