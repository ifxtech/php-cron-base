<?php
/**
 * Created by IntelliJ IDEA.
 * User: szczad
 * Date: 23.10.18
 * Time: 15:12
 */

namespace szczad\job;

use Psr\Log\LogLevel;
use Symfony\Component\Process\Process;
use szczad\schedule\Schedule;
use szczad\util\Log;

class ProcessJob implements JobInterface, JobHandler {
    const DEFAULT_PROCESS_TIMEOUT = 300;

    const LOG_MAPPING = [
        Process::ERR => LogLevel::ERROR,
        Process::OUT => LogLevel::INFO,
    ];

    /**
     * @var int Default timeout after which to kill the process
     */
    private $timeout = 300;
    private $stop_timeout = 10;
    private $stop_signal = SIGINT;

    private $command;
    private $process;
    private $job;
    private $iterator;

    /**
     * @param static $command
     * @return bool
     */
    public static function canHandle($command) {
        return true;
    }

    /**
     * @param Schedule $schedule
     * @param string $command
     * @param $options
     * @return Job
     */
    public static function getJob($schedule, $command, $options) {
        $impl = new self();
        $impl->command = $command;
        $impl->timeout = $options['timeout'];
        $impl->stop_signal = $options['stop_signal'];
        $impl->stop_timeout = $options['stop_timeout'];
        $impl->job = new Job($schedule, $impl);
        return $impl->job;
    }

    public function __construct() {
        $this->process = new Process($this->command, '/', null, null, $this->timeout);
    }

    /**
     * @return int
     */
    public function getTimeout() {
        return $this->timeout;
    }

    /**
     * @return int
     */
    public function getStopTimeout() {
        return $this->stop_timeout;
    }

    /**
     * @return int
     */
    public function getStopSignal() {
        return $this->stop_signal;
    }

    /**
     * @param int $stop_signal
     */
    public function setStopSignal($stop_signal) {
        $this->stop_signal = $stop_signal;
    }

    public function run() {
        $this->process->start();
        $this->iterator = $this->process->getIterator(Process::ITER_NON_BLOCKING);
    }

    public function isRunning() {
        return $this->process->isRunning();
    }

    public function terminate() {
        // TODO: handle termination manually & asynchronously
        $this->process->stop($this->stop_timeout, $this->stop_signal);
    }

    public function getCommand() {
        return $this->command;
    }

    public function getResultCode() {
        return $this->process->getExitCode();
    }

    public function update() {
        if (!$this->process->isRunning()) {
            return;
        }

        foreach($this->iterator as $type => $message) {
            $this->addLog($type, $message);
        }
    }

    public function __toString() {
        $pid = $this->process->getPid();
        return "PID(".($pid ?: "DEAD").")";
    }

    private function addLog($type, $line) {
        Log::getInstance()->log(self::LOG_MAPPING[$type], $line, [], "PID(".$this->process->getPid().")");
    }

    public function getSchedule() {
        return $this->job->getSchedule();
    }
}

