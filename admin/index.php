<?php
require_once __DIR__ . '/../config.php';

$articleService = new ArticleService();
$topicService = new TopicService();
$settingService = new SettingService();
$openaiClient = new OpenAIClient(OPENAI_API_KEY);
$generator = new ArticleGenerator($openaiClient);

$action = isset($_GET['action']) ? $_GET['action'] : 'articles';

if ($action === 'logout') {
    Auth::logout();
    Helpers::redirect('/admin/');
}

if (!Auth::check()) {
    $error = null;
    if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $login = isset($_POST['login']) ? trim($_POST['login']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        if (Auth::attempt($login, $password)) {
            Helpers::redirect('/admin/');
        } else {
            $error = 'Неверный логин или пароль';
        }
    }
    echo Helpers::view('admin/login', array('error' => $error));
    exit;
}

switch ($action) {
    case 'articles':
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $articles = $articleService->listAll($status ? $status : null);
        echo Helpers::view('admin/layout', array(
            'title' => 'Статьи',
            'content' => Helpers::view('admin/articles_list', array(
                'articles' => $articles,
                'status' => $status
            ))
        ));
        break;

    case 'edit_article':
        $article = array(
            'title' => '',
            'meta_description' => '',
            'h1' => '',
            'lead' => '',
            'body' => '',
            'status' => 'draft'
        );
        if (!empty($_GET['id'])) {
            $found = $articleService->find((int)$_GET['id']);
            if ($found) {
                $article = $found;
            }
        }
        $message = isset($_GET['saved']) ? 'Статья сохранена' : '';
        echo Helpers::view('admin/layout', array(
            'title' => 'Редактор',
            'content' => ($message ? '<div class="alert">' . Helpers::e($message) . '</div>' : '') . Helpers::view('admin/article_form', array('article' => $article))
        ));
        break;

    case 'save_article':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Helpers::validateCsrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
            die('Неверный CSRF-токен');
        }
        $data = array(
            'id' => isset($_POST['id']) ? (int)$_POST['id'] : null,
            'title' => trim($_POST['title']),
            'meta_description' => trim($_POST['meta_description']),
            'h1' => trim($_POST['h1']),
            'lead' => trim($_POST['lead']),
            'body' => $_POST['body'],
            'status' => isset($_POST['status']) ? $_POST['status'] : 'draft'
        );
        $data['slug'] = $articleService->generateSlug($data['title'], $data['id']);

        if (!empty($_FILES['image']['name'])) {
            $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($extension, array('jpg', 'jpeg', 'png', 'gif'))) {
                $extension = 'jpg';
            }
            $filename = uniqid('img_') . '.' . $extension;
            $path = UPLOADS_PATH . '/' . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $path)) {
                $data['image'] = $filename;
            }
        }

        $articleId = $articleService->save($data);
        if (!empty($_POST['publish']) || $data['status'] === 'published') {
            $articleService->publish($articleId);
        }
        Helpers::redirect('/admin/?action=edit_article&id=' . $articleId . '&saved=1');
        break;

    case 'upload_media':
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            echo json_encode(array('error' => 'Метод не поддерживается'));
            exit;
        }
        if (!Helpers::validateCsrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
            http_response_code(400);
            echo json_encode(array('error' => 'Неверный CSRF-токен'));
            exit;
        }
        if (empty($_FILES['file']) || !isset($_FILES['file']['error']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(array('error' => 'Не удалось загрузить файл'));
            exit;
        }

        $extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $allowedImages = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg');
        $allowedVideos = array('mp4', 'webm', 'ogg');
        $allowedFiles = array_merge($allowedImages, $allowedVideos, array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar')); // расширенный список для менеджера

        if (!in_array($extension, $allowedFiles, true)) {
            http_response_code(400);
            echo json_encode(array('error' => 'Недопустимый тип файла'));
            exit;
        }

        $filename = str_replace('.', '', uniqid('media_', true)) . '.' . $extension;
        $path = UPLOADS_PATH . '/' . $filename;

        if (!move_uploaded_file($_FILES['file']['tmp_name'], $path)) {
            http_response_code(500);
            echo json_encode(array('error' => 'Ошибка сохранения файла'));
            exit;
        }

        $fileType = 'file';
        if (in_array($extension, $allowedImages, true)) {
            $fileType = 'image';
        } elseif (in_array($extension, $allowedVideos, true)) {
            $fileType = 'media';
        }

        $location = '/public/uploads/' . $filename;
        echo json_encode(array(
            'success' => true,
            'location' => $location,
            'name' => $filename,
            'type' => $fileType
        ));
        exit;

    case 'list_media':
        header('Content-Type: application/json');
        if (!Helpers::validateCsrf(isset($_GET['csrf_token']) ? $_GET['csrf_token'] : '')) {
            http_response_code(400);
            echo json_encode(array('error' => 'Неверный CSRF-токен'));
            exit;
        }

        $requestedType = isset($_GET['type']) ? $_GET['type'] : 'all';
        $allowedTypes = array('all', 'image', 'media', 'file');
        if (!in_array($requestedType, $allowedTypes, true)) {
            $requestedType = 'all';
        }

        $items = array();
        if (is_dir(UPLOADS_PATH)) {
            $files = scandir(UPLOADS_PATH);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $fullPath = UPLOADS_PATH . '/' . $file;
                if (!is_file($fullPath)) {
                    continue;
                }
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $type = 'file';
                $imageExtensions = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg');
                $videoExtensions = array('mp4', 'webm', 'ogg');
                if (in_array($extension, $imageExtensions, true)) {
                    $type = 'image';
                } elseif (in_array($extension, $videoExtensions, true)) {
                    $type = 'media';
                }
                if ($requestedType !== 'all' && $requestedType !== $type) {
                    continue;
                }

                $items[] = array(
                    'name' => $file,
                    'url' => '/public/uploads/' . rawurlencode($file),
                    'type' => $type,
                    'size' => filesize($fullPath),
                    'modified' => filemtime($fullPath)
                );
            }
        }

        usort($items, function ($a, $b) {
            if ($a['modified'] === $b['modified']) {
                return strcmp($a['name'], $b['name']);
            }
            return $a['modified'] < $b['modified'] ? 1 : -1;
        });

        echo json_encode(array('files' => $items));
        exit;

    case 'generator':
        $message = isset($_GET['message']) ? $_GET['message'] : '';
        $topic = $topicService->nextInQueue();
        echo Helpers::view('admin/layout', array(
            'title' => 'Генерация',
            'content' => Helpers::view('admin/generator', array('topic' => $topic, 'message' => $message))
        ));
        break;

    case 'generate_article':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Helpers::validateCsrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
            die('Неверный CSRF-токен');
        }
        $topicId = (int)$_POST['topic_id'];
        $topic = $topicService->nextInQueue();
        if (!$topic || $topic['id'] != $topicId) {
            Helpers::redirect('/admin/?action=generator&message=Тема+не+найдена');
        }
        try {
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
                    // игнорируем ошибки генерации изображений
                }
            }

            $articleId = $articleService->save($articleData);
            $topicService->markDone($topic['id']);
            Helpers::redirect('/admin/?action=edit_article&id=' . $articleId);
        } catch (Exception $e) {
            Helpers::redirect('/admin/?action=generator&message=' . urlencode($e->getMessage()));
        }
        break;

    case 'topics':
        $topics = $topicService->all();
        echo Helpers::view('admin/layout', array(
            'title' => 'Темы',
            'content' => Helpers::view('admin/topics', array('topics' => $topics))
        ));
        break;

    case 'add_topic':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Helpers::validateCsrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
            die('Неверный CSRF-токен');
        }
        $keyword = trim($_POST['keyword']);
        if ($keyword) {
            $topicService->add($keyword);
        }
        Helpers::redirect('/admin/?action=topics');
        break;

    case 'settings':
        $settings = array(
            'site_name' => $settingService->get('site_name', 'AI Publisher'),
            'site_description' => $settingService->get('site_description', 'Генератор статей на базе OpenAI.')
        );
        $message = isset($_GET['saved']) ? 'Настройки сохранены' : '';
        echo Helpers::view('admin/layout', array(
            'title' => 'Настройки',
            'content' => Helpers::view('admin/settings', array('settings' => $settings, 'message' => $message))
        ));
        break;

    case 'save_settings':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Helpers::validateCsrf(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
            die('Неверный CSRF-токен');
        }
        $settingService->set('site_name', trim($_POST['site_name']));
        $settingService->set('site_description', trim($_POST['site_description']));
        Helpers::redirect('/admin/?action=settings&saved=1');
        break;

    default:
        Helpers::redirect('/admin/?action=articles');
}
