<?php

require 'api.php';
require 'db.php';

$topics = get_topics($db);

if (empty($_COOKIE['reader'])) {
    $token = bin2hex(random_bytes(16));
    setcookie('reader', $token, time() + 60 * 60 * 24 * 365, '/');
} else {
    $token = $_COOKIE['reader'];
}
$reader_id = get_reader_id($db, $token);

$topic = $_COOKIE['topic'] ?? 'wiki';
if (!isset($topics[$topic])) {
    $topic = 'wiki';
}

if (isset($_GET['topic']) && isset($topics[$_GET['topic']])) {
    $topic = $_GET['topic'];
    setcookie('topic', $topic, time() + 60 * 60 * 24 * 365, '/');
}

$article = null;
$article_id = (int)($_COOKIE['article_' . $topic] ?? 0);
if ($article_id > 0) {
    $article = get_article($db, $article_id);
}

$next_time = (int)($_COOKIE['next_time'] ?? 0);
$error = false;

$action = $_POST['action'] ?? '';

if ($action == 'retry') {
    $article = null;
}

if ($action == 'read' && $article) {
    mark_read($db, $reader_id, $article['id']);
}

if ($next_time <= time()) {
    foreach ($topics as $key => $name) {
        setcookie('article_' . $key, '', time() - 3600, '/');
    }
    $article = null;
    $next_time = time() + 60 * 60 * 24;
    setcookie('next_time', $next_time, time() + 60 * 60 * 24 * 365, '/');
}

if (!$article || $article['topic'] != $topic) {
    $new_article = load_article($topic);
    if ($new_article) {
        $id = save_article($db, $new_article);
        setcookie('article_' . $topic, $id, time() + 60 * 60 * 24 * 365, '/');
        $article = get_article($db, $id);
    } else {
        $error = true;
    }
}

if ($action != '' || isset($_GET['topic'])) {
    if (!$error) {
        header('Location: index.php');
        exit;
    }
}

$is_read = false;
if ($article) {
    $is_read = is_read($db, $reader_id, $article['id']);
}

$history = get_history($db, $reader_id);

$left = $next_time - time();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Страница дня</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="header">
    <div class="container">
        <div class="header-top">
            <div>
                <h1>Страница дня</h1>
                <p class="header-text">Каждый день одна новая страница по выбранной теме</p>
            </div>
            <div class="timer">
                <span class="timer-label">Новая страница через</span>
                <span class="timer-value" id="timer" data-left="<?= $left ?>">
                    <?= sprintf('%02d:%02d:%02d', floor($left / 3600), floor($left % 3600 / 60), $left % 60) ?>
                </span>
            </div>
        </div>

        <nav class="topics">
            <?php foreach ($topics as $key => $name): ?>
                <a href="index.php?topic=<?= $key ?>" class="<?= $key == $topic ? 'active' : '' ?>"><?= $name ?></a>
            <?php endforeach; ?>
        </nav>
    </div>
</header>

<main class="container main">

    <?php if ($error): ?>
        <div class="card error">
            <p>Не получилось загрузить статью. Возможно, сервис сейчас недоступен.</p>
            <form method="post">
                <button type="submit" name="action" value="retry" class="btn">Попробовать снова</button>
            </form>
        </div>
    <?php else: ?>
        <article class="card">
            <div class="card-head">
                <div class="card-meta">
                    <span><?= $topics[$article['topic']] ?></span>
                    <span><?= htmlspecialchars($article['source']) ?></span>
                </div>
                <h2 class="<?= $article['topic'] == 'kanji' ? 'kanji' : '' ?>"><?= htmlspecialchars($article['title']) ?></h2>
                <?php if ($article['subtitle']): ?>
                    <p class="subtitle"><?= htmlspecialchars($article['subtitle']) ?></p>
                <?php endif; ?>
            </div>

            <?php if ($article['image']): ?>
                <img src="<?= htmlspecialchars($article['image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>" class="card-img">
            <?php endif; ?>

            <div class="card-text">
                <?= nl2br(htmlspecialchars($article['text'])) ?>
            </div>

            <div class="card-footer">
                <a href="<?= htmlspecialchars($article['link']) ?>" target="_blank">Читать полностью</a>
                <?php if ($is_read): ?>
                    <span class="read-mark">Прочитано</span>
                <?php endif; ?>
            </div>
        </article>

        <form method="post" class="buttons">
            <button type="submit" name="action" value="read" class="btn btn-main" <?= $is_read ? 'disabled' : '' ?>>
                <?= $is_read ? 'Уже прочитано' : 'Прочитано' ?>
            </button>
        </form>
    <?php endif; ?>

    <details class="history" open>
        <summary>История прочитанного</summary>
        <?php if (count($history) == 0): ?>
            <p class="history-empty">Здесь пока пусто. Нажмите «Прочитано», и статья появится в истории.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($history as $item): ?>
                    <li>
                        <span class="history-date"><?= date('d.m.Y', strtotime($item['read_at'])) ?></span>
                        <a href="<?= htmlspecialchars($item['link']) ?>" target="_blank"><?= htmlspecialchars($item['title']) ?></a>
                        <span class="history-topic"><?= $item['topic'] ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </details>

</main>

<script src="script.js"></script>
</body>
</html>
