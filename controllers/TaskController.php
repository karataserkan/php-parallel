<?php

namespace app\controllers;

use Amp\ByteStream;
use Amp\Delayed;
use Amp\Loop;
use Amp\Parallel\Context\Parallel;
use Amp\Parallel\Context\Process;
use Amp\Parallel\Worker;
use Amp\Parallel\Worker\CallableTask;
use Amp\Parallel\Worker\DefaultWorkerFactory;
use Amp\Promise;

/**
 * Task Controller.
 */
class TaskController extends BaseController
{
    public function test()
    {
        printf('test');
    }

    public function parallel()
    {
        $start = microtime(true);
        $urls  = [
            'https://secure.php.net',
            'https://amphp.org',
            'https://github.com',
        ];

        $promises = [];
        foreach ($urls as $url) {
            $promises[$url] = Worker\enqueueCallable('file_get_contents', $url);
        }

        $responses = Promise\wait(Promise\all($promises));

        foreach ($responses as $url => $response) {
            \printf("Read %d bytes from %s\n", \strlen($response), $url);
        }

        $end = microtime(true);

        \printf("Execution time: %f\n", $end - $start);
    }

    public function standart()
    {
        $start = microtime(true);
        $urls  = [
            'https://secure.php.net',
            'https://amphp.org',
            'https://github.com',
        ];

        foreach ($urls as $url) {
            $response = file_get_contents($url);
            \printf("Read %d bytes from %s\n", \strlen($response), $url);
        }

        $end = microtime(true);

        \printf("Execution time: %f\n", $end - $start);
    }

    public function worker()
    {
        Loop::run(function () {
            $factory = new DefaultWorkerFactory();

            $worker = $factory->create();

            $result = yield $worker->enqueue(new CallableTask('file_get_contents', ['https://google.com']));
            \printf("Read %d bytes\n", \strlen($result));

            $code = yield $worker->shutdown();
            \printf("Code: %d\n", $code);
        });
    }

    public function process()
    {
        Loop::run(function () {
            $timer = Loop::repeat(1000, function () {
                static $i;
                $i = $i ? ++$i : 1;
                print "Demonstrating how alive the parent is for the {$i}th time.\n";
            });

            try {
                // Create a new child process that does some blocking stuff.
                $context = yield Process::run(__DIR__ . "/../helpers/blocking-process.php");

                \assert($context instanceof Process);
                // Pipe any data written to the STDOUT in the child process to STDOUT of this process.
                Promise\rethrow(ByteStream\pipe($context->getStdout(), ByteStream\getStdout()));

                print "Waiting 2 seconds to send start data...\n";
                yield new Delayed(2000);

                yield $context->send("Start data"); // Data sent to child process, received on line 9 of blocking-process.php

                \printf("Received the following from child: %s\n", yield $context->receive()); // Sent on line 14 of blocking-process.php
                \printf("Process ended with value %d!\n", yield $context->join());
            } finally {
                Loop::cancel($timer);
            }
        });
    }

    public function parallel_extension()
    {
        Loop::run(function () {
            $timer = Loop::repeat(1000, function () {
                static $i;
                $i = $i ? ++$i : 1;
                print "Demonstrating how alive the parent is for the {$i}th time.\n";
            });

            try {
                // Create a new child thread that does some blocking stuff.
                $context = yield Parallel::run(__DIR__ . "/../helpers/blocking-process.php");

                \assert($context instanceof Parallel);

                print "Waiting 2 seconds to send start data...\n";
                yield new Delayed(2000);

                yield $context->send("Start data"); // Data sent to child process, received on line 9 of blocking-process.php

                \printf("Received the following from child: %s\n", yield $context->receive()); // Sent on line 14 of blocking-process.php
                \printf("Process ended with value %d!\n", yield $context->join());
            } finally {
                Loop::cancel($timer);
            }
        });
    }
}
