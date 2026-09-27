<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Tests must never reach real services (Meta tracking runs on checkout, cart and product views),
        // and never carry the real access token from .env.
        config(['services.meta.pixel_id' => 'test-pixel', 'services.meta.access_token' => 'test-token']);
        Http::preventStrayRequests();
    }
}
