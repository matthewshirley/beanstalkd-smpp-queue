<?php

/**
 * BeanstalkD and SMPP Queue
 *
 * @author     Matthew Shirley
 * @version    Development
 *
 **/

require_once 'vendor/smpp/smppclient.class.php';
require_once 'vendor/smpp/gsmencoder.class.php';
require_once 'vendor/smpp/sockettransport.class.php';

class sms
{
    /*
     * Handles the SMPP Protocol
     */ 
     
    private $smpp;
    
    /*
     * Handles to Sockets
     */
     
    private $transport;
    
    /**
     * Connect to the SMPP Server
     *
     * @param $type - TX or RX
     * @param $server - Host IP
     * @param $username - SMPP Username
     * @param $password - SMPP Password
     * @param $port - SMPP Port
     * @param $debug - Do we debug the connection
     **/
    
    public function connect($type, $server, $username, $password, $port, $debug = false)
    {
        $this->transport = new SocketTransport(array($server), $port);
        $this->transport->debug = $debug;
            
        $this->smpp = new SmppClient($this->transport);
        $this->smpp->debug = $debug;
        
        $this->transport->open();
        
        if($type == 'tx')
        {
            $this->smpp->BindTransmitter($username, $password);
        }
        elseif($type == 'rx')
        {
            $this->smpp->bindReceiver($username, $password);
        }
    }
    
    /**
     * Deconstructs the service
     * 
     **/
    
    public function __deconstruct() 
    {
        $this->smpp->close();
        $this->transport->close();
    }
    
    /**
     * Sends a packet to the server to keep connection alive
     * 
     **/
    
    public function ping()
	{
		$this->smpp->enquireLink();
		$this->smpp->respondEnquireLink();
	}
	
	/**
     * Sends the SMS to SMPP Server. This is a fucking poor effort; needs more work. 
     *
     * @param $sms - Array of message, from, to
     * @param $tags - any SMPP tags
     **/
	
    public function sender($sms, $tags = null)
    {
        try 
        {
            $encoded = GsmEncoder::utf8_to_gsm0338($sms['message']);
            $sender = new SmppAddress($sms['from'], SMPP::TON_ALPHANUMERIC);
            $reciever = new SmppAddress($sms['to'], SMPP::TON_NATIONAL, SMPP::NPI_E164);
            
            $this->smpp->sendSMS($sender, $reciever, $encoded, $tags);
            
            return true;
        }
        catch (Exception $e) 
        {
            return false;
        }
    }
    
    /**
     * Reads the SMPP Server for any waiting messages
     **/
    
    public function read()
	{
	    $sms = $this->smpp->ReadSMS();
	    
	    return $sms;
	}
}

?>