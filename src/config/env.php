<?php

use Dotenv\Dotenv;

return function () {
    // Corrected path to the root directory where .env is located
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');  // This points to the root of the project

    // Load environment variables
    $dotenv->load();

    // Test output
};
