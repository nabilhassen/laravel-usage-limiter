<?php

namespace Workbench\App\Http\Controllers;

use Illuminate\Support\Str;
use Workbench\App\Models\Location;

class LocationController
{
    public function store()
    {
        Location::create(['name' => Str::random(10)]);

        return response('created', 201);
    }

    public function destroy(Location $location)
    {
        $location->delete();

        return response('deleted', 201);
    }
}
