<?php
$api('company', 'CompanyAPI');
$api('user', 'UserAPI');
$api('device', 'DeviceAPI');
$api('vehicle', 'VehicleAPI');
$api('shop', 'ShopAPI');
$api('customer', 'CustomerAPI');
$router->post("device/unused", "DeviceAPI@unused");
$router->post("vehicle/{id:[a-z0-9]+}/reset", "VehicleAPI@reset");
$router->post("address", "TrackimoAPI@address");
