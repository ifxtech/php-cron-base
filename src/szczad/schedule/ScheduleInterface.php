<?php
/**
 * User: szczad
 * Date: 30.10.18
 * Time: 15:54
 */

namespace szczad\schedule;

use DateTime;
use szczad\job\JobBuilder;
use szczad\job\JobHandler;

interface ScheduleInterface {
    /**
     * @param int|null $time
     * @return DateTime
     */
    public function getNextRun($time = null);

    /**
     * @param JobBuilder $builder
     * @return JobHandler
     */
    public function getJob($builder);
}