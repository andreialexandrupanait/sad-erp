<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;

class TranslationsReport extends Command
{
    protected $signature = 'translations:report
                            {--compare= : Compare two languages (e.g., --compare=en,ro)}
                            {--unused : Find unused translation keys}
                            {--duplicates : Find duplicate translation values}
                            {--export= : Export to file (json|csv)}
                            {--lang= : Analyze specific language}';

    protected $description = 'Analyze translation coverage and find missing, unused, or duplicate keys';

    protected string $langPath;
    protected array $sourceLocations;

    public function __construct()
    {
        parent::__construct();
        $this->langPath = base_path('lang');
        $this->sourceLocations = [
            base_path('app'),
            base_path('resources/views'),
        ];
    }

    public function handle(): int
    {
        $this->newLine();
        $this->components->info('Translation Report');
        $this->newLine();

        // Determine what to do based on options
        if ($compare = $this->option('compare')) {
            return $this->compareLanguages($compare);
        }

        if ($this->option('unused')) {
            return $this->findUnusedKeys();
        }

        if ($this->option('duplicates')) {
            return $this->findDuplicates();
        }

        if ($lang = $this->option('lang')) {
            return $this->analyzeLanguage($lang);
        }

        // Default: show overview of all languages
        return $this->showOverview();
    }

    protected function showOverview(): int
    {
        $languages = $this->getAvailableLanguages();

        if (empty($languages)) {
            $this->components->error('No language directories found in ' . $this->langPath);
            return Command::FAILURE;
        }

        $this->components->twoColumnDetail('<fg=cyan>Language</>', '<fg=cyan>Files / Keys</>');

        $data = [];
        foreach ($languages as $lang) {
            $files = $this->getTranslationFiles($lang);
            $totalKeys = 0;

            foreach ($files as $file) {
                $keys = $this->getKeysFromFile($file);
                $totalKeys += count($keys);
            }

            $data[$lang] = [
                'files' => count($files),
                'keys' => $totalKeys,
            ];

            $this->components->twoColumnDetail(
                strtoupper($lang),
                count($files) . ' files / ' . $totalKeys . ' keys'
            );
        }

        $this->newLine();

        // Show per-file breakdown for each language
        foreach ($languages as $lang) {
            $this->components->info("Files in '{$lang}':");

            $files = $this->getTranslationFiles($lang);
            foreach ($files as $file) {
                $keys = $this->getKeysFromFile($file);
                $filename = basename($file, '.php');
                $this->components->twoColumnDetail("  {$filename}.php", count($keys) . ' keys');
            }
            $this->newLine();
        }

        // Calculate coverage if multiple languages
        if (count($languages) >= 2) {
            $this->components->info('Quick Coverage Check (en vs ro):');
            $this->compareLanguages('en,ro', true);
        }

        return Command::SUCCESS;
    }

    protected function compareLanguages(string $compare, bool $summaryOnly = false): int
    {
        $langs = explode(',', $compare);

        if (count($langs) !== 2) {
            $this->components->error('Please provide exactly two languages separated by comma (e.g., --compare=en,ro)');
            return Command::FAILURE;
        }

        [$lang1, $lang2] = array_map('trim', $langs);

        $keys1 = $this->getAllKeysForLanguage($lang1);
        $keys2 = $this->getAllKeysForLanguage($lang2);

        $missingIn2 = array_diff(array_keys($keys1), array_keys($keys2));
        $missingIn1 = array_diff(array_keys($keys2), array_keys($keys1));

        // Calculate coverage
        $totalKeys = count(array_unique(array_merge(array_keys($keys1), array_keys($keys2))));
        $coverage1 = $totalKeys > 0 ? round((count($keys1) / $totalKeys) * 100, 1) : 0;
        $coverage2 = $totalKeys > 0 ? round((count($keys2) / $totalKeys) * 100, 1) : 0;

        $this->components->twoColumnDetail("Total unique keys", $totalKeys);
        $this->components->twoColumnDetail(strtoupper($lang1) . " coverage", "{$coverage1}% (" . count($keys1) . " keys)");
        $this->components->twoColumnDetail(strtoupper($lang2) . " coverage", "{$coverage2}% (" . count($keys2) . " keys)");
        $this->newLine();

        if ($summaryOnly) {
            if (count($missingIn2) > 0 || count($missingIn1) > 0) {
                $this->components->warn(count($missingIn2) . " keys missing in {$lang2}, " . count($missingIn1) . " keys missing in {$lang1}");
            } else {
                $this->components->info('Both languages have identical key coverage!');
            }
            return Command::SUCCESS;
        }

        // Show missing keys
        if (!empty($missingIn2)) {
            $this->components->error("Missing in '{$lang2}' (" . count($missingIn2) . " keys):");
            $grouped = $this->groupKeysByFile($missingIn2);
            foreach ($grouped as $file => $keys) {
                $this->line("  <fg=yellow>{$file}.php</>:");
                foreach ($keys as $key) {
                    $this->line("    - {$key}");
                }
            }
            $this->newLine();
        }

        if (!empty($missingIn1)) {
            $this->components->error("Missing in '{$lang1}' (" . count($missingIn1) . " keys):");
            $grouped = $this->groupKeysByFile($missingIn1);
            foreach ($grouped as $file => $keys) {
                $this->line("  <fg=yellow>{$file}.php</>:");
                foreach ($keys as $key) {
                    $this->line("    - {$key}");
                }
            }
            $this->newLine();
        }

        if (empty($missingIn1) && empty($missingIn2)) {
            $this->components->info('Both languages have identical key coverage!');
        }

        // Export if requested
        if ($export = $this->option('export')) {
            $this->exportReport($export, [
                'comparison' => [$lang1, $lang2],
                'missing_in_' . $lang2 => $missingIn2,
                'missing_in_' . $lang1 => $missingIn1,
                'coverage' => [
                    $lang1 => $coverage1,
                    $lang2 => $coverage2,
                ],
            ]);
        }

        return Command::SUCCESS;
    }

