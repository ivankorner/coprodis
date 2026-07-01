<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('VIEWS_PATH', BASE_PATH . '/views');
define('UPLOADS_PATH', BASE_PATH . '/uploads');
define('STORAGE_PATH', BASE_PATH . '/storage');

require_once BASE_PATH . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

require_once CONFIG_PATH . '/app.php';
require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/session.php';

use App\Core\App;
use App\Core\Router;
use App\Core\Request;
use App\Core\Database;
use App\Core\Session;

Session::start();

$db = Database::getInstance();

$app = new App();
$app->run();
