<div class="card">
    <h2>Статьи</h2>
    <div style="margin-bottom: 15px;">
        <a class="btn" href="/admin/?action=edit_article">Добавить статью</a>
        <form method="get" action="/admin/" style="display:inline-block;margin-left:20px;">
            <input type="hidden" name="action" value="articles">
            <select name="status" onchange="this.form.submit();">
                <option value="">Все статусы</option>
                <option value="draft" <?= $status === 'draft' ? 'selected' : ''; ?>>Черновики</option>
                <option value="published" <?= $status === 'published' ? 'selected' : ''; ?>>Опубликованные</option>
            </select>
        </form>
    </div>
    <table class="table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Заголовок</th>
            <th>Статус</th>
            <th>Обновлено</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($articles)): ?>
            <tr>
                <td colspan="5">Статьи не найдены.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($articles as $article): ?>
                <tr>
                    <td><?= (int)$article['id']; ?></td>
                    <td><?= Helpers::e($article['title']); ?></td>
                    <td><span class="status-badge <?= Helpers::e($article['status']); ?>"><?= Helpers::e($article['status']); ?></span></td>
                    <td><?= Helpers::e($article['updated_at']); ?></td>
                    <td><a class="btn btn-secondary" href="/admin/?action=edit_article&id=<?= (int)$article['id']; ?>">Редактировать</a></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
