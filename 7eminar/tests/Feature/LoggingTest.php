<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tests\TestCase;

class LoggingTest extends TestCase
{
    public function test_logging_writes_to_correct_files()
    {
        $debugLogPath = storage_path('logs/debug.log');
        $errorLogPath = storage_path('logs/error.log');

        $debugMessage = 'DEBUG_LOG_TEST_' . Str::uuid();
        $errorMessage = 'ERROR_LOG_TEST_' . Str::uuid();

        Log::debug($debugMessage);
        Log::error($errorMessage);

        sleep(1);

        $debugLogContent = file_get_contents($debugLogPath);
        $errorLogContent = file_get_contents($errorLogPath);

        $this->assertStringContainsString($debugMessage, $debugLogContent, 'Debug message not found in debug.log');
        $this->assertStringContainsString($errorMessage, $debugLogContent, 'Error message not found in debug.log');
        $this->assertStringContainsString($errorMessage, $errorLogContent, 'Error message not found in error.log');
        $this->assertStringNotContainsString($debugMessage, $errorLogContent, 'Debug message should not be in error.log');
    }
}
