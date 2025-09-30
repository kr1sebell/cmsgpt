<?php
require_once __DIR__ . '/config.php';

$router = new Router();
$articleService = new ArticleService();
$settingService = new SettingService();

$router->add('GET', '/', function () use ($articleService, $settingService) {
    $articles = $articleService->allPublished();
    $siteName = $settingService->get('site_name', 'AI Publisher');
    echo Helpers::view('frontend/layout', array(
        'title' => $siteName,
        'description' => $settingService->get('site_description', 'Генератор статей на базе OpenAI.'),
        'content' => Helpers::view('frontend/index', array('articles' => $articles)),
        'canonical' => BASE_URL . '/',
        'site_name' => $siteName
    ));
});

$router->add('GET', '/article/{slug}', function ($params) use ($articleService, $settingService) {
    $article = $articleService->findBySlug($params['slug']);
    if (!$article) {
        header('HTTP/1.0 404 Not Found');
        echo 'Статья не найдена';
        return;
    }
    $siteName = $settingService->get('site_name', 'AI Publisher');
    $canonical = BASE_URL . '/article/' . $article['slug'];
    echo Helpers::view('frontend/layout', array(
        'title' => $article['title'],
        'description' => $article['meta_description'],
        'content' => Helpers::view('frontend/article', array('article' => $article, 'site_name' => $siteName)),
        'canonical' => $canonical,
        'site_name' => $siteName
    ));
});

$router->add('GET', '/sitemap.xml', function () use ($articleService) {
    header('Content-Type: application/xml; charset=utf-8');
    $articles = $articleService->allPublished();
    echo Helpers::view('frontend/sitemap', array('articles' => $articles));
});

$router->add('GET', '/robots.txt', function () {
    header('Content-Type: text/plain; charset=utf-8');
    echo "User-agent: *\n";
    echo "Allow: /\n";
    echo "Sitemap: " . BASE_URL . "/sitemap.xml\n";
});

$router->dispatch();
