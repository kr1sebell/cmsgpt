<?php

class SettingService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function get($name, $default = null)
    {
        $sql = "SELECT value FROM settings WHERE name = ?s LIMIT 1";
        $value = $this->db->getOne($sql, $name);
        return $value !== null ? $value : $default;
    }

    public function set($name, $value)
    {
        $sql = "INSERT INTO settings SET name = ?s, value = ?s ON DUPLICATE KEY UPDATE value = VALUES(value)";
        $this->db->query($sql, $name, $value);
    }
}
