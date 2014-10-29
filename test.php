<?php

require 'vendor/autoload.php';
require 'system/queue.php';

$msg = array("to" => "phone number", "from" => "Test", "message" => "Hello!");
$send = json_encode($msg);

$sms = new beanstalkd('client');
$sms->addtask('tx', $msg);

exit();
