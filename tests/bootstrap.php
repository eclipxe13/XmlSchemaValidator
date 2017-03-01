<?php

// report all errors
error_reporting(-1);

// require composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

call_user_func(function () {
    // Command that starts the built-in web server
    $command = sprintf(
        'php -S %s:%d -t %s >/dev/null 2>&1 & echo $!',
        '127.0.0.1',
        '8999',
        escapeshellarg(__DIR__ . '/assets')
    );

    // Execute the command and store the process ID
    $output = [];
    exec($command, $output);
    if (! isset($output[0])) {
        trigger_error('Unable to start server using ' . $command, E_USER_ERROR);
        return;
    }
    $pid = (int) $output[0];

    // Kill the web server when the process ends
    register_shutdown_function(function () use ($pid) {
        exec('kill ' . $pid);
    });
    // wait 0.5 seconds to server start before continue
    usleep(50000);
});
