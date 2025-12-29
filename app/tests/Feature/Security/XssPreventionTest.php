<?php

namespace Tests\Feature\Security;

use App\Models\User;
use App\Models\Organization;
use App\Models\Offer;
use App\Models\Contract;
use App\Models\Client;
use App\Services\HtmlSanitizerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XssPreventionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Organization $organization;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        // Create organization
        $this->organization = Organization::create([
            'name' => 'Test Organization',
            'slug' => 'test-org',
        ]);

        // Create user
        $this->user = User::factory()->create([
            'organization_id' => $this->organization->id,
            'is_admin' => true,
        ]);

        // Create client
        $this->client = Client::create([
            'name' => 'Test Client',
            'organization_id' => $this->organization->id,
        ]);
    }

    /** @test */
    public function it_sanitizes_script_tags_in_offer_introduction()
    {
        $maliciousContent = '<p>Normal content</p><script>alert("XSS")</script><p>More content</p>';

        $offer = Offer::create([
            'client_id' => $this->client->id,
            'organization_id' => $this->organization->id,
            'title' => 'Test Offer',
            'introduction' => $maliciousContent,
            'status' => 'draft',
        ]);

        $this->assertStringNotContainsString('<script>', $offer->fresh()->introduction);
        $this->assertStringNotContainsString('alert(', $offer->fresh()->introduction);
        $this->assertStringContainsString('<p>Normal content</p>', $offer->fresh()->introduction);
    }

    /** @test */
    public function it_sanitizes_inline_javascript_in_offer_terms()
    {
        $maliciousContent = '<a href="javascript:alert(\'XSS\')">Click me</a>';

        $offer = Offer::create([
            'client_id' => $this->client->id,
            'organization_id' => $this->organization->id,
            'title' => 'Test Offer',
            'terms' => $maliciousContent,
            'status' => 'draft',
        ]);

        $this->assertStringNotContainsString('javascript:', $offer->fresh()->terms);
    }

    /** @test */
    public function it_sanitizes_event_handlers_in_offer_notes()
    {
        $maliciousContent = '<div onclick="alert(\'XSS\')">Click me</div>';

        $offer = Offer::create([
            'client_id' => $this->client->id,
            'organization_id' => $this->organization->id,
            'title' => 'Test Offer',
            'notes' => $maliciousContent,
            'status' => 'draft',
        ]);

        $this->assertStringNotContainsString('onclick=', $offer->fresh()->notes);
        $this->assertStringNotContainsString('alert(', $offer->fresh()->notes);
    }

    /** @test */
    public function it_sanitizes_iframe_tags_in_offer_blocks()
    {
        $maliciousBlocks = [
            [
                'type' => 'text',
                'data' => [
                    'content' => '<iframe src="https://evil.com"></iframe><p>Normal content</p>',
                ],
            ],
        ];

        $offer = Offer::create([
            'client_id' => $this->client->id,
            'organization_id' => $this->organization->id,
            'title' => 'Test Offer',
            'blocks' => $maliciousBlocks,
            'status' => 'draft',
        ]);

        $freshBlocks = $offer->fresh()->blocks;
        $this->assertStringNotContainsString('<iframe', $freshBlocks[0]['data']['content']);
        $this->assertStringContainsString('<p>Normal content</p>', $freshBlocks[0]['data']['content']);
    }

    /** @test */
    public function it_preserves_safe_html_formatting_in_offers()
    {
        $safeContent = '<h1>Title</h1><p>Paragraph with <strong>bold</strong> and <em>italic</em> text.</p><ul><li>Item 1</li><li>Item 2</li></ul>';

        $offer = Offer::create([
            'client_id' => $this->client->id,
            'organization_id' => $this->organization->id,
            'title' => 'Test Offer',
            'introduction' => $safeContent,
            'status' => 'draft',
        ]);

        $savedContent = $offer->fresh()->introduction;
        $this->assertStringContainsString('<h1>Title</h1>', $savedContent);
        $this->assertStringContainsString('<strong>bold</strong>', $savedContent);
        $this->assertStringContainsString('<em>italic</em>', $savedContent);
        $this->assertStringContainsString('<ul>', $savedContent);
    }

    /** @test */
    public function it_sanitizes_script_tags_in_contract_content()
    {
        $maliciousContent = '<p>Contract text</p><script>document.cookie="stolen"</script>';

        $contract = Contract::create([
            'client_id' => $this->client->id,
            'organization_id' => $this->organization->id,
            'title' => 'Test Contract',
            'content' => $maliciousContent,
            'contract_number' => 'TEST-001',
            'status' => 'draft',
        ]);

        $this->assertStringNotContainsString('<script>', $contract->fresh()->content);
        $this->assertStringNotContainsString('document.cookie', $contract->fresh()->content);
    }

    /** @test */
    public function it_sanitizes_dangerous_attributes_in_contract_blocks()
    {
        $maliciousBlocks = [
            [
                'type' => 'text',
                'data' => [
                    'content' => '<img src="x" onerror="alert(\'XSS\')">',
                ],
            ],
        ];

        $contract = Contract::create([
            'client_id' => $this->client->id,
            'organization_id' => $this->organization->id,
            'title' => 'Test Contract',
            'contract_number' => 'TEST-002',
            'blocks' => $maliciousBlocks,
            'status' => 'draft',
        ]);

        $freshBlocks = $contract->fresh()->blocks;
        $this->assertStringNotContainsString('onerror=', $freshBlocks[0]['data']['content']);
    }

    /** @test */
    public function sanitizer_service_detects_dangerous_content()
    {
        $sanitizer = app(HtmlSanitizerService::class);

        $dangerousPatterns = [
            '<script>alert("XSS")</script>',
            '<img src=x onerror="alert(\'XSS\')">',
            '<a href="javascript:alert(\'XSS\')">Click</a>',
            '<iframe src="evil.com"></iframe>',
            '<div onclick="malicious()">Click</div>',
        ];

        foreach ($dangerousPatterns as $pattern) {
            $this->assertTrue(
                $sanitizer->containsDangerousContent($pattern),
                "Failed to detect dangerous content: {$pattern}"
            );
        }
    }

    /** @test */
    public function sanitizer_service_does_not_flag_safe_content()
    {
        $sanitizer = app(HtmlSanitizerService::class);

        $safePatterns = [
            '<p>Normal paragraph</p>',
            '<h1>Title</h1><p>Content</p>',
            '<strong>Bold</strong> and <em>italic</em>',
            '<a href="https://example.com">Link</a>',
            '<ul><li>Item 1</li><li>Item 2</li></ul>',
        ];

        foreach ($safePatterns as $pattern) {
            $this->assertFalse(
                $sanitizer->containsDangerousContent($pattern),
                "Incorrectly flagged safe content: {$pattern}"
            );
        }
    }

    /** @test */
    public function public_view_sanitization_is_more_restrictive()
    {
        $sanitizer = app(HtmlSanitizerService::class);

        $contentWithLinks = '<p>Text with <a href="https://example.com">link</a></p>';

        // Regular sanitize preserves links
        $regularSanitized = $sanitizer->sanitize($contentWithLinks);
        $this->assertStringContainsString('<a href', $regularSanitized);

        // Public sanitize removes links
        $publicSanitized = $sanitizer->sanitizeForPublic($contentWithLinks);
        $this->assertStringNotContainsString('<a href', $publicSanitized);
        $this->assertStringContainsString('link', $publicSanitized); // Text preserved
    }

    /** @test */
    public function strip_all_tags_removes_all_html()
    {
        $sanitizer = app(HtmlSanitizerService::class);

        $htmlContent = '<h1>Title</h1><p>Paragraph with <strong>bold</strong> text.</p>';
        $stripped = $sanitizer->stripAllTags($htmlContent);

        $this->assertEquals('Title Paragraph with bold text.', $stripped);
        $this->assertStringNotContainsString('<', $stripped);
        $this->assertStringNotContainsString('>', $stripped);
    }
}
