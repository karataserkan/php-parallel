<?php

// The function returned by this script is run by process.php in a separate process.
// $argc and $argv are available in this process as any other cli PHP script.

use Amp\Parallel\Sync\Channel;

return function (Channel $channel): \Generator {
    \printf("Received the following from parent: %s\n", yield $channel->receive());

    print "Sleeping for 20 seconds...\n";
    \sleep(20); // Blocking call in process.

    yield $channel->send("Data sent from child.");

    print "Sleeping for 10 seconds...\n";
    \sleep(10); // Blocking call in process.

    return 42;
};