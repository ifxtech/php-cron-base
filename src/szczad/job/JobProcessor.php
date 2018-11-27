<?php
/**
 * User: szczad
 * Date: 31.10.18
 * Time: 16:10
 */

namespace szczad\job;


class JobProcessor {

    /**
     * @var Job[]
     */
    private $jobs = [];

    /**
     * @param $job
     */
    public function addJob($job) {
        if (!in_array($job, $this->jobs))
            $this->jobs[] = $job;
    }

    public function update() {
        foreach ($this->jobs as $key => $job) {
            $job->update();
            if (!$job->isRunning())
                unset($this->jobs[$key]);
        }
    }
}