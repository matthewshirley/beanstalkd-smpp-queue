<?php

/**
 * BeanstalkD and SMPP
 *
 * @package    BeanstalkD Worker
 * @author     Matthew Shirley
 * @version    Development
 *
 **/

require('vendor/autoload.php');
require('configuration.php');
require('system/queue.php');

$worker = new beanstalkd('SMPP Reciever'); 
$worker->connect('rx', $config['smpp']);