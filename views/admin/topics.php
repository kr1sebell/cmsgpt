<div class="card">
    <h2>Темы</h2>
    <form method="post" action="/admin/?action=add_topic" style="margin-bottom:20px;">
        <input type="hidden" name="csrf_token" value="<?= Helpers::e(Helpers::csrfToken()); ?>">
        <div class="form-group">
            <label for="keyword">Ключевое слово</label>
            <input type="text" id="keyword" name="keyword" class="form-control" required>
        </div>
        <button type="submit" class="btn">Добавить тему</button>
    </form>
    <table class="table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Ключ</th>
            <th>Статус</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($topics as $topic): ?>
            <tr>
                <td><?= (int)$topic['id']; ?></td>
                <td><?= Helpers::e($topic['keyword']); ?></td>
                <td><span class="status-badge <?= Helpers::e($topic['status']); ?>"><?= Helpers::e($topic['status']); ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
