<?php
/**
 * Created by IntelliJ IDEA.
 * User: szczad
 * Date: 24.10.18
 * Time: 09:40
 */

namespace szczad\parser;


use szczad\exceptions\InvalidPeriodValueException;
use szczad\exceptions\UnknownNamedPeriodException;
use szczad\job\JobBuilder;
use szczad\schedule\CrontabPeriodicSchedule;
use szczad\schedule\Schedule;
use szczad\schedule\Scheduler;
use szczad\util\Log;

class CrontabParser {
    const DAY_OF_WEEK = [
        'SUN' => 0,
        'MON' => 1,
        'TUE' => 2,
        'WED' => 3,
        'THU' => 4,
        'FRI' => 5,
        'SAT' => 6
    ];

    const MONTH = [
        'JAN' => 1,
        'FEB' => 2,
        'MAR' => 3,
        'APR' => 4,
        'MAY' => 5,
        'JUN' => 6,
        'JUL' => 7,
        'AUG' => 8,
        'SEP' => 9,
        'OCT' => 10,
        'NOV' => 11,
        'DEC' => 12
    ];

    const NAMED = [
        '@yearly'   => '0 0 0 1 1 *',
        '@annually' => '0 0 0 1 1 *',
        '@monthly'  => '0 0 0 1 * *',
        '@weekly'   => '0 0 0 * * 0',
        '@daily'    => '0 0 0 * * *',
        '@hourly'   => '0 0 * * * *',
        '@reboot'   => 'self::generatorNamedReboot'
    ];

    private $job_builder;
    private $crontab_class;
    private $logger;

    /**
     * CrontabParser constructor.
     * @param JobBuilder $job_builder
     * @param string $crontab_schedule_implementation
     */
    public function __construct($job_builder, $crontab_schedule_implementation = CrontabPeriodicSchedule::class) {
        $this->job_builder = $job_builder;
        $this->crontab_class = $crontab_schedule_implementation;
        $this->logger = Log::getInstance();
    }

    /**
     * @param string $filename
     * @return Scheduler
     */
    public function getSchedulerFromFile($filename) {
        $schedules = [];
        $fd = fopen($filename, 'r');
        try {
            while (($content = fgets($fd)) !== false) {
                $schedule = $this->getSchedule($content);
                if ($schedule !== null)
                    $schedules[] = $schedule;
            }
        } finally {
            fclose($fd);
        }

        $scheduler = new Scheduler($schedules);
        return $scheduler;
    }

    /**
     * @param string $line Line containing crontab job definition
     * @return Schedule|null
     */
    public function getSchedule($line) {
        $this->logger->debug("Processing {line}", ['line' => $line]);

        try {
            $num_args = $this->fixLine($line);
            $values = $this->lineToConstructorArgs($line, $num_args);
        } catch (UnknownNamedPeriodException $e) {
            $this->logger->debug("Unknown named period in line: {line}", ['line' => $line]);
            return null;
        } catch (InvalidPeriodValueException $e) {
            $this->logger->debug("Invalid period definition in line: {line}", ['line' => $line]);
            return null;
        }

        return new Schedule(new $this->crontab_class(...$values));
    }

    /**
     * @param $line
     * @return int
     * @throws UnknownNamedPeriodException
     */
    public function fixLine(&$line) {
        $starts_with = substr($line, 0, 1);
        switch ($starts_with) {
            case "@":
                $this->namedToPeriod($line);
                $num_args = 6;
                break;
            case "+":
                $line = substr($line, 1);
                $num_args = 6;
                break;
            default:
                $num_args = 5;
        }

        return $num_args;
    }

    /**
     * @param $line
     * @param $num_args
     * @return array
     * @throws InvalidPeriodValueException
     */
    public function lineToConstructorArgs($line, $num_args) {
        $values = array_reverse(explode(" ", $line, $num_args+1));
        $values[1] = $this->parseField($values[1], 0, 7, self::DAY_OF_WEEK); // days of week
        $values[2] = $this->parseField($values[2], 1, 12, self::MONTH);      // months
        $values[3] = $this->parseField($values[3], 1, 31);                           // days
        $values[4] = $this->parseField($values[4], 0, 23);                           // hours
        $values[5] = $this->parseField($values[5], 0, 59);                           // minutes
        $values[6] = ($num_args === 6) ? $this->parseField($values[6], 0, 59) : [0]; // seconds
        return $values;
    }

