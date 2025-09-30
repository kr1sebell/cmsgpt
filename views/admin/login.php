<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход в админку</title>
    <link rel="stylesheet" href="/public/css/admin.css">
</head>
<body>
<div class="container">
    <div class="card" style="max-width: 400px; margin: 60px auto;">
        <h2>Авторизация</h2>
        <?php if (!empty($error)): ?>
            <div class="alert"><?= Helpers::e($error); ?></div>
        <?php endif; ?>
        <form method="post" action="/admin/?action=login">
            <div class="form-group">
                <label for="login">Логин</label>
                <input type="text" id="login" name="login" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn">Войти</button>
        </form>
    </div>
</div>
</body>
</html>
