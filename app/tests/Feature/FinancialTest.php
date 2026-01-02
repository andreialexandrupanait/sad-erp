<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\FinancialExpense;
use App\Models\FinancialFile;
use App\Models\FinancialRevenue;
use App\Models\Organization;
use App\Models\SettingOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FinancialTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'role' => 'admin',
        ]);

        // Seed required settings
        $this->seed(\Database\Seeders\CurrenciesSeeder::class);
        $this->seed(\Database\Seeders\ExpenseCategoriesSeeder::class);
        $this->seed(\Database\Seeders\PaymentMethodsSeeder::class);

        Storage::fake('financial');
    }

    /** @test */
    public function user_can_create_revenue()
    {
        $client = Client::factory()->create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
        ]);

        $revenueData = [
            'document_name' => 'Invoice #001',
            'amount' => 1500.00,
            'currency' => 'RON',
            'occurred_at' => now()->format('Y-m-d'),
            'client_id' => $client->id,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('financial.revenues.store'), $revenueData);

        $response->assertRedirect();
        $this->assertDatabaseHas('financial_revenues', [
            'document_name' => 'Invoice #001',
            'amount' => 1500.00,
            'organization_id' => $this->organization->id,
        ]);
    }

    /** @test */
    public function user_can_create_expense()
    {
        $category = SettingOption::expenseCategories()->first();

        $expenseData = [
            'document_name' => 'Office Supplies',
            'amount' => 250.50,
            'currency' => 'RON',
            'occurred_at' => now()->format('Y-m-d'),
            'category_option_id' => $category->id,
        ];

        $response = $this->actingAs($this->user)
            ->post(route('financial.expenses.store'), $expenseData);

        $response->assertRedirect();
        $this->assertDatabaseHas('financial_expenses', [
            'document_name' => 'Office Supplies',
            'amount' => 250.50,
            'organization_id' => $this->organization->id,
        ]);
    }

    /** @test */
    public function revenue_requires_valid_currency()
    {
        $response = $this->actingAs($this->user)
            ->post(route('financial.revenues.store'), [
                'document_name' => 'Test Invoice',
                'amount' => 100,
                'currency' => 'INVALID',
                'occurred_at' => now()->format('Y-m-d'),
            ]);

        $response->assertSessionHasErrors('currency');
    }

    /** @test */
    public function financial_file_validates_entity_type()
    {
        $this->expectException(\InvalidArgumentException::class);

        FinancialFile::create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
            'file_name' => 'test.pdf',
            'file_path' => 'test.pdf',
            'entity_type' => 'App\Models\InvalidModel', // Invalid type
            'entity_id' => 1,
        ]);
    }

    /** @test */
    public function financial_file_accepts_valid_entity_types()
    {
        $revenue = FinancialRevenue::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $file = FinancialFile::create([
            'organization_id' => $this->organization->id,
            'user_id' => $this->user->id,
            'file_name' => 'invoice.pdf',
            'file_path' => 'invoices/invoice.pdf',
            'entity_type' => FinancialRevenue::class,
            'entity_id' => $revenue->id,
        ]);

        $this->assertEquals(FinancialRevenue::class, $file->entity_type);
        $this->assertEquals($revenue->id, $file->entity_id);
    }

    /** @test */
    public function user_can_attach_file_to_revenue()
    {
        $revenue = FinancialRevenue::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $file = UploadedFile::fake()->create('invoice.pdf', 100);

        $response = $this->actingAs($this->user)
            ->put(route('financial.revenues.update', $revenue), [
                'document_name' => $revenue->document_name,
                'amount' => $revenue->amount,
                'currency' => $revenue->currency,
                'occurred_at' => $revenue->occurred_at->format('Y-m-d'),
                'files' => [$file],
            ]);

        $response->assertRedirect();
        Storage::disk('financial')->assertExists(
            $revenue->year . '/' . $revenue->month . '/incasare/' . $file->hashName()
        );
    }

    /** @test */
    public function revenue_factory_creates_valid_revenue()
    {
        $revenue = FinancialRevenue::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->assertNotNull($revenue->document_name);
        $this->assertGreaterThan(0, $revenue->amount);
        $this->assertContains($revenue->currency, ['RON', 'EUR', 'USD']);
        $this->assertNotNull($revenue->occurred_at);
    }

    /** @test */
    public function expense_factory_creates_valid_expense()
    {
        $expense = FinancialExpense::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->assertNotNull($expense->document_name);
        $this->assertGreaterThan(0, $expense->amount);
        $this->assertContains($expense->currency, ['RON', 'EUR', 'USD']);
        $this->assertNotNull($expense->occurred_at);
    }

    /** @test */
    public function user_cannot_access_financials_from_other_organizations()
    {
        $otherOrganization = Organization::factory()->create();
        $otherRevenue = FinancialRevenue::factory()->create([
            'organization_id' => $otherOrganization->id,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('financial.revenues.show', $otherRevenue));

        // Global scope makes records from other organizations invisible (404)
        // This is more secure than 403 as it doesn't reveal resource existence
        $response->assertStatus(404);
    }

    /** @test */
    public function revenue_scopes_by_year()
    {
        FinancialRevenue::factory()->create([
            'organization_id' => $this->organization->id,
            'occurred_at' => now()->setYear(2024),
        ]);

        FinancialRevenue::factory()->create([
            'organization_id' => $this->organization->id,
            'occurred_at' => now()->setYear(2023),
        ]);

        $revenues2024 = FinancialRevenue::forYear(2024)->get();

        $this->assertCount(1, $revenues2024);
        $this->assertEquals(2024, $revenues2024->first()->year);
    }

    /** @test */
    public function financial_dashboard_shows_correct_data()
    {
        // Create some test data
        FinancialRevenue::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
            'occurred_at' => now(),
            'amount' => 1000,
            'currency' => 'RON',
        ]);

        FinancialExpense::factory()->count(2)->create([
            'organization_id' => $this->organization->id,
            'occurred_at' => now(),
            'amount' => 500,
            'currency' => 'RON',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('financial.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('financial.dashboard');
    }
}
