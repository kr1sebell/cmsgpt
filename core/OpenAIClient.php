<?php

class OpenAIClient
{
    protected $apiKey;
//    protected $baseUrl = 'https://api.openai.com/v1';
    protected $baseUrl = 'https://quick-donkey-55.deno.dev';

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    protected function request($endpoint, $payload = array())
    {
        if (empty($this->apiKey)) {
            throw new Exception('Не задан ключ OpenAI API.');
        }

        $url = $this->baseUrl . $endpoint;
        $ch = curl_init($url);
        $headers = array(
//            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'X-Relay-Token: CHANGE_ME_STRONG_SHARED_SECRET' ,   // авторизация на релэе
        );

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);

        $response = curl_exec($ch);
        if ($response === false) {
            throw new Exception('Ошибка запроса: ' . curl_error($ch));
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status >= 400) {
            throw new Exception('Ответ API: ' . $response);
        }

        return json_decode($response, true);
    }

    public function generateArticle($prompt)
    {
        $payload = array(
            'model' => 'gpt-3.5-turbo',
            'messages' => array(
                array('role' => 'system', 'content' => 'Ты профессиональный русскоязычный SEO-копирайтер.'),
                array('role' => 'user', 'content' => $prompt)
            ),
            'temperature' => 0.7
        );

        $response = $this->request('/chat/completions', $payload);
        if (!isset($response['choices'][0]['message']['content'])) {
            throw new Exception('Некорректный ответ API');
        }

        $content = $response['choices'][0]['message']['content'];
        $json = json_decode($content, true);
        if (!$json) {
            throw new Exception('Не удалось распарсить JSON: ' . $content);
        }

        return $json;
    }

    public function generateImage($prompt)
    {
        if (!$prompt) {
            return null;
        }
        $payload = array(
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1024x1024',
            'response_format' => 'b64_json'
        );

        $response = $this->request('/images/generations', $payload);
        if (isset($response['data'][0]['b64_json'])) {
            return base64_decode($response['data'][0]['b64_json']);
        }
        return null;
    }
}
