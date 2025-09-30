<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= Helpers::e($title); ?></title>
    <meta name="description" content="<?= Helpers::e($description); ?>">
    <link rel="canonical" href="<?= Helpers::e($canonical); ?>">
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?= Helpers::e($title); ?>">
    <meta property="og:description" content="<?= Helpers::e($description); ?>">
    <meta property="og:url" content="<?= Helpers::e($canonical); ?>">
    <meta property="og:site_name" content="<?= Helpers::e($site_name); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container">
        <h1><a href="/"><?= Helpers::e($site_name); ?></a></h1>
        <p class="tagline">Генератор статей на базе OpenAI</p>
    </div>
</header>
<main class="container">
    <?= $content; ?>
</main>
<footer class="site-footer">
    <div class="container">
        <p>&copy; <?= date('Y'); ?> <?= Helpers::e($site_name); ?>. Все права защищены.</p>
    </div>
</footer>
</body>
</html>
