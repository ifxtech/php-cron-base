<?php
/**
 * Created by IntelliJ IDEA.
 * User: szczad
 * Date: 23.10.18
 * Time: 14:37
 */

namespace szczad\job;


use szczad\schedule\Schedule;

class Job implements JobInterface {
    /**
     * @var Schedule
     */
    private $schedule;
    /**
     * @var JobInterface
     */
    private $job_impl;

    /**
     * AbstractJob constructor.
     * @param Schedule $schedule
     * @param JobInterface $job_impl
     */
    public function __construct($schedule, $job_impl) {
        $this->job_impl = $job_impl;
        $this->schedule = $schedule;
    }

    /**
     * @return Schedule
     */
    public function getSchedule() {
        return $this->schedule;
    }

    /**
     * @return string
     */
    public function getCommand() {
        return $this->job_impl->getCommand();
    }

    public function run() {
        return $this->job_impl->run();
    }

    /**
     * @return bool
     */
    public function isRunning() {
        return $this->job_impl->isRunning();
    }

    public function terminate() {
        $this->job_impl->terminate();
    }

    public function update() {
        $this->job_impl->update();
    }

    /**
     * @return int
     */
    public function getResultCode() {
        return $this->job_impl->getResultCode();
    }
}