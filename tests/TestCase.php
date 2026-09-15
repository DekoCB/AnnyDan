<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // spatie/laravel-permission caches role/permission lookups; with
        // RefreshDatabase reseeding roles on every test, that cache goes
        // stale between tests unless it's forgotten here.
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
