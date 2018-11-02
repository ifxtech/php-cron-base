<?php

namespace szczad;

use szczad\job\JobProcessor;
use szczad\schedule\Scheduler;

/**
 * User: szczad
 * Date: 30.10.18
 * Time: 14:04
 */

class CronRunner {
    private $processing = false;
    private $running = false;
    private $scheduler;
    private $job_processor;
    private $logger;

    /**
     * CronRunner constructor.
     * @param Scheduler $scheduler
     * @param JobProcessor $job_processor
     */
    public function __construct($scheduler, $job_processor, $logger = null) {
        $this->scheduler = $scheduler;
        $this->job_processor = $job_processor;
        $this->logger = $logger;
    }

    public function run() {
        $this->running = true;

        $this->processing = true;
        while ($this->processing) {
            $this->job_processor->update();
//            $timer = $this->job_processor->isProcessing() ? 1 : $this->scheduler->getTimeToNextJob();
            $timer = 1;
            sleep($timer);
        }
        $this->cleanup();

        $this->running = false;
    }

    public function isRunning() {
        return $this->running;
    }

    public function stop() {
        if (!$this->isRunning())
            return;

        if ($this->processing)
            $this->processing = false;
        else
            $this->forceStop();
    }

    private function forceStop() {

    }


    private function cleanup() {

    }
}