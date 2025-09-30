<?php

class Helpers
{
    public static function view($template, $data = array())
    {
        $viewPath = BASE_PATH . '/views/' . $template . '.php';
        if (!file_exists($viewPath)) {
            throw new Exception('Шаблон не найден: ' . $template);
        }
        extract($data);
        ob_start();
        include $viewPath;
        return ob_get_clean();
    }

    public static function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }

    public static function csrfToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = md5(uniqid(mt_rand(), true));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrf($token)
    {
        return isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token;
    }

    public static function slugify($string)
    {
        $string = mb_strtolower($string, 'UTF-8');
        $string = preg_replace('~[^\pL0-9]+~u', '-', $string);
        $string = trim($string, '-');
        return $string;
    }

    public static function e($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}
