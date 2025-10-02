<?php

class ArticleGenerator
{
    protected $openai;

    public function __construct(OpenAIClient $openai)
    {
        $this->openai = $openai;
    }

    public function buildPrompt($keyword)
    {
        $template = "Сгенерируй структуру статьи в формате JSON со следующими ключами: title, meta_description, h1, lead, body (поле body в виде одной HTML-строки (<p>, <h2>, <h3>, <h4>, <li> и так далее), без массива блоков, Выделяй ключевые фразы в <b> или <strong> в тексте.), suggested_image_prompt. Текст должен быть SEO оптимизирован под поисковую систему Яндекс и Гугл, а так же должны быть очеловечен, как будто его псиал человек а не робот, это важно. Это должна быть именно SEO статья под поиссковой запрос. Используй ключевое слово: %s. Пиши на русском языке. Верни только чистый JSON. Без комментариев, без текста до и после. Ответ строго должен начинаться с { и заканчиваться }.";
        return sprintf($template, $keyword);
    }

    public function createFromTopic($topic)
    {
        $prompt = $this->buildPrompt($topic['keyword']);
        $data = $this->openai->generateArticle($prompt);
        if (!isset($data['title'])) {
            throw new Exception('Ответ API не содержит title');
        }
        return $data;
    }
}
