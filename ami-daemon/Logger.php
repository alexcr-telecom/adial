<?php
/**
 * Simple Logger class
 */
class Logger {
    private $logFile;
    private $level;
    private $levels = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3];

    public function __construct($logFile, $level = 'info') {
        $this->logFile = $logFile;
        $this->level = $level;

        // Create log directory if it doesn't exist
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    public function getLevel() {
        return $this->level;
    }

    private function shouldLog($level) {
        return $this->levels[$level] >= $this->levels[$this->level];
    }

    private function write($level, $message, $context = []) {
        if (!$this->shouldLog($level)) {
            return;
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logMessage = "[{$timestamp}] [{$level}] {$message}{$contextStr}\n";

        file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    public function debug($message, $context = []) {
        $this->write('debug', $message, $context);
    }

    public function info($message, $context = []) {
        $this->write('info', $message, $context);
    }

    public function warning($message, $context = []) {
        $this->write('warning', $message, $context);
    }

    public function error($message, $context = []) {
        $this->write('error', $message, $context);
    }
}
