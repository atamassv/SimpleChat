<?php

use Colors\Color;
use React\Socket\ConnectionInterface;

Class ConnectionsPool {

    Protected $connections;

    public function __construct()
    {
        $this->connections = new SplObjectStorage();
    }

    /**
     * add
     *
     * @param  ConnectionInterface $connection
     *
     * @return void
     */
    public function add(ConnectionInterface $connection) {

        $connection->write((new Color("Welcome to the channel!\n"))->fg("green"));
        $connection->write("Enter your username:");
        $this->setConnectionName($connection, '');
        $this->handelEvents($connection);
    }

    /**
     * handelEvents
     *
     * @param  ConnectionInterface $connection
     *
     * @return void
     */
    private function handelEvents(ConnectionInterface $connection){
        $connection->on('data', function($data) use ($connection) {
            $name = $this->getConnectionName($connection);
            if(empty($name)){
                $this->addNewMember($connection, $data);
                return;
            }
            $this->sendAll((new Color("$name:"))->bold() ." $data", $connection);
        });

        $connection->on('close', function() use ($connection) {
            $name = $this->getConnectionName($connection);
            $this->connections->offsetUnset($connection);
            $this->sendAll((new Color("The user ".$name." just left the cahnnel!\n"))->fg("red"), $connection);
        });
    }

    /**
     * sendAll
     *
     * @param  String $message
     * @param  ConnectionInterface $except
     *
     * @return void
     */
    private function sendAll($message, ConnectionInterface $except){
        foreach($this->connections as $connection){
            if($connection !== $except){
                $connection->write($message);
            }
        }
    }

    /**
     * getConnectionName
     *
     * @param  ConnectionInterface $connection
     *
     * @return void
     */
    private function getConnectionName(ConnectionInterface $connection){
        return $this->connections->offsetGet($connection);
    }

    /**
     * setConnectionName
     *
     * @param  ConnectionInterface $connection
     * @param  String $name
     *
     * @return void
     */
    private function setConnectionName(ConnectionInterface $connection, $name){
        $this->connections->offsetSet($connection, $name);
    }

    /**
     * addNewMember
     *
     * @param  ConnectionInterface $connection
     * @param  String $name
     *
     * @return void
     */
    private function addNewMember(ConnectionInterface $connection, $name){
        $name = str_replace(["\n", "\r"], '', $name);
        $this->setConnectionName($connection, $name);
        $this->sendAll((new Color("User $name joined the channel\n"))->fg("blue"), $connection);
    }
}