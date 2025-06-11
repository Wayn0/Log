<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use WayneOliver\Log;

class LogTest extends TestCase
{
    private string $logDir;

    protected function setUp(): void
    {
        $this->logDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'log_test';
        if (!file_exists($this->logDir)) {
            mkdir($this->logDir);
        }
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->logDir . DIRECTORY_SEPARATOR . '*.log'));
        rmdir($this->logDir);
    }

    private function getLogFile(string $prefix = 'log'): string
    {
        return $this->logDir . DIRECTORY_SEPARATOR . $prefix . '_' . date('Y-m-d') . '.log';
    }

    private function readLogContents(string $prefix = 'log'): string
    {
        return file_get_contents($this->getLogFile($prefix));
    }

    public function testLogInfoIsWritten()
    {
        $logger = new Log($this->logDir, Log::INFO, 'testlog');
        $logger->logInfo('Info test message');
        $this->assertFileExists($this->getLogFile('testlog'));
        $this->assertStringContainsString('INFO', $this->readLogContents('testlog'));
        $this->assertStringContainsString('Info test message', $this->readLogContents('testlog'));
    }

    public function testLogDebugIsSkippedWhenLevelIsInfo()
    {
        $logger = new Log($this->logDir, Log::INFO, 'skipdebug');
        $logger->logDebug('Debug test message');
        $logFile = $this->getLogFile('skipdebug');
        $this->assertFileExists($logFile);
        $this->assertStringNotContainsString('Debug test message', $this->readLogContents('skipdebug'));
    }

    public function testLogFatalAlwaysWrittenIfLevelAllows()
    {
        $logger = new Log($this->logDir, Log::FATAL, 'fatal');
        $logger->logFatal('Fatal issue');
        $this->assertStringContainsString('FATAL', $this->readLogContents('fatal'));
        $this->assertStringContainsString('Fatal issue', $this->readLogContents('fatal'));
    }

    public function testNoLogsWrittenIfLevelNone()
    {
        $logger = new Log($this->logDir, Log::NONE, 'silent');
        $logger->logFatal('Should not appear');
        $file = $this->getLogFile('silent');
        if (file_exists($file)) {
            $this->assertEmpty(trim(file_get_contents($file)));
        } else {
            $this->assertFalse(file_exists($file));
        }
    }
}
