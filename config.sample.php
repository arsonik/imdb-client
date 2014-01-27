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

    // for unit test
    'googleCustomSearch' => null,
];