<?php
/**
 * Created by IntelliJ IDEA.
 * User: szczad
 * Date: 30.10.18
 * Time: 09:56
 */

namespace szczad\util;

use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Log implements LoggerInterface {
    /**
     * @var LoggerInterface
     */
    private static $_instance = null;
    private $impl;

    /**
     * Log constructor.
     * @param LoggerInterface $impl
     */
    private function __construct($impl) {
        if ($impl === null)
            throw new InvalidArgumentException("Logger implementation cannot be null!");
        $this->impl = $impl;
    }

    /**
     * @param LoggerInterface $log_impl
     * @return Log
     */
    public static function getInstance($log_impl = null) {
        if ($log_impl === null)
            $log_impl = new NullLogger();

        if (self::$_instance === null) {
            self::$_instance = new self($log_impl);
        } else if ($log_impl !== null)
            self::$_instance->warning("Logger already defined");

        return self::$_instance;
    }


    public function emergency($message, array $context = array()) {
        $this->impl->emergency($message, $context);
    }

    public function alert($message, array $context = array()) {
        $this->impl->alert($message, $context);
    }

    public function critical($message, array $context = array()) {
        $this->impl->critical($message, $context);
    }

    public function error($message, array $context = array()) {
        $this->impl->error($message, $context);
    }

    public function warning($message, array $context = array()) {
        $this->impl->warning($message, $context);
    }

    public function notice($message, array $context = array()) {
        $this->impl->notice($message, $context);
    }

    public function info($message, array $context = array()) {
        $this->impl->info($message, $context);
    }

    public function debug($message, array $context = array()) {
        $this->impl->debug($message, $context);
    }

    public function log($level, $message, array $context = array()) {
        $this->impl->log($level, $message, $context);
    }
}