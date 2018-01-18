<?php

namespace app\components;


/**
 * TelnetException class
 * Extension to throw specific exceptions
 */
class TelnetException extends \Exception
{
}


/**
 * Simplified telnet class
 */
class Telnet
{

    /**
     * Timeout in seconds
     * @var int
     */
    private $timeout = 5;

    /**
     * Socket resource handler
     * @var resource
     */
    private $socket;

    /**
     * Property for storing raw data read from context
     * @var string
     */
    private $data;

    /**
     * Failed login string patterns
     * @var array
     */
    private $auth_fail = [
        '/fail/i',
        '/error/i',
    ];

    /**
     * @var string
     */
    private $prompt = '/#/';


    /**
     * @return void
     */
    public function __destruct()
    {
        if ( is_resource($this->socket) ) {
            $this->send("logout\n");
            fclose($this->socket);
        }
    }

    /**
     * Timeout setter
     * Sets value for stream_set_timeout() second argument (only seconds)
     *
     * @param  int $timeout
     * @return $this
     */
    public function setTimeout(int $timeout)
    {

        $this->timeout = intval($timeout);

        if( $this->timeout < 1 ) {
            $this->timeout = 1;
            trigger_error('Timeout can not be less than 1 second', E_USER_NOTICE);
        }

        if( is_resource($this->socket) ) {
            stream_set_timeout($this->socket, $this->timeout, 0);
        }

        return $this;

    }


    /**
     * @param  string $prompt
     * @return $this
     */
    public function setPrompt(string $prompt)
    {
        $this->prompt = $prompt;
        return $this;
    }


    /**
     * Return data which was read from socket
     *
     * @see waitfor()
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }


    /**
     * Socket initialization
     *
     * @param  string $ip ip address
     * @param  int    $port remote port
     * @throws TelnetException
     * @return $this
     */
    public function connect($ip, $port=23)
    {

        if ( !is_resource($this->socket) ) {
            $this->socket = stream_socket_client("$ip:$port", $errno, $errstr, $this->timeout);
        }

        if( is_resource($this->socket) ) {
            stream_set_timeout($this->socket, $this->timeout, 0);
            return $this;
        }

        throw new TelnetException('Unable to open connection');

    }


    /**
     * Waiting for the line
     *
     * @param  string $pattern  regular expression to be waited for
     * @param  bool   $silent   if false - an error will be triggered in case of timeout
     *                          if true - timeout error will be suppressed
     * @return $this
     */
    public function waitfor(string $pattern, bool $silent = false)
    {

        $start      = microtime( true );
        $this->data = '';

        // Loop until timeout is hit or pattern is found
        do {

            $this->data.= fread( $this->socket, 1 );
            $match      = preg_match($pattern, $this->data);

            if( (microtime(true) - $start) > $this->timeout ) {
                if (!$silent) {
                    trigger_error("Reading socket timed out in $this->timeout seconds", E_USER_WARNING);
                }
                break;
            }

        } while ( $match == 0 );

        return $this;

    }


    /**
     * Send data
     *
     * @param  string $data     the data to be sent to the socket
     * @param  bool   $newLine  should 'enter' be added to the command
     * @return bool
     */
    public function send(string $data, bool $newLine = true): bool
    {

        $data = stream_socket_sendto($this->socket, $data);

        if( $newLine ) {
            $data = stream_socket_sendto($this->socket, "\n") + $data;
        }

        return boolval($data);

    }


    /**
     * Authentication wrapper
     *
     * @param  string $login  username
     * @param  string $pass   password
     * @param  array  $prompt array with credentials
     *                        two elements should be provided: [ login, password ]
     * @throws TelnetException
     * @return bool
     */
    public function login(string $login, string $pass, array $prompt = ['/ame:/i', '/ord:/i']): bool
    {

        $this->waitfor($prompt[0], true)->send($login);
        $this->waitfor($prompt[1], true)->send($pass);

        // Read data
        $raw = $this->waitfor($this->prompt, true)->getData();

        foreach( $this->auth_fail as $pattern ) {
            if( preg_match($pattern, $raw) ) {
                throw new TelnetException('Authentication failed');
            }
        }

        return true;

    }

}
