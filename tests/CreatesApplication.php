<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application — يفرض sqlite + testing env قبل bootstrap.
     *
     * 🔴 لا تحذف هذه القيم — بدونها قد تشتغل الاختبارات على DB الإنتاج بالخطأ.
     */
    public function createApplication()
    {
        $forced = [
            'APP_ENV'           => 'testing',
            'APP_KEY'           => 'base64:R+Mh2PEm9Z14XPRunW8wT2uAayD8EVgF2QfCkqBp1V8=',
            'APP_DEBUG'         => 'true',
            'DB_CONNECTION'     => 'sqlite',
            'DB_DATABASE'       => ':memory:',
            'CACHE_STORE'       => 'array',
            'SESSION_DRIVER'    => 'array',
            'SESSION_ENCRYPT'   => 'false',
            'QUEUE_CONNECTION'  => 'sync',
            'MAIL_MAILER'       => 'array',
            'BCRYPT_ROUNDS'     => '4',
            'BROADCAST_CONNECTION' => 'log',
            'LOG_CHANNEL'       => 'null',
            'TELESCOPE_ENABLED' => 'false',
        ];

        foreach ($forced as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
        }

        $app = require __DIR__ . '/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
