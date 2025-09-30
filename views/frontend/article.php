<article class="article">
    <script type="application/ld+json">
        <?= json_encode(array(
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article['title'],
            'description' => $article['meta_description'],
            'datePublished' => $article['created_at'],
            'dateModified' => $article['updated_at'],
            'image' => !empty($article['image']) ? BASE_URL . '/public/uploads/' . $article['image'] : null,
            'author' => array(
                '@type' => 'Organization',
                'name' => $site_name
            )
        ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
    </script>
    <header class="article__header">
        <h1><?= Helpers::e($article['h1']); ?></h1>
        <p class="article__lead"><?= Helpers::e($article['lead']); ?></p>
    </header>
    <?php if (!empty($article['image'])): ?>
        <figure class="article__image">
            <img src="/public/uploads/<?= Helpers::e($article['image']); ?>" alt="<?= Helpers::e($article['title']); ?>">
        </figure>
    <?php endif; ?>
    <div class="article__body">
        <?= $article['body']; ?>
    </div>
</article>
