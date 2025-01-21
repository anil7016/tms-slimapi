<?php

return function ($container) {
    $container->set('settings', function () {
        return [
            'displayErrorDetails' => true, // Set to false in production
        ];
    });
};
