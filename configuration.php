<?php

/**
 * BeanstalkD and SMPP
 *
 * @package    BeanstalkD Worker
 * @author     Matthew Shirley
 * @version    Development
 *
 **/

$config = array();

$config['app']['name'] = 'SMPP BeanstalkD Queue';
$config['app']['version'] = 'dev';

$config['beanstalk']['host'] = '127.0.0.1';
$config['beanstalk']['port'] = null;
$config['beanstalk']['user'] = null;
$config['beanstalk']['pass'] = null;
$config['beanstalk']['debug'] = true;

$config['smpp']['host'] = '127.0.0.1';
$config['smpp']['port'] = 2775;
$config['smpp']['user'] = 'smppclient1';
$config['smpp']['pass'] = 'password';
$config['smpp']['debug'] = true;

?>