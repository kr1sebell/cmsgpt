<div class="card">
    <h2>Настройки сайта</h2>
    <?php if (!empty($message)): ?>
        <div class="alert"><?= Helpers::e($message); ?></div>
    <?php endif; ?>
    <form method="post" action="/admin/?action=save_settings">
        <input type="hidden" name="csrf_token" value="<?= Helpers::e(Helpers::csrfToken()); ?>">
        <div class="form-group">
            <label for="site_name">Название сайта</label>
            <input type="text" id="site_name" name="site_name" class="form-control" value="<?= Helpers::e($settings['site_name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="site_description">Описание (meta)</label>
            <textarea id="site_description" name="site_description" class="form-control" rows="3" required><?= Helpers::e($settings['site_description']); ?></textarea>
        </div>
        <button type="submit" class="btn">Сохранить</button>
    </form>
</div>
