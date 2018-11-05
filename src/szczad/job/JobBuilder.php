<?php
/**
 * Created by IntelliJ IDEA.
 * User: szczad
 * Date: 23.10.18
 * Time: 14:34
 */

namespace szczad\job;


use szczad\schedule\Schedule;

class JobBuilder {
    private static $_instance = null;
    private $job_classes = [
        ProcessJob::class
    ];

    private function __construct() {}

    public static function getInstance() {
        if (self::$_instance === null)
            self::$_instance = new self();

        return self::$_instance;
    }

    /**
     * @param string $job_handler_class
     */
    public function registerJobHandler($job_handler_class) {
        if (!in_array($job_handler_class, $this->job_classes))
            array_push($this->job_classes, $job_handler_class);
    }

    /**
     * @param Schedule $schedule
     * @param string $command
     * @return Job|null
     */
    public function getJob($schedule, $command) {
        foreach ($this->job_classes as $job) {
            if ($job::canHandle($command)) {
                return $job::getJob($schedule, $command);
            }
        }

        return null;
    }
}