<?php

return [
    [
        'client_id' => 'testclient',
        'client_secret'  => 'testpass',
        'redirect_uri' => 'http://localhost/ http://localhost:8080/ '
            . 'http://127.0.0.1/ http://127.0.0.1:8080/',
        'grant_types' => 'client_credentials authorization_code '
            . 'password implicit'
    ],
];
