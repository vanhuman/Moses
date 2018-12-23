<?php
/*
 * http://localhost/start_up/monitor
 */
namespace Harm;

require_once HARM_START_UP_BASE_PATH . '/controllers/monitor_controller.php';

$monitor = new \Harm\Monitor_controller();
$monitor->show();