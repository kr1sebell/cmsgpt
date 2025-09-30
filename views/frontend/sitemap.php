<?= '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc><?= Helpers::e(BASE_URL . '/'); ?></loc>
        <changefreq>daily</changefreq>
    </url>
    <?php foreach ($articles as $article): ?>
        <url>
            <loc><?= Helpers::e(BASE_URL . '/article/' . $article['slug']); ?></loc>
            <lastmod><?= date('c', strtotime($article['updated_at'])); ?></lastmod>
        </url>
    <?php endforeach; ?>
</urlset>
