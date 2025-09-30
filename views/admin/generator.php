<div class="card">
    <h2>Генерация статьи</h2>
    <?php if (!empty($message)): ?>
        <div class="alert"><?= Helpers::e($message); ?></div>
    <?php endif; ?>
    <?php if (empty($topic)): ?>
        <p>Свободных тем нет. Добавьте новую тему в разделе «Темы».</p>
    <?php else: ?>
        <p>Текущая тема: <strong><?= Helpers::e($topic['keyword']); ?></strong></p>
        <form method="post" action="/admin/?action=generate_article">
            <input type="hidden" name="csrf_token" value="<?= Helpers::e(Helpers::csrfToken()); ?>">
            <input type="hidden" name="topic_id" value="<?= (int)$topic['id']; ?>">
            <button type="submit" class="btn">Сгенерировать статью</button>
        </form>
    <?php endif; ?>
</div>
