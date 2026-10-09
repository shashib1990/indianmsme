<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\BulkImportController;
use Tests\TestCase;

class Utf8ImportSanitizationTest extends TestCase
{
    public function test_clean_utf8_sanitizes_invalid_byte_sequences()
    {
        $controller = new BulkImportController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('cleanUtf8');
        $method->setAccessible(true);

        // Windows-1252 byte string containing smart quotes and accented characters
        $invalidUtf8String = "\x80\x93\x94 test \xE9";
        $cleaned = $method->invoke($controller, $invalidUtf8String);

        $this->assertTrue(mb_check_encoding($cleaned, 'UTF-8'));

        // Nested array test
        $nestedData = [
            'key1' => $invalidUtf8String,
            'key2' => ['nested' => "\xFF\xFE invalid"]
        ];

        $cleanedArray = $method->invoke($controller, $nestedData);
        $this->assertTrue(mb_check_encoding($cleanedArray['key1'], 'UTF-8'));
        $this->assertTrue(mb_check_encoding($cleanedArray['key2']['nested'], 'UTF-8'));

        // Ensure json_encode succeeds without Malformed UTF-8 exception
        $json = json_encode($cleanedArray);
        $this->assertNotFalse($json);
        $this->assertEquals(JSON_ERROR_NONE, json_last_error());
    }
}
