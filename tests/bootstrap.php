<?php

declare(strict_types=1);

// report all errors
error_reporting(-1);

// require composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

call_user_func(function (): void {
    // Command that starts the built-in web server
    $command = sprintf(
        'php -S %s:%d -t %s >/dev/null 2>&1 & echo $!',
        '127.0.0.1',
        '8999',
        escapeshellarg(__DIR__ . '/public')
    );

    // Execute the command and store the process ID
    $output = [];
    exec($command, $output);
    if (! isset($output[0])) {
        trigger_error('Unable to start server using ' . $command, E_USER_ERROR);
    }
    $pid = (int) $output[0];

    // Kill the web server when the process ends
    register_shutdown_function(function () use ($pid): void {
        exec('kill ' . $pid);
    });

    $maxWaitTime = time() + 2;
    $expectedFileCheck = file_get_contents(__DIR__ . '/public/is-working-probe.txt') ?: '';
    do {
        usleep(40000); // wait 0.4 seconds to server start before continue

        if (time() > $maxWaitTime) { // maximum time reached
            trigger_error('Unable to test that server is running using ' . $command, E_USER_ERROR);
        }

        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        $givenData = @file_get_contents('http://127.0.0.1:8999/is-working-probe.txt') ?: '';
        if ($expectedFileCheck === $givenData) {
            break; // server is working
        }

    } while ('' === $givenData);

});
