<?php
/**
 * Created by IntelliJ IDEA.
 * User: szczad
 * Date: 23.10.18
 * Time: 15:12
 */

namespace szczad\job;

use Symfony\Component\Process\Process;
use szczad\schedule\Schedule;

class ProcessJob implements JobHandler {

    private $command;
    private $process;
    private $timeout;

    public static function canHandle($command) {
        return true;
    }

    public static function getJob($schedule) {
        return new Job(new self());
    }

    /**
     * ProcessJob constructor.
     * @param int $timeout
     */
    public function __construct($timeout = 300) {
        $this->timeout = $timeout;

        $this->process = new Process($this->command, '/', null, null, $this->timeout);
    }

    public function run() {
        $this->process->start(function ($type, $line) {
            $this->addLog($type, $line);
        });
    }


    public function isRunning() {
        return $this->process->isRunning();
    }

    public function terminate() {
        $this->process->stop();
    }

    public function forceTerminate() {
        $this->process->stop(3, SIGTERM);
    }

    /**
     * @return Schedule
     */
    public function getSchedule() {
        // TODO: Implement getSchedule() method.
    }

    public function getCommand() {
        // TODO: Implement getCommand() method.
    }
}