    protected function findUnusedKeys(): int
    {
        $this->components->info('Scanning codebase for translation usage...');

        // Get all defined keys
        $definedKeys = [];
        foreach ($this->getAvailableLanguages() as $lang) {
            $langKeys = $this->getAllKeysForLanguage($lang);
            $definedKeys = array_merge($definedKeys, array_keys($langKeys));
        }
        $definedKeys = array_unique($definedKeys);

        // Scan source files for usage
        $usedKeys = $this->scanSourceForTranslationUsage();

        // Find unused
        $unused = array_diff($definedKeys, $usedKeys);

        if (empty($unused)) {
            $this->components->info('All translation keys are being used!');
            return Command::SUCCESS;
        }

        $this->components->warn('Found ' . count($unused) . ' potentially unused keys:');
        $this->newLine();

        $grouped = $this->groupKeysByFile($unused);
        foreach ($grouped as $file => $keys) {
            $this->line("<fg=yellow>{$file}.php</>:");
            foreach ($keys as $key) {
                $this->line("  - {$key}");
            }
        }

        $this->newLine();
        $this->components->info('Note: Some keys may be used dynamically and not detected.');

        if ($export = $this->option('export')) {
            $this->exportReport($export, ['unused_keys' => $unused]);
        }

        return Command::SUCCESS;
    }

    protected function findDuplicates(): int
    {
        $lang = $this->option('lang') ?? 'en';

        $this->components->info("Finding duplicate values in '{$lang}'...");

        $allTranslations = $this->getAllKeysForLanguage($lang);

        // Group by value
        $byValue = [];
        foreach ($allTranslations as $key => $value) {
            if (is_string($value) && strlen($value) > 3) { // Skip very short values
                $byValue[$value][] = $key;
            }
        }

        // Filter to only duplicates
        $duplicates = array_filter($byValue, fn($keys) => count($keys) > 1);

        if (empty($duplicates)) {
            $this->components->info('No duplicate translation values found!');
            return Command::SUCCESS;
        }

        $this->components->warn('Found ' . count($duplicates) . ' duplicate values:');
        $this->newLine();

        foreach ($duplicates as $value => $keys) {
            $displayValue = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
            $this->line("<fg=cyan>\"{$displayValue}\"</> used in:");
            foreach ($keys as $key) {
                $this->line("  - {$key}");
            }
            $this->newLine();
        }

        if ($export = $this->option('export')) {
            $this->exportReport($export, ['duplicates' => $duplicates]);
        }

        return Command::SUCCESS;
    }

    protected function analyzeLanguage(string $lang): int
    {
        $langPath = "{$this->langPath}/{$lang}";

        if (!is_dir($langPath)) {
            $this->components->error("Language '{$lang}' not found at {$langPath}");
            return Command::FAILURE;
        }

        $this->components->info("Analyzing '{$lang}' translations:");
        $this->newLine();

        $files = $this->getTranslationFiles($lang);
        $totalKeys = 0;
        $emptyValues = [];

        foreach ($files as $file) {
            $filename = basename($file, '.php');
            $translations = include $file;

            if (!is_array($translations)) {
                $this->components->warn("  {$filename}.php - Invalid format (not an array)");
                continue;
            }

            $keys = $this->flattenArray($translations);
            $keyCount = count($keys);
            $totalKeys += $keyCount;

            // Check for empty values
            foreach ($keys as $key => $value) {
                if (empty($value) && $value !== '0') {
                    $emptyValues[] = "{$filename}.{$key}";
                }
            }

            $this->components->twoColumnDetail("  {$filename}.php", "{$keyCount} keys");
        }

        $this->newLine();
        $this->components->twoColumnDetail('<fg=green>Total</>', "{$totalKeys} keys in " . count($files) . " files");

        if (!empty($emptyValues)) {
            $this->newLine();
            $this->components->warn('Empty values found (' . count($emptyValues) . '):');
            foreach ($emptyValues as $key) {
                $this->line("  - {$key}");
            }
        }

        return Command::SUCCESS;
    }

