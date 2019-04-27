<?php
return [
    'type' => 'model',
    'ns' => 'auth',
    'fields' => [
        'id' => 'int',
        'email' => 'string',
        'name' => 'string',
        'password' => 'string',
        'createdTs' => 'timestamp',
        'updatedTs' => '?timestamp',
    ],
    'exports' => [
        'id',
        'email',
        'name',
        'password',
        'createdTs',
        'updatedTs',
    ],
    'defaults' => [
        'email' => 'a@a.a',
        'name' => '',
    ]
];
