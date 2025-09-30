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
        $template = "Сгенерируй структуру статьи в формате JSON со следующими ключами: title, meta_description, h1, lead, body (в формате HTML параграфов и подзаголовков h2/h3), suggested_image_prompt. Используй ключевое слово: %s. Пиши на русском языке. JSON без дополнительных комментариев.";
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
