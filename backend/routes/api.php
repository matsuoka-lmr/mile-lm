<?php
$api('company', 'CompanyAPI');
$api('user', 'UserAPI');
$api('device', 'DeviceAPI');
$router->get("vehicle/create", "VehicleAPI@showCreateForm");
$api('vehicle', 'VehicleAPI');
$api('shop', 'ShopAPI');
$router->post("shop", "ShopAPI@list");
$api('customer', 'CustomerAPI');
$router->post("device/unused", "DeviceAPI@unused");

$router->post("vehicle/{id:[a-z0-9]+}/reset", "VehicleAPI@reset");
$router->get("vehicle/create", "VehicleAPI@showCreateForm");
$router->post("address", "TrackimoAPI@address");