    /**
     * @param $line
     * @throws UnknownNamedPeriodException
     */
    public function namedToPeriod(&$line) {
        $parts = explode(" ", $line, 2);
        if (!array_key_exists($parts[0], self::NAMED))
            throw new UnknownNamedPeriodException("Unknown named period: " . $parts[0]);

        $value = self::NAMED[$parts[0]];
        if (substr($value, 0, 4) == 'self') {
            call_user_func_array($value, [&$value]);
        }

        $parts[0] = $value;
        $line = join(" ", $parts);
    }

    public static function generatorNamedReboot(&$value) {
        $value = date('s i G j n *', time() + 1);
    }

    /**
     * @param string $value Field to parse
     * @param int $range_start Start value of field range (i.e.: 0 for hours, 1 for months)
     * @param int $range_end End value of field range (i.e: 23 for minutes, 31 for months)
     * @param array $mapping mapping Mapping of values if required (i.e.: months JAN => 1)
     * @return array
     * @throws InvalidPeriodValueException
     */
    public function parseField($value, $range_start, $range_end, $mapping = []) {
        $result = array();

        $parts = $this->getParts($value);
        foreach($parts as $part) {
            $this->checkPart($part);

            $step = 1;
            $left = $range_start;
            $right = $range_end;

            $this->getStep($part, $step);
            $this->getRange($part, $left, $right, $mapping);
            $this->populateResults($result, $left, $right, $step);
        }

        return $this->cleanupRange($result);
    }

    public function getParts($value) {
        return explode(",", $value);
    }

    /**
     * @param $part
     * @throws InvalidPeriodValueException
     */
    public function checkPart($part) {
        if ($part === "")
            throw new InvalidPeriodValueException();
    }

    /**
     * @param $part
     * @param $step
     * @throws InvalidPeriodValueException
     */
    public function getStep(&$part, &$step) {
        if (strpos($part, '/') !== false) {
            $tokens = explode('/', $part);
            if ((count($tokens) !== 2) || empty($tokens[1]) || (intval($tokens[1]) <= 0))
                throw new InvalidPeriodValueException("Invalid token: " . $part);
            list($part, $step) = $tokens;
        } else {
            $step = 1;
        }
    }

    /**
     * @param string $part
     * @param string $left
     * @param string $right
     * @param array $mapping
     * @throws InvalidPeriodValueException
     */
    public function getRange($part, &$left, &$right, $mapping = []) {
        if (strpos($part, '-') !== false) {
            $tokens = explode("-", $part);
            if ((count($tokens) !== 2) || empty($tokens[0]) || empty($tokens[1]))
                throw new InvalidPeriodValueException();

            $left = $this->getMappedInteger($tokens[0], $mapping);
            $right = $this->getMappedInteger($tokens[1], $mapping);
        } elseif ($part !== "*") {
            $left = $right = $this->getMappedInteger($part, $mapping);
        }
    }

    public function populateResults(&$result, $left, $right, $step) {
        if ($step <= 0)
            throw new \InvalidArgumentException("Step must be greater than 0");

        $result = [];
        for ($i = $left; $i <= $right; $i++) {
            if ($i % $step == 0)
                array_push($result, $i);
        }
    }

    public function cleanupRange($array) {
        sort($array);
        return array_unique($array);
    }

    /**
     * @param $value
     * @param $mapping
     * @return int
     * @throws InvalidPeriodValueException
     */
    public function getMappedInteger($value, $mapping) {
        if (array_key_exists($value, $mapping))
            $value = intval($mapping[$value]);

        if ((string)(int)$value != $value)
            throw new InvalidPeriodValueException();

        return $value;
    }

}