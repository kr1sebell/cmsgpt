<?php

class TopicService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function all($status = null)
    {
        $sql = "SELECT * FROM topics";
        $params = array();
        if ($status) {
            $sql .= " WHERE status = ?s";
            $params[] = $status;
        }
        $sql .= " ORDER BY id DESC";
        return call_user_func_array(array($this->db, 'getAll'), array_merge(array($sql), $params));
    }

    public function add($keyword)
    {
        $sql = "INSERT INTO topics SET keyword = ?s, status = 'queue', created_at = NOW()";
        $this->db->query($sql, $keyword);
    }

    public function nextInQueue()
    {
        $sql = "SELECT * FROM topics WHERE status = 'queue' ORDER BY id ASC LIMIT 1";
        return $this->db->getRow($sql);
    }

    public function markDone($id)
    {
        $sql = "UPDATE topics SET status = 'done' WHERE id = ?i";
        $this->db->query($sql, $id);
    }
}
