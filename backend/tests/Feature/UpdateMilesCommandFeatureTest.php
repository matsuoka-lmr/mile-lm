<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Vehicle;
use App\Models\Device;
use App\Models\History;
use App\Services\Trackimo;
use Illuminate\Support\Carbon;
use Mockery;
use Log;

class UpdateMilesCommandFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set up MongoDB connection for testing
        config(['database.connections.mongodb' => [
            'driver'   => 'mongodb',
            'host'     => env('DB_HOST', '172.17.0.2'),
            'port'     => env('DB_PORT', 27017),
            'database' => env('DB_DATABASE', 'mamol'), // Use the actual database name
            'username' => env('DB_USERNAME', ''),
            'password' => env('DB_PASSWORD', ''),
            'options'  => [
                'database' => 'admin' // sets the authentication database in the connection string
            ],
        ]]);
        config(['database.default' => 'mongodb']);

        // Clear the test database before each test
        $this->app['db']->connection('mongodb')->getMongoDB()->drop();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testUpdateMilesCommandProcessesVehiclesAndUpdatesMileage()
    {
        // Create a test device and vehicle
        $device = Device::create([
            'status' => 'active',
            'name' => 'Test Device',
            'measure_interval' => 60,
        ]);
        Log::debug("Created Device with _id: " . (string)$device->_id);

        $vehicle = Vehicle::create([
            'device_id' => (string)$device->_id, // Explicitly cast to string
            'attach_at' => Carbon::now()->subDays(7),
            'oil_date' => Carbon::now()->subDays(5)->toDateString(),
            'tire_date' => Carbon::now()->subDays(5)->toDateString(),
            'battery_date' => Carbon::now()->subDays(5)->toDateString(),
            'oil_mileage' => 0,
            'tire_mileage' => 0,
            'battery_mileage' => 0,
        ]);
        Log::debug("Created Vehicle with _id: " . (string)$vehicle->_id . " and device_id: " . (string)$vehicle->device_id); 
        // Mock the Trackimo service
        $trackimoMock = Mockery::mock(Trackimo::class);
        $this->app->instance(Trackimo::class, $trackimoMock);

        // Define expected history data from Trackimo API
        $mockHistoryData = [
            ['lat' => 34.0522, 'lng' => -118.2437, 'time' => Carbon::now()->subDays(6)->timestamp],
            ['lat' => 34.0523, 'lng' => -118.2438, 'time' => Carbon::now()->subDays(5)->timestamp],
            ['lat' => 34.0524, 'lng' => -118.2439, 'time' => Carbon::now()->subDays(4)->timestamp],
        ];

        $trackimoMock->shouldReceive('getHistories')
            ->andReturn($mockHistoryData);

	// Run the artisan command and capture output
        Log::debug("Vehicles in DB before command: " . Vehicle::all()->count()); 
        $result = $this->artisan('batch:update', ['--env' => 'testing']);
        $result->assertExitCode(0); // Expect successful execution
        echo $result->output(); // Print the command output for debugging

        // Assertions
        // 1. Check if history records are created
        $this->assertEquals(count($mockHistoryData), History::count());

        // 2. Check if vehicle mileage is updated
        $updatedVehicle = Vehicle::find($vehicle->id);
        $this->assertGreaterThan(0, $updatedVehicle->oil_mileage);
        $this->assertGreaterThan(0, $updatedVehicle->tire_mileage);
        $this->assertGreaterThan(0, $updatedVehicle->battery_mileage);
    }
}
