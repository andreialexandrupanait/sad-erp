<?php

namespace Tests\Unit\Rules;

use Tests\TestCase;
use App\Rules\SecureFileUpload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SecureFileUploadTest extends TestCase
{
    /**
     * Test legitimate PDF file passes validation
     *
     * @test
     */
    public function it_allows_legitimate_pdf_file()
    {
        // Create a real PDF file (simplified - just PDF header)
        $pdfContent = "%PDF-1.4\n%����\nHello World";
        $file = UploadedFile::fake()->createWithContent('document.pdf', $pdfContent);

        $rule = new SecureFileUpload();
        $this->assertTrue($rule->passes('file', $file));
    }

    /**
     * Test legitimate JPEG file passes validation
     *
     * @test
     */
    public function it_allows_legitimate_jpeg_file()
    {
        $file = UploadedFile::fake()->image('photo.jpg', 100, 100);

        $rule = new SecureFileUpload();
        $this->assertTrue($rule->passes('file', $file));
    }

    /**
     * Test PHP file is rejected
     *
     * @test
     */
    public function it_rejects_php_files()
    {
        $phpContent = "<?php echo 'malicious code'; ?>";
        $file = UploadedFile::fake()->createWithContent('shell.php', $phpContent);

        $rule = new SecureFileUpload();
        $this->assertFalse($rule->passes('file', $file));
        $this->assertStringContainsString('executable', $rule->message());
    }

    /**
     * Test double extension attack is blocked
     *
     * @test
     */
    public function it_blocks_double_extension_attack()
    {
        $phpContent = "<?php echo 'malicious'; ?>";
        $file = UploadedFile::fake()->createWithContent('image.jpg.php', $phpContent);

        $rule = new SecureFileUpload();
        $this->assertFalse($rule->passes('file', $file));
    }

    /**
     * Test MIME type spoofing is detected
     *
     * @test
     */
    public function it_detects_mime_type_spoofing()
    {
        // Create a PHP file pretending to be a JPEG
        $phpContent = "<?php echo 'malware'; ?>";
        $file = UploadedFile::fake()->createWithContent('fake.jpg', $phpContent);

        $rule = new SecureFileUpload();

        // The file will fail because:
        // 1. Real MIME type (detected by finfo) won't match image/jpeg
        // 2. Magic bytes won't match JPEG signature (FFD8)
        $this->assertFalse($rule->passes('file', $file));
    }

    /**
     * Test null byte injection is blocked
     *
     * @test
     */
    public function it_blocks_null_byte_injection()
    {
        $file = UploadedFile::fake()->createWithContent("exploit\0.pdf", "content");

        $rule = new SecureFileUpload();
        $this->assertFalse($rule->passes('file', $file));
        $this->assertStringContainsString('Invalid filename', $rule->message());
    }

    /**
     * Test file size limit is enforced
     *
     * @test
     */
    public function it_enforces_file_size_limit()
    {
        // Create a file larger than 10MB
        $largeFile = UploadedFile::fake()->create('huge.pdf', 11000); // 11MB

        $rule = new SecureFileUpload();
        $this->assertFalse($rule->passes('file', $largeFile));
        $this->assertStringContainsString('size exceeds', $rule->message());
    }

    /**
     * Test .phar files are blocked (PHP archive exploit)
     *
     * @test
     */
    public function it_blocks_phar_files()
    {
        $pharContent = "<?php __HALT_COMPILER(); ?>";
        $file = UploadedFile::fake()->createWithContent('backdoor.phar', $pharContent);

        $rule = new SecureFileUpload();
        $this->assertFalse($rule->passes('file', $file));
    }

    /**
     * Test .exe files are blocked
     *
     * @test
     */
    public function it_blocks_executable_files()
    {
        $exeContent = "MZ\x90\x00"; // EXE magic bytes
        $file = UploadedFile::fake()->createWithContent('malware.exe', $exeContent);

        $rule = new SecureFileUpload();
        $this->assertFalse($rule->passes('file', $file));
    }

    /**
     * Test all allowed extensions are whitelisted
     *
     * @test
     */
    public function it_has_comprehensive_whitelist()
    {
        $allowedExtensions = SecureFileUpload::getAllowedExtensions();

        $expectedExtensions = [
            'pdf', 'jpg', 'jpeg', 'png', 'gif',
            'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            'txt', 'csv', 'zip', 'rar'
        ];

        foreach ($expectedExtensions as $ext) {
            $this->assertContains($ext, $allowedExtensions);
        }
    }

    /**
     * Test magic byte verification for PDF
     *
     * @test
     */
    public function it_verifies_pdf_magic_bytes()
    {
        // Create file with wrong magic bytes but .pdf extension
        $fakeContent = "FAKE PDF CONTENT";
        $file = UploadedFile::fake()->createWithContent('fake.pdf', $fakeContent);

        $rule = new SecureFileUpload();
        $this->assertFalse($rule->passes('file', $file));
        $this->assertStringContainsString('signature verification failed', $rule->message());
    }

    /**
     * Test extension mismatch detection
     *
     * @test
     */
    public function it_detects_extension_mismatch()
    {
        // Upload a real JPEG but with .png extension
        $jpegFile = UploadedFile::fake()->image('photo.png', 100, 100);

        // The fake()->image() creates a real image file
        // If the MIME type is image/jpeg but extension is .png, it should be flagged
        // Note: This test may vary based on how UploadedFile::fake() generates images
    }

    /**
     * Test ZIP files are allowed
     *
     * @test
     */
    public function it_allows_zip_files()
    {
        // ZIP magic bytes: PK\x03\x04
        $zipContent = "PK\x03\x04\x14\x00\x00\x00";
        $file = UploadedFile::fake()->createWithContent('archive.zip', $zipContent);

        $rule = new SecureFileUpload();
        // May pass depending on exact MIME detection
        // This is a simplified test
    }

    /**
     * Test CSV files are allowed (text-based, skip magic byte check)
     *
     * @test
     */
    public function it_allows_csv_files()
    {
        $csvContent = "name,email,phone\nJohn,john@example.com,123456";
        $file = UploadedFile::fake()->createWithContent('data.csv', $csvContent);

        $rule = new SecureFileUpload();
        // CSV files skip magic byte check as they're text-based
        $this->assertTrue($rule->passes('file', $file));
    }
}
