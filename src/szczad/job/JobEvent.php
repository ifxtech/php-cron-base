<?php
/**
 * Created by IntelliJ IDEA.
 * User: szczad
 * Date: 23.10.18
 * Time: 16:13
 */

namespace szczad\job;


use szczad\schedule\Scheduler;

class JobEvent {
    const TYPE_START = 1;
    const TYPE_STOP = 2;
    const TYPE_TIMEOUT = 3;
    const TYPE_TERMINATE = 4;
    const TYPE_LOG = 5;

    private $src;
    private $scheduler;
    private $job;
    private $log_type;
    private $log_value;


    /**
     * JobEvent constructor.
     * @param $src
     * @param $schedule
     * @param $job
     */
    public function __construct($src, $schedule, $job) {
        $this->src = $src;
        $this->scheduler = $schedule;
        $this->job = $job;
    }

    /**
     * @return mixed
     */
    public function getSource() {
        return $this->src;
    }

    /**
     * @return Scheduler
     */
    public function getScheduler() {
        return $this->scheduler;
    }

    /**
     * @return AbstractJob
     */
    public function getJob() {
        return $this->job;
    }

    /**
     * @return string
     */
    public function getLogType() {
        return $this->log_type;
    }

    /**
     * @return string
     */
    public function getLogValue() {
        return $this->log_value;
    }

    /**
     * @param $type string OUT or ERR
     * @param $value string Line with log file
     */
    public function addLog($type, $value) {
        $this->log_type = $type;
        $this->log_value = $value;
    }

}