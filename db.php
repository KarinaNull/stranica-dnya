<?php

require 'connect.php';

$db = @pg_connect(connect_string($config, $db_name));
if (!$db) {
    header('Location: setup.php');
    exit;
}

$check = pg_query($db, "SELECT to_regclass('topics') AS name");
if (pg_fetch_assoc($check)['name'] === null) {
    header('Location: setup.php');
    exit;
}

function get_topics($db)
{
    $result = pg_query($db, 'SELECT code, name FROM topics ORDER BY id');
    $topics = [];
    while ($row = pg_fetch_assoc($result)) {
        $topics[$row['code']] = $row['name'];
    }
    return $topics;
}

function get_reader_id($db, $token)
{
    $result = pg_query_params($db, 'SELECT id FROM readers WHERE token = $1', [$token]);
    $row = pg_fetch_assoc($result);
    if ($row) {
        return $row['id'];
    }

    $result = pg_query_params($db, 'INSERT INTO readers (token) VALUES ($1) RETURNING id', [$token]);
    return pg_fetch_assoc($result)['id'];
}

function get_article($db, $id)
{
    $sql = 'SELECT a.id, t.code AS topic, a.title, a.subtitle, a.text, a.image, a.link, a.source
            FROM articles a
            JOIN topics t ON t.id = a.topic_id
            WHERE a.id = $1';
    $result = pg_query_params($db, $sql, [$id]);
    return pg_fetch_assoc($result);
}

function save_article($db, $article)
{
    $sql = 'INSERT INTO articles (topic_id, title, subtitle, text, image, link, source)
            SELECT id, $2, $3, $4, $5, $6, $7 FROM topics WHERE code = $1
            ON CONFLICT (link) DO UPDATE SET loaded_at = NOW()
            RETURNING id';
    $result = pg_query_params($db, $sql, [
        $article['topic'],
        $article['title'],
        $article['subtitle'],
        $article['text'],
        $article['image'],
        $article['link'],
        $article['source'],
    ]);
    return pg_fetch_assoc($result)['id'];
}

function mark_read($db, $reader_id, $article_id)
{
    $sql = 'INSERT INTO read_history (reader_id, article_id) VALUES ($1, $2)
            ON CONFLICT (reader_id, article_id) DO NOTHING';
    pg_query_params($db, $sql, [$reader_id, $article_id]);
}

function is_read($db, $reader_id, $article_id)
{
    $sql = 'SELECT 1 FROM read_history WHERE reader_id = $1 AND article_id = $2';
    $result = pg_query_params($db, $sql, [$reader_id, $article_id]);
    return pg_num_rows($result) > 0;
}

function get_history($db, $reader_id)
{
    $sql = 'SELECT a.title, a.link, t.name AS topic, r.read_at
            FROM read_history r
            JOIN articles a ON a.id = r.article_id
            JOIN topics t ON t.id = a.topic_id
            WHERE r.reader_id = $1
            ORDER BY r.read_at DESC
            LIMIT 20';
    $result = pg_query_params($db, $sql, [$reader_id]);
    return pg_fetch_all($result) ?: [];
}
