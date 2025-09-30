<?php

class ArticleService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function allPublished()
    {
        $sql = "SELECT * FROM articles WHERE status = 'published' ORDER BY created_at DESC";
        return $this->db->getAll($sql);
    }

    public function findBySlug($slug)
    {
        $sql = "SELECT * FROM articles WHERE slug = ?s AND status = 'published' LIMIT 1";
        return $this->db->getRow($sql, $slug);
    }

    public function find($id)
    {
        $sql = "SELECT * FROM articles WHERE id = ?i";
        return $this->db->getRow($sql, $id);
    }

    public function listAll($status = null)
    {
        $sql = "SELECT * FROM articles";
        $params = array();
        if ($status) {
            $sql .= " WHERE status = ?s";
            $params[] = $status;
        }
        $sql .= " ORDER BY created_at DESC";
        return call_user_func_array(array($this->db, 'getAll'), array_merge(array($sql), $params));
    }

    public function save($data)
    {
        $now = date('Y-m-d H:i:s');
        if (isset($data['id']) && $data['id']) {
            $data['updated_at'] = $now;
            $id = $data['id'];
            unset($data['id']);
            $sql = "UPDATE articles SET ?u WHERE id = ?i";
            $this->db->query($sql, $data, $id);
            return $id;
        }
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        $sql = "INSERT INTO articles SET ?u";
        $this->db->query($sql, $data);
        return $this->db->insertId();
    }

    public function publish($id)
    {
        $sql = "UPDATE articles SET status = 'published', updated_at = NOW() WHERE id = ?i";
        $this->db->query($sql, $id);
    }

    public function slugExists($slug, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) FROM articles WHERE slug = ?s";
        $params = array($slug);
        if ($excludeId) {
            $sql .= " AND id != ?i";
            $params[] = $excludeId;
        }
        return (int)call_user_func_array(array($this->db, 'getOne'), array_merge(array($sql), $params));
    }

    public function generateSlug($title, $excludeId = null)
    {
        $baseSlug = Helpers::slugify($title);
        if ($baseSlug === '') {
            $baseSlug = 'article-' . time();
        }
        $slug = $baseSlug;
        $counter = 1;
        while ($this->slugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        return $slug;
    }
}
