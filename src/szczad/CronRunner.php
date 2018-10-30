<?php

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

    public function __construct($scheduler) {
        $this->scheduler = $scheduler;
    }

    public function run() {
        $this->running = true;

        $this->processing = true;
        while ($this->processing) {

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