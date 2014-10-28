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
require('system/beanstalkd.php');

$worker = new beanstalkd('Beth'); 
$worker->connect('tx', $config['smpp']);