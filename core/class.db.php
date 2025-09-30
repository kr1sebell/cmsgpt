<?php
/**
 * Минимальная реализация SafeMySQL для совместимости с проектом.
 * Основано на оригинальной библиотеке colshrapnel/safemysql.
 */
class SafeMySQLException extends Exception
{
}

class SafeMySQL
{
    /** @var mysqli */
    protected $mysqli;

    /** @var array */
    protected $stats = array();

    /** @var string */
    protected $emode = 'exception';

    /** @var string */
    protected $exname = 'SafeMySQLException';

    /**
     * Параметры подключения по умолчанию
     * @var array
     */
    protected $defaults = array(
        'host'    => 'localhost',
        'user'    => 'root',
        'pass'    => '',
        'db'      => '',
        'port'    => null,
        'socket'  => null,
        'charset' => 'utf8'
    );

    public function __construct($options = array())
    {
        if (!class_exists('mysqli')) {
            throw new SafeMySQLException('Расширение mysqli не найдено.');
        }

        $opts = array_merge($this->defaults, $options);

        $this->mysqli = @new mysqli(
            $opts['host'],
            $opts['user'],
            $opts['pass'],
            $opts['db'],
            $opts['port'],
            $opts['socket']
        );

        if ($this->mysqli->connect_errno) {
            $this->error($this->mysqli->connect_error);
        }

        if (!empty($opts['charset'])) {
            $this->mysqli->set_charset($opts['charset']);
        }
    }

    public function query($query)
    {
        $args = func_get_args();
        $query = $this->prepareQuery($query, array_slice($args, 1));
        return $this->rawQuery($query);
    }

    public function getAll($query)
    {
        $res = call_user_func_array(array($this, 'query'), func_get_args());
        $data = array();
        if ($res instanceof mysqli_result) {
            while ($row = $res->fetch_assoc()) {
                $data[] = $row;
            }
            $res->free();
        }
        return $data;
    }

    public function getRow($query)
    {
        $res = call_user_func_array(array($this, 'query'), func_get_args());
        if ($res instanceof mysqli_result) {
            $row = $res->fetch_assoc();
            $res->free();
            return $row ?: null;
        }
        return null;
    }

    public function getOne($query)
    {
        $res = call_user_func_array(array($this, 'query'), func_get_args());
        if ($res instanceof mysqli_result) {
            $row = $res->fetch_row();
            $res->free();
            return $row ? $row[0] : null;
        }
        return null;
    }

    public function insertId()
    {
        return $this->mysqli->insert_id;
    }

    public function affectedRows()
    {
        return $this->mysqli->affected_rows;
    }

    public function escape($value)
    {
        if ($value === null) {
            return 'NULL';
        }
        return "'" . $this->mysqli->real_escape_string($value) . "'";
    }

    protected function rawQuery($query)
    {
        $start = microtime(true);
        $result = $this->mysqli->query($query);
        $this->stats[] = array(
            'query' => $query,
            'start' => $start,
            'duration' => microtime(true) - $start
        );
        if ($result === false) {
            $this->error($this->mysqli->error . " in query: " . $query);
        }
        return $result;
    }

    protected function prepareQuery($query, array $args)
    {
        if (empty($args)) {
            return $query;
        }

        $offset = 0;
        foreach ($args as $value) {
            $qpos = strpos($query, '?', $offset);
            if ($qpos === false) {
                break;
            }
            $placeholder = substr($query, $qpos, 2);
            switch ($placeholder) {
                case '?s':
                    $replacement = $this->escapeString($value);
                    $length = 2;
                    break;
                case '?i':
                    $replacement = $this->escapeInt($value);
                    $length = 2;
                    break;
                case '?u':
                    $replacement = $this->escapeSet($value);
                    $length = 2;
                    break;
                default:
                    $replacement = $this->escapeString($value);
                    $length = 2;
                    break;
            }
            $query = substr_replace($query, $replacement, $qpos, $length);
            $offset = $qpos + strlen($replacement);
        }

        return $query;
    }

    protected function escapeString($value)
    {
        if ($value === null) {
            return 'NULL';
        }
        return "'" . $this->mysqli->real_escape_string($value) . "'";
    }

    protected function escapeInt($value)
    {
        if ($value === null) {
            return 'NULL';
        }
        if ($value === true) {
            return '1';
        }
        if ($value === false) {
            return '0';
        }
        if ($value === '') {
            return '0';
        }
        return (string)(int)$value;
    }

    protected function escapeSet($value)
    {
        if (!is_array($value)) {
            throw new SafeMySQLException('Плейсхолдер ?u требует ассоциативный массив.');
        }
        $parts = array();
        foreach ($value as $key => $val) {
            $parts[] = $this->escapeIdentifier($key) . ' = ' . $this->escapeString($val);
        }
        return implode(', ', $parts);
    }

    protected function escapeIdentifier($field)
    {
        if (!preg_match('~^[a-zA-Z0-9_]+$~', $field)) {
            throw new SafeMySQLException('Недопустимое имя поля: ' . $field);
        }
        return '`' . $field . '`';
    }

    protected function error($err)
    {
        if ($this->emode == 'error') {
            trigger_error($err, E_USER_ERROR);
        } else {
            throw new $this->exname($err);
        }
    }
}
