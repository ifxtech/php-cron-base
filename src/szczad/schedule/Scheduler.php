<?php
/**
 * Created by IntelliJ IDEA.
 * User: szczad
 * Date: 23.10.18
 * Time: 14:36
 */

namespace szczad\schedule;


use szczad\job\JobBuilder;

class Scheduler {

    /**
     * @var Schedule[]
     */
    private $schedules;

    public function __construct($schedules = []) {
        $this->schedules = $schedules;
    }

    /**
     * @param Schedule $schedule Schedule for Job running routine
     * @return bool true if operation succeeds, false otherwise
     */
    public function addSchedule($schedule) {
        if (in_array($schedule, $this->schedules))
            return false;

        $this->schedules[] = $schedule;
        $this->update();

        return true;
    }

    /**
     * @param Schedule $schedule
     * @return bool true if operation succeeds, false otherwise
     */
    public function removeSchedule($schedule) {
        if (($key = array_search($schedule, $this->schedules)) !== false) {
            unset($this->schedules[$key]);
            $this->update();

            return true;
        }

        return false;
    }

    public function update() {
        usort($this->schedules, "self::sort");
    }

    /**
     * @return bool|int Returns amount of seconds till next schedule or
     */
    public function getTimeToNextJobs() {
        if (count($this->schedules) === 0)
            return false;

        $time = $this->schedules[0]->compare(time());
        return ($time > 0) ? $time : false;
    }

    /**
     * @param JobBuilder $builder
     * @return array
     */
    public function getJobs($builder) {
        $time = time();
        $jobs = [];
        foreach ($this->schedules as $schedule) {
            if ($schedule->compare($time) === 0)
                $jobs[] = $schedule->getJob($builder);
        }
        return $jobs;
    }

    private static function sort($left, $right) {
        return $left->compare($right);
    }

}