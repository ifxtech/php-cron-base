<?php
/**
 * User: szczad
 * Date: 31.10.18
 * Time: 16:10
 */

namespace job;


use szczad\job\Job;

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

    public function process() {
        foreach($this->jobs as $job) {
            $job->
        }
    }
}