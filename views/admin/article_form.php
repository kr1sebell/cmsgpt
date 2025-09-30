<div class="card">
    <h2><?= isset($article['id']) ? 'Редактирование статьи' : 'Новая статья'; ?></h2>
    <form method="post" action="/admin/?action=save_article" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= Helpers::e(Helpers::csrfToken()); ?>">
        <?php if (!empty($article['id'])): ?>
            <input type="hidden" name="id" value="<?= (int)$article['id']; ?>">
        <?php endif; ?>
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" class="form-control" value="<?= Helpers::e(isset($article['title']) ? $article['title'] : ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="meta_description">Meta Description</label>
            <textarea id="meta_description" name="meta_description" rows="2" class="form-control" required><?= Helpers::e(isset($article['meta_description']) ? $article['meta_description'] : ''); ?></textarea>
        </div>
        <div class="form-group">
            <label for="h1">H1</label>
            <input type="text" id="h1" name="h1" class="form-control" value="<?= Helpers::e(isset($article['h1']) ? $article['h1'] : ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="lead">Лид</label>
            <textarea id="lead" name="lead" rows="3" class="form-control" required><?= Helpers::e(isset($article['lead']) ? $article['lead'] : ''); ?></textarea>
        </div>
        <div class="form-group">
            <label for="body">Текст (HTML)</label>
            <textarea id="body" name="body" rows="12" class="form-control" required><?= isset($article['body']) ? Helpers::e($article['body']) : ''; ?></textarea>
        </div>
        <div class="form-group">
            <label for="image">Изображение</label>
            <?php if (!empty($article['image'])): ?>
                <div style="margin-bottom:10px;">
                    <img src="/public/uploads/<?= Helpers::e($article['image']); ?>" alt="" style="max-width:200px;">
                </div>
            <?php endif; ?>
            <input type="file" id="image" name="image" accept="image/*">
        </div>
        <div class="form-group">
            <label for="status">Статус</label>
            <select name="status" id="status">
                <option value="draft" <?= isset($article['status']) && $article['status'] === 'published' ? '' : 'selected'; ?>>Черновик</option>
                <option value="published" <?= isset($article['status']) && $article['status'] === 'published' ? 'selected' : ''; ?>>Опубликовано</option>
            </select>
        </div>
        <div>
            <button type="submit" class="btn">Сохранить</button>
            <button type="submit" name="publish" value="1" class="btn btn-secondary">Сохранить и опубликовать</button>
        </div>
    </form>
</div>
