<?php
/**
 * Created by IntelliJ IDEA.
 * User: szczad
 * Date: 30.10.18
 * Time: 09:56
 */

namespace szczad\util;

use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use szczad\exceptions\LogAlreadyCreated;

class Log implements LoggerInterface {
    const NULL_SOURCE = "CRON";

    /**
     * @var LoggerInterface
     */
    private static $_instance = null;
    private $impl;
    private $content = [];

    /**
     * Log constructor.
     * @param LoggerInterface $impl Implementation that handles real logging
     * @throws LogAlreadyCreated If
     */
    public function __construct($impl = null) {
        if (self::$_instance !== null)
            throw new LogAlreadyCreated();

        if ($impl === null)
            $impl = new NullLogger();

        $this->impl = $impl;
        self::$_instance = $this;
    }

    /**
     * @return Log
     */
    public static function getInstance() {
        if (self::$_instance === null) try {
            self::$_instance = new self();
        } catch (Exception $ignore) {}
        return self::$_instance;
    }

    /**
     * System is unusable.
     *
     * @param string $message Message to log
     * @param array $context Message context to fill in placeholders
     * @param string $src Source of the message
     *
     * @return void
     */
    public function emergency($message, array $context = array(), $src = self::NULL_SOURCE) {
        $this->log(LogLevel::EMERGENCY, $message, $context, $src);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message Message to log
     * @param array $context Message context to fill in placeholders
     * @param string $src Source of the message
     *
     * @return void
     */
    public function alert($message, array $context = array(), $src = self::NULL_SOURCE) {
        $this->log(LogLevel::ALERT, $message, $context, $src);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message Message to log
     * @param array $context Message context to fill in placeholders
     * @param string $src Source of the message
     *
     * @return void
     */
    public function critical($message, array $context = array(), $src = self::NULL_SOURCE) {
        $this->log(LogLevel::CRITICAL, $message, $context, $src);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message Message to log
     * @param array $context Message context to fill in placeholders
     * @param string $src Source of the message
     *
     * @return void
     */
    public function error($message, array $context = array(), $src = self::NULL_SOURCE) {
        $this->log(LogLevel::ERROR, $message, $context, $src);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message Message to log
     * @param array $context Message context to fill in placeholders
     * @param string $src Source of the message
     *
     * @return void
     */
    public function warning($message, array $context = array(), $src = self::NULL_SOURCE) {
        $this->log(LogLevel::WARNING, $message, $context, $src);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message Message to log
     * @param array $context Message context to fill in placeholders
     * @param string $src Source of the message
     *
     * @return void
     */
    public function notice($message, array $context = array(), $src = self::NULL_SOURCE) {
        $this->log(LogLevel::NOTICE, $message, $context, $src);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message Message to log
     * @param array $context Message context to fill in placeholders
     * @param string $src Source of the message
     *
     * @return void
     */
    public function info($message, array $context = array(), $src = self::NULL_SOURCE) {
        $this->log(LogLevel::INFO, $message, $context, $src);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message Message to log
     * @param array $context Message context to fill in placeholders
     * @param string $src Source of the message
     *
     * @return void
     */
    public function debug($message, array $context = array(), $src = self::NULL_SOURCE) {
        $this->log(LogLevel::DEBUG, $message, $context, $src);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param string $level Message level
     * @param string $message Message to log
     * @param array $context Message context to fill in placeholders
     * @param string $src Source of the message
     *
     * @return void
     */
    public function log($level, $message, array $context = array(), $src = self::NULL_SOURCE) {
        $this->content[] = [$src, $level, $this->interpolate($message, $context)];
    }

    // SRC: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md#12-message
    private function interpolate($message, array $context = array()) {
        $replace = array();
        foreach ($context as $key => $val) {
            // check that the value can be casted to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        return strtr($message, $replace);
    }
}