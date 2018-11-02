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
     * @var Job[]
     */
    private $current_jobs = [];

    /**
     * @param $job
     */
    public function addJob($job) {
        if (!in_array($job, $this->jobs))
            $this->jobs[] = $job;
    }

    public function update() {
        foreach ($this->jobs as $job) {
            if (!$job->isRunning() && in_array($job, $this->current_jobs)) {
                $key = array_search($this->current_jobs, $job);
                unset($this->current_jobs['$key']);
            } elseif () {

            }
        }
    }

    public function isProcessing() {
        foreach ($this->current_jobs as $index => $job) {
            if (!$job->isRunning()) {
                unset($this->current_jobs[$index]);
            }
        }

        return !empty($this->current_jobs);
    }

    private function calculateCurrentJobs() {
        foreach($this->jobs as $job) {
            if ($job->isRunning() && in_array($job, $this->current_jobs)) {

            }
        }
    }
}