<?php
/**
 * User: szczad
 * Date: 23.10.18
 * Time: 14:04
 */

namespace szczad;

use CronRunner;
use szczad\schedule\Scheduler;

class Cron {
    private $runner;
    private $scheduler;

    public function __construct() {
        $this->scheduler = new Scheduler();
        $this->runner = new CronRunner($this->scheduler);


        pcntl_signal(SIGTERM, function($signo, $sig) {
            switch ($signo) {
                case SIGTERM:
                    echo "Killing remaining jobs and quitting...";
                    $this->runner->stop();
            }
        });
    }


    public function run() {
        $this->runner->run();
    }

    public function stop() {
        $this->runner->stop();
    }
}