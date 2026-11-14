<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $defaultConn = config('database.default');
        $databaseName = (string) config("database.connections.{$defaultConn}.database");

        if ($defaultConn === 'mysql' && in_array($databaseName, ['candycutz_db', 'candycutz'], true)) {
            throw new \RuntimeException(
                "CRITICAL SAFETY SHIELD: Tests attempted to execute against development database '{$databaseName}'! " .
                "Tests must run on SQLite in-memory (:memory:) or a dedicated test database (e.g. candycutz_test) " .
                "to prevent wiping persistent development/production data."
            );
        }
    }

    protected function beforeRefreshingDatabase()
    {
        $defaultConn = config('database.default');
        $databaseName = (string) config("database.connections.{$defaultConn}.database");

        if ($defaultConn === 'mysql' && in_array($databaseName, ['candycutz_db', 'candycutz'], true)) {
            throw new \RuntimeException(
                "CRITICAL SAFETY SHIELD: RefreshDatabase attempted to run against development database '{$databaseName}'! Aborted to protect persistent data."
            );
        }
    }
}
