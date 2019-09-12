<?php

use Colors\Color;
use React\Socket\ConnectionInterface;

class ConnectionsPool
{

    protected $connections;
    protected $pmConstant = '#pm';

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
    public function add(ConnectionInterface $connection)
    {
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
    private function handelEvents(ConnectionInterface $connection)
    {
        $connection->on('data', function ($data) use ($connection) {
            $name = $this->getConnectionName($connection);

            if (empty($name)) {
                $this->addNewMember($connection, $data);
                return;
            }

            if (substr($data, 0, 3) == $this->pmConstant) {
                $targetName = $this->getNameFromString($data);
                $data = str_replace(($this->pmConstant . ' ' . $targetName . ' '), '', $data);
                $tagetConnection = $this->getObjectbyName($targetName);
                if ($tagetConnection) {
                    $tagetConnection->write((new Color("PM|$name:"))->bold()->fg('magenta') . " $data");
                } else {
                    $connection->write((new Color("The user " . $targetName . " is offline!\n"))->fg('red'));
                }
            } else {
                $this->sendAll((new Color("$name:"))->bold() . " $data", $connection);
            }
        });

        $connection->on('close', function () use ($connection) {
            $name = $this->getConnectionName($connection);
            $this->connections->offsetUnset($connection);
            $this->sendAll((new Color("The user " . $name . " just left the cahnnel!\n"))->fg("red"), $connection);
        });
    }

    /**
     * getNameFromString
     *
     * @param  String $data
     *
     * @return mixt
     */
    private function getNameFromString($data)
    {
        preg_match("/" . $this->pmConstant . "\s*(\S+)/", $data, $name);

        if (isset($name[1])) {
            return trim($name[1]);
        }
    }

    /**
     * sendAll
     *
     * @param  String $message
     * @param  ConnectionInterface $except
     *
     * @return null
     */
    private function sendAll($message, ConnectionInterface $except)
    {
        foreach ($this->connections as $connection) {
            if ($connection !== $except) {
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
    private function getConnectionName(ConnectionInterface $connection)
    {
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
    private function setConnectionName(ConnectionInterface $connection, $name)
    {
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
    private function addNewMember(ConnectionInterface $connection, $name)
    {
        $name = str_replace(["\n", "\r"], '', $name);
        $this->setConnectionName($connection, $name);
        $this->sendAll((new Color("User $name joined the channel\n"))->fg("blue"), $connection);
    }

    /**
     * getObjectbyName
     *
     * @param  String $name
     *
     * @return mixt
     */
    private function getObjectbyName($name)
    {
        $this->connections->rewind();
        while ($this->connections->valid()) {
            $object = $this->connections->current();
            $data = $this->connections->getInfo();
            if ($name === $data) {
                $this->connections->rewind();

                return $object;
            }
            $this->connections->next();
        }

        return null;
    }
}
