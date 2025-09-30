<?php

class Auth
{
    public static function check()
    {
        return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
    }

    protected static function passwordHash($password)
    {
        return hash('sha256', $password);
    }

    public static function attempt($login, $password)
    {
        if ($login === ADMIN_LOGIN && $password === ADMIN_PASSWORD) {
            $_SESSION['admin_logged_in'] = true;
            return true;
        }

        try {
            $db = Database::getInstance()->getConnection();
            $sql = "SELECT * FROM users WHERE login = ?s LIMIT 1";
            $user = $db->getRow($sql, $login);
            if ($user && hash_equals($user['password_hash'], self::passwordHash($password))) {
                $_SESSION['admin_logged_in'] = true;
                return true;
            }
        } catch (Exception $e) {
            // таблица может отсутствовать, игнорируем ошибку
        }

        return false;
    }

    public static function logout()
    {
        unset($_SESSION['admin_logged_in']);
    }
}
