beanstalkd-smpp-queue
=====================
Demostration of how BeanstalkD can be used to transport SMS via the SMPP protocol. 

Requirements
-----------

* Beanstalkd Server
* PHP
* SMPP Server (Simulator or Real)
* Composer (for updating)

Installation
-----------
I have included Pheanstalk into the respository to keep it simple although it wouldn't hurt to run an update.

    composer update

Once complete, edit `configuration.php` to match you server settings. If you know anything about SMPP, you can also change
the client settings at `vendor/smpp

Usage
-----
To run the queue workers, do this within the directory:

    php rx.php
    php tx.php

RX will handle incoming messages and recipts. They will sit in the Beanstalk queue until someone collects them.
TX handles outgoing messages.

Notes
-----------
If you're using this, please note:

* This provides basic functions - it sends and recieves. If you have any suggestions on features to do, please let me know.
* If you want to monitor or have access to the queues, download [Beanstalk Console](https://github.com/ptrofimov/beanstalk_console)
* This was tested using [SMPPSim](http://www.seleniumsoftware.com/downloads.html) and also a number of aggregators that provide SMPP connections.
