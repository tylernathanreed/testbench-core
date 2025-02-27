<?php

namespace Orchestra\Testbench\Concerns;

use Closure;
use Illuminate\Database\Events\DatabaseRefreshed;
use Orchestra\Testbench\Attributes\DefineDatabase;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\Exceptions\ApplicationNotAvailableException;
use Orchestra\Testbench\Features\TestingFeature;

/**
 * @internal
 */
trait HandlesDatabases
{
    /**
     * Setup database requirements.
     *
     * @param  \Closure():void  $callback
     */
    protected function setUpDatabaseRequirements(Closure $callback): void
    {
        if (\is_null($app = $this->app)) {
            throw ApplicationNotAvailableException::make(__METHOD__);
        }

        $app['events']->listen(DatabaseRefreshed::class, function () {
            $this->defineDatabaseMigrationsAfterDatabaseRefreshed();
        });

        if (static::usesTestingConcern(WithLaravelMigrations::class)) {
            $this->setUpWithLaravelMigrations(); // @phpstan-ignore-line
        }

        TestingFeature::run(
            testCase: $this,
            attribute: function () use ($app) {
                $this->parseTestMethodAttributes($app, WithMigration::class);
            },
        );

        $attributeCallbacks = TestingFeature::run(
            testCase: $this,
            default: function () {
                $this->defineDatabaseMigrations();
                $this->beforeApplicationDestroyed(fn () => $this->destroyDatabaseMigrations());
            },
            annotation: fn () => $this->parseTestMethodAnnotations($app, 'define-db'),
            attribute: fn () => $this->parseTestMethodAttributes($app, DefineDatabase::class),
            pest: function ($default) {
                $this->defineDatabaseMigrationsUsingPest(); // @phpstan-ignore-line

                $this->beforeApplicationDestroyed(fn () => $this->destroyDatabaseMigrationsUsingPest()); // @phpstan-ignore-line

                value($default);
            },
        )->get('attribute');

        $callback();

        $attributeCallbacks->handle();

        TestingFeature::run(
            testCase: $this,
            default: fn () => $this->defineDatabaseSeeders(),
            pest: function ($default) {
                $this->defineDatabaseSeedersUsingPest(); // @phpstan-ignore-line

                value($default);
            }
        );
    }

    /**
     * Determine if using in-memory SQLite database connection
     *
     * @param  string|null  $connection
     * @return bool
     */
    protected function usesSqliteInMemoryDatabaseConnection(?string $connection = null): bool
    {
        if (\is_null($app = $this->app)) {
            throw ApplicationNotAvailableException::make(__METHOD__);
        }

        /** @var \Illuminate\Contracts\Config\Repository $config */
        $config = $app->make('config');

        /** @var string $connection */
        $connection = ! \is_null($connection) ? $connection : $config->get('database.default');

        /** @var array{driver: string, database: string}|null $database */
        $database = $config->get("database.connections.{$connection}");

        return ! \is_null($database) && $database['driver'] === 'sqlite' && $database['database'] == ':memory:';
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        // Define database migrations.
    }

    /**
     * Define database migrations after database refreshed.
     *
     * @return void
     */
    protected function defineDatabaseMigrationsAfterDatabaseRefreshed()
    {
        // Define database migrations after database refreshed.
    }

    /**
     * Destroy database migrations.
     *
     * @return void
     */
    protected function destroyDatabaseMigrations()
    {
        // Destroy database migrations.
    }

    /**
     * Define database seeders.
     *
     * @return void
     */
    protected function defineDatabaseSeeders()
    {
        // Define database seeders.
    }
}
