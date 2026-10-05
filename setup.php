<?php

require 'connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $config = [
        'host' => trim($_POST['host']),
        'port' => trim($_POST['port']),
        'user' => trim($_POST['user']),
        'password' => $_POST['password'],
    ];

    $conn = @pg_connect(connect_string($config, 'postgres'));

    if (!$conn) {
        $error = 'Не удалось подключиться к PostgreSQL. Проверьте пароль, порт и что сервер запущен.';
    } else {
        $result = pg_query_params($conn, 'SELECT 1 FROM pg_database WHERE datname = $1', [$db_name]);
        if (pg_num_rows($result) == 0) {
            pg_query($conn, "CREATE DATABASE $db_name");
        }
        pg_close($conn);

        $db = pg_connect(connect_string($config, $db_name));
        $check = pg_query($db, "SELECT to_regclass('topics') AS name");
        if (pg_fetch_assoc($check)['name'] === null) {
            pg_query($db, file_get_contents(__DIR__ . '/db/2_tables.sql'));
            pg_query($db, file_get_contents(__DIR__ . '/db/3_data.sql'));
        }

        file_put_contents(__DIR__ . '/config.php', '<?php return ' . var_export($config, true) . ";\n");

        header('Location: index.php');
        exit;
    }
}

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
                <p class="header-text">Подключение к базе данных</p>
            </div>
        </div>
    </div>
</header>

<main class="container main">
    <form method="post" class="card setup">
        <p>Для работы приложения нужна база данных PostgreSQL. Введите данные для подключения, база stranica_dnya и таблицы создадутся автоматически.</p>

        <?php if ($error): ?>
            <p class="setup-error"><?= $error ?></p>
        <?php endif; ?>

        <label>Сервер
            <input type="text" name="host" value="<?= htmlspecialchars($config['host']) ?>" required>
        </label>
        <label>Порт
            <input type="text" name="port" value="<?= htmlspecialchars($config['port']) ?>" required>
        </label>
        <label>Пользователь
            <input type="text" name="user" value="<?= htmlspecialchars($config['user']) ?>" required>
        </label>
        <label>Пароль
            <input type="password" name="password">
        </label>

        <div class="buttons">
            <button type="submit" class="btn btn-main">Подключиться</button>
        </div>
    </form>
</main>

</body>
</html>
