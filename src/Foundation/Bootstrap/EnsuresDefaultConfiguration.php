<?php

namespace Orchestra\Testbench\Foundation\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Env;

/**
 * @internal
 */
final class EnsuresDefaultConfiguration
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app): void
    {
        /** @var \Illuminate\Contracts\Config\Repository $config */
        $config = $app['config'];

        $config->set([
            Collection::make([
                'APP_KEY' => ['app.key' => 'AckfSECXIvnK5r28GVIWUAxmbBSjTsmF'],
                'APP_DEBUG' => ['app.debug' => true],
                'DB_CONNECTION' => \defined('TESTBENCH_DUSK') ? ['database.default' => 'testing'] : null,
            ])->filter()
            ->reject(function ($config, $key) {
                return ! \is_null(Env::get($key));
            })->values()
            ->all(),
        ]);
    }
}
