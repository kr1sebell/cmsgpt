# OpenAI Article Publisher (PHP 5.6)

Полноценный PHP-проект для генерации и публикации статей через OpenAI API. Подходит для развёртывания на любом VPS с PHP-FPM 5.6 и MySQL 5.6.

## Возможности

- Публичная часть с листингом и страницами статей, микроразметкой и SEO-тегами.
- Админка с авторизацией, редактором статей, управлением темами и генерацией контента.
- Интеграция с OpenAI Chat и Images API.
- Автоматическая генерация sitemap.xml и robots.txt.
- Ежедневный cron-скрипт для автогенерации статей.

## Установка

1. Склонируйте репозиторий и перейдите в директорию проекта:
   ```bash
   git clone https://github.com/your-account/openai-article-publisher.git
   cd openai-article-publisher
   ```
2. Установите зависимости Composer (SafeMySQL):
   ```bash
   composer install
   ```
3. Скопируйте файл окружения и задайте свои параметры:
   ```bash
   cp .env.sample .env
   ```
   В `.env` пропишите доступы к БД, базовый URL и ключ OpenAI. При необходимости задайте `OPENAI_BASE_URL`, `OPENAI_RELAY_TOKEN` и параметры прокси (`OPENAI_PROXY`, `OPENAI_PROXY_AUTH`, `OPENAI_PROXY_TYPE`).
4. Импортируйте структуру базы данных:
   ```bash
   mysql -u root -p project < database/schema.sql
   ```
5. Настройте права на директории загрузок и логов:
   ```bash
   chmod -R 775 public/uploads logs
   ```
6. Настройте виртуальный хост в Nginx.

## Настройка Nginx + PHP-FPM

Пример server block для `/etc/nginx/conf.d/site.conf`:

```
server {
    listen 80;
    server_name example.com;
    root /var/www/openai-article-publisher;
    index index.php;

    location /public/ {
        try_files $uri $uri/ =404;
    }

    location / {
        try_files $uri /index.php?$args;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php5.6-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Перезагрузите Nginx после изменения конфигурации: `service nginx reload`.

## Cron

Для ежедневного запуска генерации статей добавьте в crontab запись:

```
0 6 * * * /usr/bin/php /var/www/openai-article-publisher/cron.php >> /var/www/openai-article-publisher/logs/cron.log 2>&1
```

Файл `cron.php` записывает сообщения в `logs/cron.log`.

## Доступ к админке

- URL: `https://example.com/admin/`
- Логин: задаётся в `.env` (`ADMIN_LOGIN`)
- Пароль: задаётся в `.env` (`ADMIN_PASSWORD`)

После импорта `database/schema.sql` также создаётся пользователь `admin` с паролем `admin123` (sha256). Вы можете удалить или изменить его.

## Настройки сайта

В разделе «Настройки» админки можно задать название сайта и базовое описание. Эти значения используются на главной странице и в SEO-тегах.

## Работа с OpenAI

Ключ API берётся из `.env`. По умолчанию запросы идут напрямую в `https://api.openai.com/v1`, но при необходимости можно указать собственный relay/прокси через `OPENAI_BASE_URL` и заголовок авторизации `OPENAI_RELAY_TOKEN`. Также доступны настройки прокси (`OPENAI_PROXY`, `OPENAI_PROXY_AUTH`, `OPENAI_PROXY_TYPE` — `http`, `https`, `socks4`, `socks5`).

Генератор отправляет запрос к `gpt-3.5-turbo`, ожидая ответ в формате JSON с полями `title`, `meta_description`, `h1`, `lead`, `body`, `suggested_image_prompt`. При наличии описания изображения отправляется запрос на генерацию картинки (png) и сохраняется в `public/uploads`.

## Безопасность

- Конфиденциальные данные хранятся в `.env` (не коммитится).
- Формы админки защищены CSRF-токенами.
- Авторизация через логин/пароль сессии.

## Требования

- PHP 5.6 + расширения cURL, mbstring
- MySQL 5.6
- Composer (для установки SafeMySQL)
- Nginx + PHP-FPM

Готово! После выполнения шагов выше сайт доступен на вашем домене, админка работает, cron генерирует черновики статей.
