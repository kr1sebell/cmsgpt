<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= Helpers::e($title); ?> — Админ-панель</title>
    <link rel="stylesheet" href="/public/css/admin.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<header class="admin-header">
    <div class="container">
        <h1>Админ-панель</h1>
        <nav>
            <a href="/admin/?action=articles">Статьи</a>
            <a href="/admin/?action=generator">Генерация</a>
            <a href="/admin/?action=topics">Темы</a>
            <a href="/admin/?action=settings">Настройки</a>
            <a href="/admin/?action=logout">Выход</a>
        </nav>
    </div>
</header>
<main class="container">
    <?= $content; ?>
</main>
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
<script src="/public/js/admin-editor.js"></script>
</body>
</html>
