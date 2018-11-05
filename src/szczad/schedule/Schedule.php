<?php
/**
 * Created by IntelliJ IDEA.
 * User: szczad
 * Date: 24.10.18
 * Time: 13:58
 */

namespace szczad\schedule;


use DateTime;
use szczad\job\JobBuilder;
use szczad\job\JobInterface;

class Schedule implements ScheduleInterface {
    /**
     * @var ScheduleInterface
     */
    private $schedule_impl;

    /**
     * Schedule constructor.
     * @param ScheduleInterface $schedule_impl
     */
    public function __construct(ScheduleInterface $schedule_impl) {
        $this->schedule_impl = $schedule_impl;
    }


    /**
     * @param int|null $time
     * @return DateTime
     */
    public function getNextRun($time = null){
        return $this->schedule_impl->getNextRun($time);
    }

    /**
     * @param JobBuilder $builder
     * @return JobInterface
     */
    public function getJob($builder) {
        return $this->schedule_impl->getJob($builder);
    }

    /**
     * @param Schedule|int $schedule Schedule or unix timestamp to compare with.
     * @return int Returns difference between current and given schedules
     * positive - Given schedule is ahead of current one by the amount of seconds
     *        0 - Both objects are scheduled for the same point in time
     * negative - Given schedule is behind current one by the amount of seconds
     */
    public function compare($schedule) {
        if ($schedule === null)
            return -INF;

        $current_seconds = $this->schedule_impl->getNextRun()->getTimestamp();
        $compare_seconds = ($schedule instanceof Schedule) ? $schedule->getNextRun()->getTimestamp() : $schedule;

        return $compare_seconds - $current_seconds;
    }
}