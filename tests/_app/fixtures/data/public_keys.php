<?php

return [
    [
        'client_id' => 'testclient',
        'public_key' => file_get_contents(__DIR__ . '/certificate.pem'),
        'private_key' => file_get_contents(__DIR__ . '/private.pem'),
    ],
];
