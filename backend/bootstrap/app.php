<?php

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();
//
//

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->withFacades();

$app->register(App\Providers\MongoDBServiceProvider::class);

$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Config Files
|--------------------------------------------------------------------------
|
| Now we will register the "app" configuration file. If the file exists in
| your configuration directory it will be loaded; otherwise, we'll load
| the default version. You may register other files below as needed.
|
*/

$app->configure('app');
$app->configure('cors');
$app->configure('services');
$app->configure('mail');

// エラーログを標準エラー出力に強制的に設定 (デバッグ用)
$app->configure('logging'); // logging設定ファイルをロード
$app->make('Psr\Log\LoggerInterface')->pushHandler(new Monolog\Handler\ErrorLogHandler());

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

$app->middleware([
    \Illuminate\Http\Middleware\HandleCors::class,
    App\Http\Middleware\UnescapeJsonMiddleware::class,
    //App\Http\Middleware\MaintenanceMiddleware::class,
]);

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

// $app->register(App\Providers\AppServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);

// mail service
$app->register(Illuminate\Mail\MailServiceProvider::class);
$app->alias('mail.manager', Illuminate\Mail\MailManager::class);
$app->alias('mail.manager', Illuminate\Contracts\Mail\Factory::class);
$app->alias('mailer', Illuminate\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\MailQueue::class);

$app->register(App\Providers\ExternalServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    $router->group(['prefix' => 'api', 'namespace' => 'API'], function() use($router){
        $router->get('status', function () {
            return response()->json('ok');
        });
        $router->post('login', 'AuthAPI@login');

        $router->group(['middleware' => 'auth:api'], function() use($router){
            $router->get('login', 'AuthAPI@init');
            $router->get('fake[/{as}]', 'AuthAPI@fake');
            $router->post('fakeuser', 'FakeUser@list');
            $router->post('password', 'AuthAPI@password');
            $api = function ($path, $API) use($router){
                $router->post("$path/", "$API@list");
                $router->get("/$path/{id:[a-z0-9]+}", "$API@get");
                $router->patch("/$path/{id:[a-z0-9]+}", "$API@update");
                $router->delete("/$path/{id:[a-z0-9]+}", "$API@delete");
                $router->put("$path/", "$API@create");
            };
            require __DIR__.'/../routes/api.php';
        });
    });
    require __DIR__.'/../routes/web.php';
});

return $app;
