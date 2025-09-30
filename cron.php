<?php
require_once __DIR__ . '/config.php';

$logFile = LOG_PATH . '/cron.log';

function log_message($message)
{
    global $logFile;
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    file_put_contents($logFile, $line, FILE_APPEND);
}

$topicService = new TopicService();
$articleService = new ArticleService();
$openaiOptions = array(
    'base_url' => OPENAI_BASE_URL,
    'relay_token' => OPENAI_RELAY_TOKEN,
    'proxy' => OPENAI_PROXY,
    'proxy_auth' => OPENAI_PROXY_AUTH,
    'proxy_type' => OPENAI_PROXY_TYPE
);
$openaiClient = new OpenAIClient(OPENAI_API_KEY, $openaiOptions);
$generator = new ArticleGenerator($openaiClient);

try {
    $topic = $topicService->nextInQueue();
    if (!$topic) {
        log_message('Темы в очереди отсутствуют.');
        exit;
    }

    $data = $generator->createFromTopic($topic);
    $articleData = array(
        'title' => $data['title'],
        'meta_description' => isset($data['meta_description']) ? $data['meta_description'] : '',
        'h1' => isset($data['h1']) ? $data['h1'] : $data['title'],
        'lead' => isset($data['lead']) ? $data['lead'] : '',
        'body' => isset($data['body']) ? $data['body'] : '',
        'status' => 'draft'
    );
    $articleData['slug'] = $articleService->generateSlug($articleData['title']);

    $imagePrompt = isset($data['suggested_image_prompt']) ? $data['suggested_image_prompt'] : '';
    if ($imagePrompt) {
        try {
            $imageData = $openaiClient->generateImage($imagePrompt);
            if ($imageData) {
                $filename = uniqid('img_') . '.png';
                file_put_contents(UPLOADS_PATH . '/' . $filename, $imageData);
                $articleData['image'] = $filename;
            }
        } catch (Exception $e) {
            log_message('Ошибка генерации изображения: ' . $e->getMessage());
        }
    }

    $articleId = $articleService->save($articleData);
    $topicService->markDone($topic['id']);

    log_message('Сгенерирована статья ID ' . $articleId . ' по теме #' . $topic['id']);
} catch (Exception $e) {
    log_message('Ошибка cron: ' . $e->getMessage());
}
