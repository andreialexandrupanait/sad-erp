<?php

namespace Tests\Unit\Services\Financial;

use App\Models\FinancialExpense;
use App\Models\SettingOption;
use App\Models\User;
use App\Models\Organization;
use App\Services\Financial\ExpenseImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for ExpenseImportService
 *
 * @covers \App\Services\Financial\ExpenseImportService
 */
class ExpenseImportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ExpenseImportService $service;
    protected User $user;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ExpenseImportService();

        // Create test organization and user
        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
    }

    /** @test */
    public function it_validates_valid_expense_row(): void
    {
        $data = [
            'document_name' => 'Invoice 001',
            'amount' => 150.50,
            'currency' => 'RON',
            'occurred_at' => '2025-01-15',
        ];

        [$isValid, $errors] = $this->service->validateRow($data);

        $this->assertTrue($isValid);
        $this->assertEmpty($errors);
    }

    /** @test */
    public function it_rejects_invalid_expense_row_missing_document_name(): void
    {
        $data = [
            'document_name' => '',
            'amount' => 150.50,
            'currency' => 'RON',
            'occurred_at' => '2025-01-15',
        ];

        [$isValid, $errors] = $this->service->validateRow($data);

        $this->assertFalse($isValid);
        $this->assertNotEmpty($errors);
    }

    /** @test */
    public function it_rejects_invalid_currency(): void
    {
        $data = [
            'document_name' => 'Invoice 001',
            'amount' => 150.50,
            'currency' => 'USD', // Not allowed
            'occurred_at' => '2025-01-15',
        ];

        [$isValid, $errors] = $this->service->validateRow($data);

        $this->assertFalse($isValid);
    }

    /** @test */
    public function it_rejects_negative_amount(): void
    {
        $data = [
            'document_name' => 'Invoice 001',
            'amount' => -100,
            'currency' => 'RON',
            'occurred_at' => '2025-01-15',
        ];

        [$isValid, $errors] = $this->service->validateRow($data);

        $this->assertFalse($isValid);
    }

    /** @test */
    public function it_imports_valid_csv_data(): void
    {
        $csvData = [
            ['document_name', 'amount', 'currency', 'occurred_at', 'note'],
            ['Invoice 001', '150.50', 'RON', '2025-01-15', 'Test note'],
            ['Invoice 002', '200.00', 'EUR', '2025-01-16', ''],
        ];

        $stats = $this->service->import(
            $csvData,
            $this->organization->id,
            $this->user->id
        );

        $this->assertEquals(2, $stats['imported']);
        $this->assertEquals(0, $stats['skipped']);
        $this->assertEmpty($stats['errors']);

        // Verify database records
        $this->assertDatabaseCount('financial_expenses', 2);

        $this->assertDatabaseHas('financial_expenses', [
            'document_name' => 'Invoice 001',
            'amount' => 150.50,
            'currency' => 'RON',
        ]);
    }

    /** @test */
    public function it_skips_invalid_rows_and_continues_processing(): void
    {
        $csvData = [
            ['document_name', 'amount', 'currency', 'occurred_at'],
            ['Invoice 001', '150.50', 'RON', '2025-01-15'], // Valid
            ['', '200.00', 'EUR', '2025-01-16'], // Invalid - empty name
            ['Invoice 003', '300.00', 'RON', '2025-01-17'], // Valid
        ];

        $stats = $this->service->import(
            $csvData,
            $this->organization->id,
            $this->user->id
        );

        $this->assertEquals(2, $stats['imported']);
        $this->assertEquals(1, $stats['skipped']);
        $this->assertCount(1, $stats['errors']);
    }

    /** @test */
    public function it_skips_empty_rows(): void
    {
        $csvData = [
            ['document_name', 'amount', 'currency', 'occurred_at'],
            ['Invoice 001', '150.50', 'RON', '2025-01-15'],
            ['', '', '', ''], // Empty row
            ['Invoice 002', '200.00', 'RON', '2025-01-16'],
        ];

        $stats = $this->service->import(
            $csvData,
            $this->organization->id,
            $this->user->id
        );

        $this->assertEquals(2, $stats['imported']);
        $this->assertEquals(0, $stats['skipped']); // Empty rows are silently skipped
    }

    /** @test */
    public function it_handles_column_count_mismatch(): void
    {
        $csvData = [
            ['document_name', 'amount', 'currency', 'occurred_at'],
            ['Invoice 001', '150.50', 'RON'], // Missing column
        ];

        $stats = $this->service->import(
            $csvData,
            $this->organization->id,
            $this->user->id
        );

        $this->assertEquals(0, $stats['imported']);
        $this->assertEquals(1, $stats['skipped']);
        $this->assertStringContainsString('Column count mismatch', $stats['errors'][0]);
    }

    /** @test */
    public function it_performs_dry_run_without_saving(): void
    {
        $csvData = [
            ['document_name', 'amount', 'currency', 'occurred_at'],
            ['Invoice 001', '150.50', 'RON', '2025-01-15'],
        ];

        $stats = $this->service->import(
            $csvData,
            $this->organization->id,
            $this->user->id,
            dryRun: true
        );

        $this->assertEquals(1, $stats['imported']);
        $this->assertDatabaseCount('financial_expenses', 0);
    }

    /** @test */
    public function it_sets_year_and_month_from_occurred_date(): void
    {
        $csvData = [
            ['document_name', 'amount', 'currency', 'occurred_at'],
            ['Invoice 001', '150.50', 'RON', '2025-03-15'],
        ];

        $this->service->import(
            $csvData,
            $this->organization->id,
            $this->user->id
        );

        $this->assertDatabaseHas('financial_expenses', [
            'document_name' => 'Invoice 001',
            'year' => 2025,
            'month' => 3,
        ]);
    }

    /** @test */
    public function it_finds_category_by_exact_match(): void
    {
        // Create a category (model uses 'label', not 'name')
        SettingOption::factory()->create([
            'category' => 'expense_categories',
            'label' => 'Office Supplies',
            'is_active' => true,
        ]);

        $this->service->loadCategoriesIndex();

        $category = $this->service->findCategoryByLabel('Office Supplies');

        $this->assertNotNull($category);
        $this->assertEquals('Office Supplies', $category->label);
    }

    /** @test */
    public function it_finds_category_by_partial_match(): void
    {
        // Create a category (model uses 'label', not 'name')
        SettingOption::factory()->create([
            'category' => 'expense_categories',
            'label' => 'Office Equipment and Supplies',
            'is_active' => true,
        ]);

        $this->service->loadCategoriesIndex();

        $category = $this->service->findCategoryByLabel('Office');

        $this->assertNotNull($category);
    }

    /** @test */
    public function it_returns_null_for_empty_category_label(): void
    {
        $this->service->loadCategoriesIndex();

        $category = $this->service->findCategoryByLabel('');

        $this->assertNull($category);
    }

    /** @test */
    public function it_associates_category_with_expense(): void
    {
        // Create a category (model uses 'label', not 'name')
        $settingOption = SettingOption::factory()->create([
            'category' => 'expense_categories',
            'label' => 'Office Supplies',
            'is_active' => true,
        ]);

        $csvData = [
            ['document_name', 'amount', 'currency', 'occurred_at', 'category'],
            ['Invoice 001', '150.50', 'RON', '2025-01-15', 'Office Supplies'],
        ];

        $this->service->import(
            $csvData,
            $this->organization->id,
            $this->user->id
        );

        $this->assertDatabaseHas('financial_expenses', [
            'document_name' => 'Invoice 001',
            'category_option_id' => $settingOption->id,
        ]);
    }
}
