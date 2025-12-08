<?php

namespace Tests\Unit\Services\Financial\Import;

use Tests\TestCase;
use App\Services\Financial\Import\ImportValidator;

class ImportValidatorTest extends TestCase
{
    protected ImportValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new ImportValidator();
    }

    /** @test */
    public function it_validates_correct_revenue_data()
    {
        $data = [
            'document_name' => 'FAC-123',
            'amount' => 1000.50,
            'currency' => 'RON',
            'occurred_at' => '2023-12-15',
        ];

        [$isValid, $errors] = $this->validator->validate($data);

        $this->assertTrue($isValid);
        $this->assertEmpty($errors);
    }

    /** @test */
    public function it_fails_when_document_name_is_missing()
    {
        $data = [
            'amount' => 1000,
            'currency' => 'RON',
            'occurred_at' => '2023-12-15',
        ];

        [$isValid, $errors] = $this->validator->validate($data);

        $this->assertFalse($isValid);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('document name', strtolower(implode(' ', $errors)));
    }

    /** @test */
    public function it_fails_when_amount_is_missing()
    {
        $data = [
            'document_name' => 'FAC-123',
            'currency' => 'RON',
            'occurred_at' => '2023-12-15',
        ];

        [$isValid, $errors] = $this->validator->validate($data);

        $this->assertFalse($isValid);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('amount', strtolower(implode(' ', $errors)));
    }

    /** @test */
    public function it_fails_when_amount_is_negative()
    {
        $data = [
            'document_name' => 'FAC-123',
            'amount' => -100,
            'currency' => 'RON',
            'occurred_at' => '2023-12-15',
        ];

        [$isValid, $errors] = $this->validator->validate($data);

        $this->assertFalse($isValid);
        $this->assertNotEmpty($errors);
    }

    /** @test */
    public function it_accepts_zero_amount()
    {
        $data = [
            'document_name' => 'FAC-123',
            'amount' => 0,
            'currency' => 'RON',
            'occurred_at' => '2023-12-15',
        ];

        [$isValid, $errors] = $this->validator->validate($data);

        $this->assertTrue($isValid);
        $this->assertEmpty($errors);
    }

    /** @test */
    public function it_fails_when_amount_is_not_numeric()
    {
        $data = [
            'document_name' => 'FAC-123',
            'amount' => 'not-a-number',
            'currency' => 'RON',
            'occurred_at' => '2023-12-15',
        ];

        [$isValid, $errors] = $this->validator->validate($data);

        $this->assertFalse($isValid);
        $this->assertNotEmpty($errors);
    }

    /** @test */
    public function it_fails_when_currency_is_missing()
    {
        $data = [
            'document_name' => 'FAC-123',
            'amount' => 1000,
            'occurred_at' => '2023-12-15',
        ];

        [$isValid, $errors] = $this->validator->validate($data);

        $this->assertFalse($isValid);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('currency', strtolower(implode(' ', $errors)));
    }

    /** @test */
    public function it_accepts_ron_currency()
    {
        $data = [
            'document_name' => 'FAC-123',
            'amount' => 1000,
            'currency' => 'RON',
            'occurred_at' => '2023-12-15',
        ];

        [$isValid, $errors] = $this->validator->validate($data);

        $this->assertTrue($isValid);
    }

    /** @test */
    public function it_accepts_eur_currency()
    {
        $data = [
            'document_name' => 'FAC-123',
            'amount' => 1000,
            'currency' => 'EUR',
            'occurred_at' => '2023-12-15',
        ];

        [$isValid, $errors] = $this->validator->validate($data);

        $this->assertTrue($isValid);
    }

    /** @test */
    public function it_fails_when_currency_is_invalid()
    {
        $data = [
            'document_name' => 'FAC-123',
            'amount' => 1000,
            'currency' => 'USD',
            'occurred_at' => '2023-12-15',
        ];

        [$isValid, $errors] = $this->validator->validate($data);

        $this->assertFalse($isValid);
        $this->assertNotEmpty($errors);
    }

    /** @test */
    public function it_fails_when_occurred_at_is_missing()
    {
        $data = [
            'document_name' => 'FAC-123',
            'amount' => 1000,
            'currency' => 'RON',
        ];

        [$isValid, $errors] = $this->validator->validate($data);

        $this->assertFalse($isValid);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('occurred at', strtolower(implode(' ', $errors)));
    }

    /** @test */
    public function it_fails_when_occurred_at_is_invalid_date()
    {
        $data = [
            'document_name' => 'FAC-123',
            'amount' => 1000,
            'currency' => 'RON',
            'occurred_at' => 'not-a-date',
        ];

        [$isValid, $errors] = $this->validator->validate($data);

        $this->assertFalse($isValid);
        $this->assertNotEmpty($errors);
    }

    /** @test */
    public function it_accepts_various_date_formats()
    {
        $dateFormats = [
            '2023-12-15',
            '2023/12/15',
            '15-12-2023',
            '15/12/2023',
        ];

        foreach ($dateFormats as $date) {
            $data = [
                'document_name' => 'FAC-123',
                'amount' => 1000,
                'currency' => 'RON',
                'occurred_at' => $date,
            ];

            [$isValid, $errors] = $this->validator->validate($data);

            $this->assertTrue($isValid, "Failed to validate date format: {$date}");
        }
    }

    /** @test */
    public function it_fails_when_document_name_exceeds_max_length()
    {
        $data = [
            'document_name' => str_repeat('A', 256), // Exceeds 255 chars
            'amount' => 1000,
            'currency' => 'RON',
            'occurred_at' => '2023-12-15',
        ];

        [$isValid, $errors] = $this->validator->validate($data);

        $this->assertFalse($isValid);
        $this->assertNotEmpty($errors);
    }

    /** @test */
    public function it_returns_multiple_errors_when_multiple_fields_invalid()
    {
        $data = [
            'amount' => -100, // Invalid (negative)
            'currency' => 'USD', // Invalid (not in list)
            // Missing document_name and occurred_at
        ];

        [$isValid, $errors] = $this->validator->validate($data);

        $this->assertFalse($isValid);
        $this->assertCount(4, $errors); // Should have 4 errors
    }
}
