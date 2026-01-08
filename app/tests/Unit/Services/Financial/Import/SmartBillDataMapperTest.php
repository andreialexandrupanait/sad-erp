<?php

namespace Tests\Unit\Services\Financial\Import;

use Tests\TestCase;
use App\Services\Financial\Import\SmartBillDataMapper;

class SmartBillDataMapperTest extends TestCase
{
    protected SmartBillDataMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new SmartBillDataMapper();
    }

    /** @test */
    public function it_detects_smartbill_export_by_serie_column()
    {
        $header = ['Serie', 'Numar', 'Total', 'Client'];

        $result = $this->mapper->isSmartBillExport($header);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_detects_smartbill_export_by_factura_column()
    {
        $header = ['Factura', 'Total', 'Client', 'Data'];

        $result = $this->mapper->isSmartBillExport($header);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_detects_smartbill_export_by_cif_column()
    {
        $header = ['CIF', 'Client', 'Total', 'Data'];

        $result = $this->mapper->isSmartBillExport($header);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_detects_smartbill_export_by_data_incasarii_column()
    {
        $header = ['Client', 'Total', 'Data incasarii'];

        $result = $this->mapper->isSmartBillExport($header);

        $this->assertTrue($result);
    }

    /** @test */
    public function it_returns_false_for_non_smartbill_format()
    {
        $header = ['document_name', 'amount', 'client_name'];

        $result = $this->mapper->isSmartBillExport($header);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_maps_smartbill_columns_to_expected_format()
    {
        $data = [
            'Serie' => 'FAC',
            'Numar' => '123',
            'Total' => '1000.50',
            'Moneda' => 'RON',
            'Client' => 'Test Company SRL',
            'CIF' => '12345678',
        ];

        $mapped = $this->mapper->mapColumns($data);

        $this->assertEquals('FAC', $mapped['serie']);
        $this->assertEquals('123', $mapped['numar']);
        $this->assertEquals('1000.50', $mapped['amount']);
        $this->assertEquals('RON', $mapped['currency']);
        $this->assertEquals('Test Company SRL', $mapped['client_name']);
        $this->assertEquals('12345678', $mapped['cif_client']);
    }

    /** @test */
    public function it_creates_document_name_from_serie_and_numar()
    {
        $data = [
            'Serie' => 'FAC',
            'Numar' => '123',
        ];

        $mapped = $this->mapper->mapColumns($data);

        $this->assertEquals('FAC-123', $mapped['document_name']);
    }

    /** @test */
    public function it_sets_default_currency_when_empty()
    {
        $data = [
            'Serie' => 'FAC',
            'Numar' => '123',
        ];

        $mapped = $this->mapper->mapColumns($data);

        $this->assertEquals('RON', $mapped['currency']);
    }

    /** @test */
    public function it_does_not_override_existing_currency()
    {
        $data = [
            'Serie' => 'FAC',
            'Numar' => '123',
            'Moneda' => 'EUR',
        ];

        $mapped = $this->mapper->mapColumns($data);

        $this->assertEquals('EUR', $mapped['currency']);
    }

    /** @test */
    public function it_converts_romanian_date_format_to_iso()
    {
        $data = [
            'Data' => '15/12/2023',
        ];

        $mapped = $this->mapper->mapColumns($data);

        $this->assertEquals('2023-12-15', $mapped['occurred_at']);
    }

    /** @test */
    public function it_handles_data_incasarii_field()
    {
        $data = [
            'Data incasarii' => '20/11/2023',
        ];

        $mapped = $this->mapper->mapColumns($data);

        $this->assertEquals('2023-11-20', $mapped['occurred_at']);
    }

    /** @test */
    public function it_maps_all_standard_smartbill_columns()
    {
        $data = [
            'Serie' => 'FAC',
            'Numar' => '123',
            'Factura' => 'FAC-123',
            'Data' => '01/01/2023',
            'Total' => '1000',
            'Moneda' => 'RON',
            'Client' => 'Company SRL',
            'CIF' => '12345678',
            'Adresa' => 'Str. Test 123',
            'Persoana contact' => 'John Doe',
            'Observatii' => 'Test note',
        ];

        $mapped = $this->mapper->mapColumns($data);

        $this->assertEquals('FAC', $mapped['serie']);
        $this->assertEquals('123', $mapped['numar']);
        $this->assertEquals('FAC-123', $mapped['document_name']);
        $this->assertEquals('2023-01-01', $mapped['occurred_at']);
        $this->assertEquals('1000', $mapped['amount']);
        $this->assertEquals('RON', $mapped['currency']);
        $this->assertEquals('Company SRL', $mapped['client_name']);
        $this->assertEquals('12345678', $mapped['cif_client']);
        $this->assertEquals('Str. Test 123', $mapped['client_address']);
        $this->assertEquals('John Doe', $mapped['client_contact']);
        $this->assertEquals('Test note', $mapped['note']);
    }

    /** @test */
    public function it_preserves_unmapped_columns()
    {
        $data = [
            'Serie' => 'FAC',
            'CustomColumn' => 'CustomValue',
        ];

        $mapped = $this->mapper->mapColumns($data);

        $this->assertEquals('FAC', $mapped['serie']);
        $this->assertEquals('CustomValue', $mapped['CustomColumn']);
    }

    /** @test */
    public function it_handles_empty_data_gracefully()
    {
        $data = [];

        $mapped = $this->mapper->mapColumns($data);

        // Should set default currency
        $this->assertEquals('RON', $mapped['currency']);
    }

    /** @test */
    public function it_handles_data_with_only_factura_column()
    {
        $data = [
            'Factura' => 'INV-456',
            'Total' => '2000',
        ];

        $mapped = $this->mapper->mapColumns($data);

        $this->assertEquals('INV-456', $mapped['document_name']);
        $this->assertEquals('2000', $mapped['amount']);
    }

    /** @test */
    public function it_does_not_create_document_name_when_already_present()
    {
        $data = [
            'Serie' => 'FAC',
            'Numar' => '123',
            'Factura' => 'CUSTOM-999',
        ];

        $mapped = $this->mapper->mapColumns($data);

        $this->assertEquals('CUSTOM-999', $mapped['document_name']);
    }
}
