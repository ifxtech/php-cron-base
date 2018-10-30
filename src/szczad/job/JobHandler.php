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

    static function canHandle($command);

    /**
     * @param Schedule $schedule
     * @return Job
     */
    public static function getJob($schedule);

    /**
     * @return Schedule
     */
    public function getSchedule();
    public function getCommand();

    public function run();
    public function isRunning();
    public function terminate();
    public function forceTerminate();
}