    protected function getAvailableLanguages(): array
    {
        $languages = [];
        $dirs = File::directories($this->langPath);

        foreach ($dirs as $dir) {
            $languages[] = basename($dir);
        }

        return $languages;
    }

    protected function getTranslationFiles(string $lang): array
    {
        $langPath = "{$this->langPath}/{$lang}";

        if (!is_dir($langPath)) {
            return [];
        }

        return File::glob("{$langPath}/*.php");
    }

    protected function getKeysFromFile(string $file): array
    {
        if (!file_exists($file)) {
            return [];
        }

        $translations = include $file;

        if (!is_array($translations)) {
            return [];
        }

        return $this->flattenArray($translations);
    }

    protected function getAllKeysForLanguage(string $lang): array
    {
        $allKeys = [];

        foreach ($this->getTranslationFiles($lang) as $file) {
            $filename = basename($file, '.php');
            $keys = $this->getKeysFromFile($file);

            foreach ($keys as $key => $value) {
                $allKeys["{$filename}.{$key}"] = $value;
            }
        }

        return $allKeys;
    }

    protected function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    protected function groupKeysByFile(array $keys): array
    {
        $grouped = [];

        foreach ($keys as $key) {
            $parts = explode('.', $key);
            $file = $parts[0];
            $keyWithoutFile = implode('.', array_slice($parts, 1));

            $grouped[$file][] = $keyWithoutFile ?: $key;
        }

        ksort($grouped);
        return $grouped;
    }

    protected function scanSourceForTranslationUsage(): array
    {
        $usedKeys = [];

        // Patterns to match translation function calls
        $patterns = [
            '/__\([\'"]([^\'"]+)[\'"]\)/',           // __('key')
            '/trans\([\'"]([^\'"]+)[\'"]\)/',        // trans('key')
            '/@lang\([\'"]([^\'"]+)[\'"]\)/',        // @lang('key')
            '/Lang::get\([\'"]([^\'"]+)[\'"]\)/',    // Lang::get('key')
            '/trans_choice\([\'"]([^\'"]+)[\'"]\)/', // trans_choice('key')
        ];

        foreach ($this->sourceLocations as $location) {
            if (!is_dir($location)) {
                continue;
            }

            $finder = new Finder();
            $finder->files()
                ->in($location)
                ->name(['*.php', '*.blade.php'])
                ->notPath(['vendor', 'node_modules', 'storage']);

            foreach ($finder as $file) {
                $content = $file->getContents();

                foreach ($patterns as $pattern) {
                    if (preg_match_all($pattern, $content, $matches)) {
                        foreach ($matches[1] as $key) {
                            $usedKeys[] = $key;
                        }
                    }
                }
            }
        }

        return array_unique($usedKeys);
    }

    protected function exportReport(string $format, array $data): void
    {
        $filename = 'translations_report_' . date('Y-m-d_His');

        switch (strtolower($format)) {
            case 'json':
                $filepath = base_path("{$filename}.json");
                File::put($filepath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                break;

            case 'csv':
                $filepath = base_path("{$filename}.csv");
                $csv = $this->arrayToCsv($data);
                File::put($filepath, $csv);
                break;

            default:
                $this->components->error("Unknown export format: {$format}. Use 'json' or 'csv'.");
                return;
        }

        $this->components->info("Report exported to: {$filepath}");
    }

    protected function arrayToCsv(array $data): string
    {
        $output = fopen('php://temp', 'r+');

        // Flatten nested arrays for CSV
        foreach ($data as $section => $values) {
            fputcsv($output, [$section]);

            if (is_array($values)) {
                foreach ($values as $key => $value) {
                    if (is_array($value)) {
                        fputcsv($output, array_merge([$key], $value));
                    } else {
                        fputcsv($output, [$key, $value]);
                    }
                }
            } else {
                fputcsv($output, [$values]);
            }

            fputcsv($output, []); // Empty line between sections
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
