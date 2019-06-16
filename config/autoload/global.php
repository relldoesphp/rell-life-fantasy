<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return [
        'db' => [
            'driver' => 'Pdo',
            'adapters' => [
                'Rlf\Db\Adapter' => [
                    'driver' => 'Pdo',
                    'dsn'    => 'mysql:dbname=fantasy_football;host=localhost;charset=utf8',
                    'username' => 'rell',
                    'password' => 'rell'
                ],
                'Dtw\Db\Adapter' => [
                    'driver' => 'Pdo',
                    'dsn'    => 'mysql:dbname=dtw_dev;host=drafttradewin.com;charset=utf8',
                    'username' => 'rell',
                    'password' => '3523Kaleb!'
                ],
            ],
        ],
];
