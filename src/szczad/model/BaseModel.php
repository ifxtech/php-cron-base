<?php
/**
 * User: szczad
 * Date: 06.11.18
 * Time: 10:12
 */

namespace szczad\model;


interface BaseModel {
    /**
     * @return Scheduler
     */
    public function getScheduler();
}