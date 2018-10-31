<?php
/**
 * Created by IntelliJ IDEA.
 * User: szczad
 * Date: 23.10.18
 * Time: 14:37
 */

namespace szczad\job;


use szczad\schedule\Schedule;

class Job {
    const OUT = "OUT";
    const ERR = "ERR";

    private $log = array();
    /**
     * @var Schedule
     */
    protected $schedule;
    /**
     * @var JobHandler
     */
    protected $job_impl;

    /**
     * AbstractJob constructor.
     * @param JobHandler $job_impl
     */
    function __construct($job_impl) {
        $this->job_impl = $job_impl;
    }

    /**
     * @return Schedule
     */
    public function getSchedule() {
        return $this->job_impl->getSchedule();
    }

    /**
     * @return string
     */
    public function getCommand() {
        return $this->getCommand();
    }

    public function run() {
        return $this->job_impl->run();
    }

    public function isRunning() {
        return $this->job_impl->isRunning();
    }

    public function terminate() {
        $this->job_impl->terminate();
    }

    public function forceTerminate() {
        $this->job_impl->terminate();
    }

    /**
     * @param string|null $type
     * @return array
     */
    public function getOutput($type = null) {
        $temp_array = array();
        foreach($this->log as $line) {
            if (($type === null) || ($line["type"] === $type)) {
                array_push($temp_array, sprintf("%s: %s", $line["type"], $line["line"]));
            }
        }

        return $temp_array;
    }

    /**
     * @param string $type
     * @param string $line
     */
    public function addLog($type, $line) {
        if (($type === Job::OUT) || ($type === Job::ERR))
            array_push($this->log, array("type" => $type, "line" => $line));
        else
            throw new \InvalidArgumentException("Output type should be AbstractJob::OUT or AbstractJob::ERR");
    }


}