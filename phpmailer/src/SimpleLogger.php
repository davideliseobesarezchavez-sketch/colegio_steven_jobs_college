<?php
namespace App;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class SimpleLogger implements LoggerInterface
{
    private $file;

    public function __construct($file = __DIR__.'/../logs/app.log') {
        $this->file = $file;
        @mkdir(dirname($this->file), 0777, true);
    }

    public function emergency($message, array $context = []) { $this->log(LogLevel::EMERGENCY, $message, $context); }
    public function alert($message, array $context = [])     { $this->log(LogLevel::ALERT, $message, $context); }
    public function critical($message, array $context = [])  { $this->log(LogLevel::CRITICAL, $message, $context); }
    public function error($message, array $context = [])     { $this->log(LogLevel::ERROR, $message, $context); }
    public function warning($message, array $context = [])   { $this->log(LogLevel::WARNING, $message, $context); }
    public function notice($message, array $context = [])    { $this->log(LogLevel::NOTICE, $message, $context); }
    public function info($message, array $context = [])      { $this->log(LogLevel::INFO, $message, $context); }
    public function debug($message, array $context = [])     { $this->log(LogLevel::DEBUG, $message, $context); }

    public function log($level, $message, array $context = []) {
        $time = date('Y-m-d H:i:s');
        $entry = "[{$time}] [{$level}] " . $this->interpolate($message, $context) . PHP_EOL;
        file_put_contents($this->file, $entry, FILE_APPEND | LOCK_EX);
    }

    private function interpolate($message, array $context = []) {
        if (!is_string($message)) {
            if (is_object($message) && method_exists($message, '__toString')) {
                $message = (string) $message;
            } else {
                $message = json_encode($message);
            }
        }
        $replace = [];
        foreach ($context as $key => $val) {
            $replace['{'.$key.'}'] = is_scalar($val) ? $val : json_encode($val);
        }
        return strtr($message, $replace);
    }
}
