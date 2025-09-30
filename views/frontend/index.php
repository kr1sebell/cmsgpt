<section class="articles">
    <?php if (empty($articles)): ?>
        <p>Статей пока нет. Загляните позже!</p>
    <?php else: ?>
        <?php foreach ($articles as $article): ?>
            <article class="article-card">
                <a href="/article/<?= Helpers::e($article['slug']); ?>" class="article-card__image">
                    <?php if (!empty($article['image'])): ?>
                        <img src="/public/uploads/<?= Helpers::e($article['image']); ?>" alt="<?= Helpers::e($article['title']); ?>">
                    <?php else: ?>
                        <img src="/public/img/placeholder.svg" alt="<?= Helpers::e($article['title']); ?>">
                    <?php endif; ?>
                </a>
                <div class="article-card__body">
                    <h2><a href="/article/<?= Helpers::e($article['slug']); ?>"><?= Helpers::e($article['title']); ?></a></h2>
                    <p><?= Helpers::e($article['lead']); ?></p>
                    <a class="btn" href="/article/<?= Helpers::e($article['slug']); ?>">Читать далее</a>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
