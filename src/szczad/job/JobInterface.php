<?php
/**
 * Created by IntelliJ IDEA.
 * User: szczad
 * Date: 25.10.18
 * Time: 13:36
 */

namespace szczad\job;


interface JobInterface {
    public function getSchedule();
    public function getCommand();

    public function run();
    public function terminate();
    public function getResultCode();

    public function isRunning();
    public function update();
}
