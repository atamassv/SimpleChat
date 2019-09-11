<?php

use \React\Socket\Connector;
use React\Socket\ConnectionInterface;

require 'vendor/autoload.php';

$loop = \React\EventLoop\Factory::create();
$input = new \React\Stream\ReadableResourceStream(STDIN, $loop);
$output = new \React\Stream\WritableResourceStream(STDOUT, $loop);

$connector = new Connector($loop);
$connector->connect('127.0.0.1:8000')
    ->then(
        function(ConnectionInterface $connection) use ($input, $output){
    
            /** 
             *EXAMPLE 1
            */

            /*
            $input->on('data', function ($data) use ($connection) {
                $connection->write($data);
            });
            $connection->on('data', function ($data) use ($output) {
                $output->write($data);
            });
            */

            /**
             *EXAMPLE 2
            */

            /*
            $input->pipe($connection);
            $connection->pipe($output);
            */

            /**
             * EXAMPLE 3
            */
            
            $input->pipe($connection)->pipe($output);
        },
        function(Exception $exception){
            echo $exception->getMessage() . PHP_EOL;
        }
    );

$loop->run();