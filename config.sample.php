<?php
// Used to instantiate : IMDb\Client::iniWithConfig($here)
return [
    'cache' => 'file',
    'googleCustomSearch' => [
        'apiKey' => 'xxxxxxxxxxx',
        'engineId' => 'xxx:yyyyy',
    ],
    'imdbCredentials' => [
        'id' => 'user@domain.com',
        'passw' => 'passw',
    ],
    // rewrite to null for unit test (no account)
    'googleCustomSearch' => null,
    'imdbCredentials' => null,
];