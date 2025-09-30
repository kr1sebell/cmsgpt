<?php
use SafeMySQL;

class Database
{
    protected static $instance;
    protected $db;

    public function __construct()
    {
        $options = array(
            'host' => DB_HOST,
            'user' => DB_USER,
            'pass' => DB_PASS,
            'db'   => DB_NAME,
            'charset' => 'utf8'
        );
        $this->db = new SafeMySQL($options);
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->db;
    }
}
