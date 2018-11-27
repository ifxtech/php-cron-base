<?php

namespace szczad;

/**
 * User: szczad
 * Date: 23.10.18
 * Time: 14:04
 */


use szczad\job\JobProcessor;
use szczad\schedule\Scheduler;

declare(ticks = 1);

class Cron {
    private $runner;

    public function __construct($scheduler = null, $processor = null) {
        $scheduler = $scheduler ?: new Scheduler();
        $processor = $processor ?: new JobProcessor();

        $this->runner = new CronRunner($scheduler, $processor);
        $fn = function($signo, $sig) {
            switch ($signo) {
                case SIGTERM:
                case SIGINT:
                    echo "Killing remaining jobs and quitting...\n";
                    $this->runner->stop();
                    break;
            }
        };

        pcntl_signal(SIGTERM, $fn);
        pcntl_signal(SIGINT, $fn);
    }

    public function run() {
        $this->runner->run();
    }

    public function stop() {
        $this->runner->stop();
    }
}