<?php
/**
 * Created by IntelliJ IDEA.
 * User: szczad
 * Date: 23.10.18
 * Time: 14:40
 */

namespace szczad\job;


interface JobEventListener {
    public function onEvent($event);
}