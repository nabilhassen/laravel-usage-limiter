<?php

namespace Nabilhassen\LaravelUsageLimiter\Tests\Feature;

use Nabilhassen\LaravelUsageLimiter\Models\Limit;
use Nabilhassen\LaravelUsageLimiter\Tests\TestCase;
use Workbench\App\Models\Location;

class LocationTest extends TestCase
{
    public function test_location_is_created(): void
    {
        Limit::findOrCreate([
            'name' => 'locations',
            'allowed_amount' => 3,
        ]);

        $this->post(route('locations.store'))->assertCreated();

        $location = Location::first();

        $location->setLimit('locations');

        $this->assertTrue($location->useLimit('locations', 1));

        $this->assertTrue($location->useLimit('locations', 2));

        $this->assertEquals(1, Location::count());
    }
}
