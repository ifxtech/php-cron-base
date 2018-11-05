<?php
/**
 * Created by IntelliJ IDEA.
 * User: szczad
 * Date: 24.10.18
 * Time: 14:15
 */

namespace szczad\schedule;


use DateInterval;
use DateTime;
use Exception;
use szczad\job\JobBuilder;
use szczad\job\JobInterface;

class CrontabPeriodicSchedule implements ScheduleInterface {
    private $command;
    private $fields = [];
    private $tz;

    public function __construct(
        $command,
        $days_of_week = [0, 1, 2, 3, 4, 5, 6, 7],
        $months = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
        $days = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31],
        $hours = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23],
        $minutes = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31,
            32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59],
        $seconds = [0],
        $tz = null
    ) {
        $this->command = $command;
        $this->tz = $tz;

        $this->setField($this->fields['days_of_week'], $days_of_week);
        $this->setField($this->fields['months'], $months);
        $this->setField($this->fields['days'], $days);
        $this->setField($this->fields['hours'], $hours);
        $this->setField($this->fields['minutes'], $minutes);
        $this->setField($this->fields['seconds'], $seconds);
    }

    /**
     * @param JobBuilder $builder
     * @return JobInterface
     */
    public function getJob($builder) {
        return $builder->getJob($this, $this->command);
    }

    /**
     * @param int|null $time
     * @return DateTime
     * @throws Exception
     */
    public function getNextRun($time = null) {
        if ($time === null)
            $time = time();
        $date = getdate($time);

        $second = $this->getAfter($this->fields['seconds'], $date['seconds'] + 1, 0, 59, $add);
        $minute = $this->getAfter($this->fields['minutes'], $date['minutes'] + $add, 0, 59, $add);
        $hour = $this->getAfter($this->fields['hours'], $date['hours'] + $add, 0, 23, $add);

        $day_interval = new DateInterval('P1D');
        $dt = new DateTime();
        $dt->setDate($date['year'], $date['month'], $date['day'] + $add);
        $dt->setTime($hour, $minute, $second);

        $found = false;
        while(!$found) {
            $day = $this->getDayFromDateTime($dt);
            $month = $this->getMonthFromDateTime($dt);
            $day_of_week = $this->getDayOfWeekFromDateTime($dt);
            if (
                in_array($day, $this->fields['days']) &&
                in_array($month, $this->fields['months']) &&
                in_array($day_of_week, $this->fields['days_of_week'])
            ) {
                $found = true;
            }

            $dt->add($day_interval);
        }

        return $dt;
    }

    private function setField(&$field, $array) {
        if (empty($array))
            throw new \InvalidArgumentException("Crontab periods cannot be empty");

        $field = $array;
    }

    /**
     * @param DateTime $dt
     * @return int
     */
    private function getDayFromDateTime($dt) {
        return idate('d', $dt->getTimestamp());
    }

    /**
     * @param DateTime $dt
     * @return int
     */
    private function getMonthFromDateTime($dt) {
        return idate('m', $dt->getTimestamp());
    }

    /**
     * @param DateTime $dt
     * @return int
     */
    private function getDayOfWeekFromDateTime($dt) {
        return idate('w', $dt->getTimestamp());
    }

    /**
     * @param array $field Field to be searched
     * @param int $value
     * @param int $range_start
     * @param int $range_end
     * @param int $add
     * @return int Value of next field
     */
    private function getAfter($field, $value, $range_start, $range_end, &$add) {
        if (empty($field))
            return -1;

        $add = 0;
        if ($value > $range_end) {
            $value = $range_start;
            $add = 1;
        }

        while(!in_array($value, $field)) {
            if (++$value > $range_end) {
                $value = $range_start;
                $add = 1;
            }
        }

        return $value;
    }
}