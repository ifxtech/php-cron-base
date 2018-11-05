<?php
/**
 * Created by IntelliJ IDEA.
 * User: szczad
 * Date: 25.10.18
 * Time: 13:36
 */

namespace szczad\job;


use szczad\schedule\Schedule;

interface JobHandler {

    /**
     * @param string $command Command to check if this handler is capable of running
     * @return bool
     */
    static function canHandle($command);

    /**
     * @param Schedule $schedule The schedule this job is attached to
     * @param string $command Command which will be invoked on schedule ticks
     * @param array $options Options sent by builder to configure certain jobs
     * @return Job Job instance for single run
     */
    public static function getJob($schedule, $command, $options);
}
