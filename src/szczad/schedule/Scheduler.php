<?php
/**
 * Created by IntelliJ IDEA.
 * User: szczad
 * Date: 23.10.18
 * Time: 14:36
 */

namespace szczad\schedule;


class Scheduler {

    /**
     * @var Schedule[]
     */
    private $schedules = array();

    /**
     * @var Schedule
     */
    private $next_schedule = null;

    public function __construct() {
    }

    /**
     * @param Schedule $schedule
     */
    public function addSchedule($schedule) {
        if (!in_array($schedule, $this->schedules)) {
            $this->schedules[] = $schedule;
            $this->updateTimetable();
        }
    }

    /**
     * @param Schedule $schedule
     */
    public function removeSchedule($schedule) {
        if (($key = array_search($schedule, $this->schedules)) !== false) {
            unset($this->schedules[$key]);
            $this->updateTimetable();
        }
    }

    /**
     * @return bool|int Returns amount of seconds till next schedule or
     */
    public function getTimeToNextJob() {
        if ($this->next_schedule === null)
            return false;

        $time = $this->next_schedule->compare(time());
        return ($time > 0) ? $time : false;
    }

    private function updateTimetable() {
        $current = null;
        foreach ($this->schedules as $schedule) {
            $seconds = $schedule->compare($current);
            if ($seconds >= 0)
                $current = $schedule;
        }

        $this->next_schedule = $current;
    }
}