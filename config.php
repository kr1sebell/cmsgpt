<?php
$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_PATH', __DIR__);

define('PUBLIC_PATH', BASE_PATH . '/public');

define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');

define('LOG_PATH', BASE_PATH . '/logs');

if (!is_dir(UPLOADS_PATH)) {
    mkdir(UPLOADS_PATH, 0777, true);
}

if (!is_dir(LOG_PATH)) {
    mkdir(LOG_PATH, 0777, true);
}

function load_env()
{
    $envPath = BASE_PATH . '/.env';
    if (!file_exists($envPath)) {
        return array();
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = array();
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $env[trim($parts[0])] = trim($parts[1]);
        }
    }
    return $env;
}

$ENV = load_env();

$baseUrl = isset($ENV['BASE_URL']) ? rtrim($ENV['BASE_URL'], '/') : '';
if ($baseUrl === '' && !empty($_SERVER['HTTP_HOST'])) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'];
}

define('DB_HOST', isset($ENV['DB_HOST']) ? $ENV['DB_HOST'] : 'localhost');
define('DB_NAME', isset($ENV['DB_NAME']) ? $ENV['DB_NAME'] : 'project');
define('DB_USER', isset($ENV['DB_USER']) ? $ENV['DB_USER'] : 'root');
define('DB_PASS', isset($ENV['DB_PASS']) ? $ENV['DB_PASS'] : '');
define('OPENAI_API_KEY', isset($ENV['OPENAI_API_KEY']) ? $ENV['OPENAI_API_KEY'] : '');
define('BASE_URL', $baseUrl);
define('ADMIN_LOGIN', isset($ENV['ADMIN_LOGIN']) ? $ENV['ADMIN_LOGIN'] : 'admin');
define('ADMIN_PASSWORD', isset($ENV['ADMIN_PASSWORD']) ? $ENV['ADMIN_PASSWORD'] : 'admin');

date_default_timezone_set(isset($ENV['TIMEZONE']) ? $ENV['TIMEZONE'] : 'Europe/Moscow');

spl_autoload_register(function ($class) {
    $path = BASE_PATH . '/core/' . $class . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});
