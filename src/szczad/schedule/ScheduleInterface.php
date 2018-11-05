<?php
/**
 * User: szczad
 * Date: 30.10.18
 * Time: 15:54
 */

namespace szczad\schedule;

use DateTime;
use szczad\job\JobBuilder;
use szczad\job\JobInterface;

interface ScheduleInterface {
    /**
     * @param int|null $time
     * @return DateTime
     */
    public function getNextRun($time = null);

    /**
     * @param JobBuilder $builder
     * @return JobInterface
     */
    public function getJob($builder);
}