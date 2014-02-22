<?php
// Configure Log4php
require('log4php/Logger.php');
Logger::configure('logging.xml');

// Logger for current script
$log = Logger::getLogger("app");

require __DIR__ . '/lib/Utils.php';
require __DIR__ . '/lib/Application.php';

// Parsing commandline options
$options = getopt("s:");
Application::set('app_dir', __DIR__);
// Load basic settings
Application::load_yml(__DIR__ . "/config/settings.yml");
// Load custom settings
if (array_key_exists("s", $options)) Application::load_yml($options['s']);
// Configure routes
Application::load_yml(__DIR__ . "/config/routes.yml");

// Configure objects
Application::set_objects(array(
    "memcache" => function(){
        $settings = Application::get('memcache');
        Logger::getLogger("app")->debug("Instate memcache ".posix_getpid());
        $mc = new Memcache();
        $mc->addServer($settings['host'], $settings['port']);
        return $mc;
    },
    "mysql" => function() {
        $settings = Application::get('mysql');
        Logger::getLogger("app")->debug("Instate mysql".posix_getpid());
        $db = new mysqli(
            $settings['host'], 
            $settings['username'],
            $settings['password'],
            $settings['database'], 
            $settings['port']
        );
        $db->set_charset($settings['charset']);
        return $db;
    },
));

$settings = Application::get('server');
$serv = new swoole_server($settings['host'], $settings['port']);
$serv->set($settings['settings']);

function onStart($serv)
{
    $log = Logger::getLogger('app');
    $log->info("Server launched,"
        ." master #{$serv->master_pid},"
        ." manager #{$serv->manager_pid}");

    $serv->addtimer(1000);
    $serv->addtimer(2000);
    $serv->addtimer(3000);
}

function onReceive($serv, $fd, $from_id, $input)
{

    $log = Logger::getLogger('app');
    $traffic_log = Logger::getLogger('traffic');
    $output = null;
    try {
        // Verify
        MessageHelper::verify($input);
        // Decrypt
        $input_decrypt = MessageHelper::decrypt($input);
        $traffic_log->info(' UP ' . $input_decrypt);
        // Unpack
        $input_unpacked = MessageHelper::unpack($input);
        $cmd = intval($input_unpacked['cmd']);
        // Route
        $routes = Application::get('routes');
        if (array_key_exists($cmd, $routes))
        {
            $func = $routes[$cmd];
            $output = $func($fd, $from_id, $data);
        }
    } catch (Exception $e) {
        $log->error($e);
        $output = MessageHelper::MSG_ILLEGAL;
    }
    $traffic_log->info(' DOWN ' . $output);
    $output_pack = MessageHelper::pack($output);
    $output_encrypt = MessageHelper::encrypt($output);
    $serv->send($fd, $output_encrypt);
    $serv->close($fd);    
}

function onTimer($serv, $interval)
{
    $log = Logger::getLogger('app');
    switch ($interval) {
        case 1000:
            break;
        case 2000:
            break;
        case 3000:
            break;
        default:
            break;
    }
}

function onWorkerStart($serv, $worker_id){
    $log = Logger::getLogger('app');
    $log->info("Worker launched. #".posix_getpid());
    Utils::require_all(__DIR__, 'helpers');
    Utils::require_all(__DIR__, 'controllers');
    Utils::require_all(__DIR__, 'models');
}

function onConnect ($serv, $fd){
    // Logger::getLogger("server")->debug("Client:Connect.");
}

$serv->on('start', 'onStart');
$serv->on('receive', 'onReceive');
$serv->on('timer', 'onTimer');
$serv->on('WorkerStart', 'onWorkerStart');
$serv->on('connect', 'onConnect');

$serv->on('close', function ($serv, $fd) {
    // Logger::getLogger("server")->debug("Client: Close.");
});

$log->info('Launching server at '.$settings['host'].':'.$settings['port']);
$serv->start();
?>