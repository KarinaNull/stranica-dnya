-- 1. История прочитанного одного читателя, сначала новые
SELECT r.read_at, t.name AS topic, a.title, a.link
FROM read_history r
JOIN articles a ON a.id = r.article_id
JOIN topics t ON t.id = a.topic_id
WHERE r.reader_id = 1
ORDER BY r.read_at DESC;

-- 2. Сколько статей прочитано по каждой теме (темы без прочтений тоже показываются)
SELECT t.name AS topic, COUNT(r.id) AS reads_count
FROM topics t
LEFT JOIN articles a ON a.topic_id = t.id
LEFT JOIN read_history r ON r.article_id = a.id
GROUP BY t.id, t.name
ORDER BY reads_count DESC;

-- 3. Самые популярные статьи: сколько читателей отметили статью прочитанной
SELECT a.title, t.name AS topic, COUNT(r.reader_id) AS readers_count
FROM articles a
JOIN topics t ON t.id = a.topic_id
JOIN read_history r ON r.article_id = a.id
GROUP BY a.id, a.title, t.name
ORDER BY readers_count DESC
LIMIT 5;

-- 4. Статистика по читателям: сколько статей прочитал и когда был последний раз
SELECT rd.id, rd.created_at, COUNT(r.id) AS articles_read, MAX(r.read_at) AS last_read
FROM readers rd
LEFT JOIN read_history r ON r.reader_id = rd.id
GROUP BY rd.id, rd.created_at
ORDER BY articles_read DESC;

-- 5. Статьи, которые загружались, но никто их не прочитал
SELECT a.title, t.name AS topic, a.loaded_at
FROM articles a
JOIN topics t ON t.id = a.topic_id
WHERE NOT EXISTS (
    SELECT 1 FROM read_history r WHERE r.article_id = a.id
)
ORDER BY a.loaded_at DESC;
