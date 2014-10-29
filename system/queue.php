<?php

/**
 * BeanstalkD and SMPP Queue
 *
 * @author     Matthew Shirley
 * @version    Development
 *
 **/

require('system/smpp.php');

use Pheanstalk\Pheanstalk;

class beanstalkd
{
    
    /**
     * @string $name - Name of our queue manager
     */
    
    private $name = 'Client';
    
    /**
    * @string $queue - Manages PheanstalkD
    */
     
    private $queue;
    
    /**
    * @string $queue - Manages the SMPP connection
    */
    private $worker;
    
    /**
     * Define queue name & create connection the BeanstalkD
     *
     * @param $who - The name of the worker to easily identify in console
     * 
     **/
    
    public function __construct($who)
    {
        $this->name  = $who;
        $this->queue = new Pheanstalk_Pheanstalk('127.0.0.1');
    }
    
    /**
     * Close the BeanstalkD Queue and exit
     **/
    
    public function __deconstruct()
    {
        $this->log('Shutting down as system requested');
        $this->worker->close();
        exit();
    }
    
    /**
     * Print out to the console in a manner that can be identifed.
     *
     * @param $what - What do you want to return?
     **/
    
    public function log($what)
    {
        echo ("[" . $this->name . "] [" . time() . "] " . $what . " \n");
    }
    
    /**
     * Checks current memory and if that memory has exceeded a limit, it wil lclose
     **/
    
    public function memcheck()
    {
        $memory = memory_get_usage();
        $this->log('Currently using: ' . $memory);
        
        if ($memory > 1000000)
        {
            $this->log('Shutting down due to excessive memory usage:' . $memory);
            exit();
        }
    }
    
    /**
     * Create a task in Beanstalkd - usually for ping or RX
     *
     * @param $tube Where?
     * @param $data What in array format? Example: 
     * $job = array("from" => "Test", "to" => "61419438238", "message" => "Hello There!","tags" => null));
     * @param $prioty How urgent?
     * @param $delay When in seconds (0 for now / ASAP)
     **/
    
    public function addtask($tube, $data, $prioty = 1024, $delay = 0)
    {
        $job = json_encode($data);
        $this->queue->useTube($tube)->put($job, $prioty, $delay);
    }
    
    /**
     * Connect to SMPP Server
     * 
     * @param $tube What queue? TX or RX?
     * @param $info What connection details?
     **/
    
    public function connect($tube, $info)
    {
        $this->log('Connecting to ' . $tube . 'queue');
        
        switch ($tube)
        {
            
            case 'tx':
                
                $this->worker = new sms();
                $this->worker->connect('tx', $info['host'], $info['user'], $info['pass'], $info['port'], $info['debug']);
                $this->sender();
                
                break;
            
            case 'rx':
                
                $this->worker = new sms();
                $this->worker->connect('rx', $info['host'], $info['user'], $info['pass'], $info['port'], $info['debug']);
                $this->read();
                
                break;

            default:
                
                $this->log('Unable to find selected queue');
                exit();
                
                break;
                
        }
        
    }
    
     /**
     * Pull data from BeanstalkD Queue and pass off to SMPP
     * 
     **/
    
    public function sender()
    {
        
        // I have added this in to keep the SMPP connection active
        // I found it was more efficient and makes great use of 
        // BeanstalkD
        
        $this->addtask('tx', array('do' => 'ping'));
        
        while (1)
        {
            $job = $this->queue->watch('tx')->ignore('default')->reserve();
            
            $smsdata = json_decode($job->getData(), true);
            
            if ($smsdata['do'] == 'ping')
            {
                $this->worker->ping();
                $this->memcheck();
                $this->queue->release($job, 2048, 30);  // Requeue same job with a 30 second delay
            }
            else
            {
                
                $work = $this->worker->sender($smsdata);
                
                if ($work)
                {
                    $this->log('Message was sent successfully to SMPP Server');
                    $this->queue->delete($job);
                }
                else
                {
                    $this->log('Failure sending message to SMPP Server');
                    $this->queue->bury($job); // Currently, buried jobs will stay there forever
                }
            }
        }
    }
    
    /**
     * Wait for incoming SMS and then put them in the queue. 
     * You could configure something else to happen here if you wanted to
     **/
    
    private function read() 
    {
        // Unlike sender, read doesn't depend on BeanstalkD
        // So we will have time()-$LastPing later on.
        
        $LastPing = time();
        $this->worker->ping();
        
        while(1)
        {
            $sms = $this->worker->read();
            
            if($sms != false)
            {
                $this->addtask('rx', $sms);
                $this->log('Recieved one inbound message');
            }
            
            if(time()-$LastPing >= 15)
            {
                $this->worker->ping();
                $this->memcheck();
                
                $LastPing = time();
            }
        }
    }
}

?>