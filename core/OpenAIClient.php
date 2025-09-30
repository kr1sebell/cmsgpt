<?php

class OpenAIClient
{
    protected $apiKey;
    protected $baseUrl = 'https://api.openai.com/v1';
    protected $relayToken = '';
    protected $timeout = 120;
    protected $connectTimeout = 30;

    public function __construct($apiKey, $options = array())
    {
        $this->apiKey = $apiKey;

        if (isset($options['base_url']) && trim($options['base_url']) !== '') {
            $this->baseUrl = rtrim($options['base_url'], '/');
        }

        if (isset($options['relay_token'])) {
            $this->relayToken = $options['relay_token'];
        }

        if (isset($options['timeout']) && (int)$options['timeout'] > 0) {
            $this->timeout = (int)$options['timeout'];
        }

        if (isset($options['connect_timeout']) && (int)$options['connect_timeout'] > 0) {
            $this->connectTimeout = (int)$options['connect_timeout'];
        }
    }

    protected function request($endpoint, $payload = array())
    {
        if (empty($this->apiKey) && empty($this->relayToken)) {
            throw new Exception('Не заданы реквизиты доступа к OpenAI.');
        }

        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $ch = curl_init($url);
        $headers = array(
            'Content-Type: application/json'
        );

        if (!empty($this->apiKey)) {
            $headers[] = 'Authorization: Bearer ' . $this->apiKey;
        }

        if (!empty($this->relayToken)) {
            $headers[] = 'X-Relay-Token: ' . $this->relayToken;
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        $response = curl_exec($ch);
        if ($response === false) {
            $errorMessage = curl_error($ch);
            $errorCode = curl_errno($ch);
            curl_close($ch);
            throw new Exception('Ошибка запроса: ' . ($errorMessage !== '' ? $errorMessage : 'неизвестная ошибка') . ' (код ' . $errorCode . ')');
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status >= 400) {
            $decoded = json_decode($response, true);
            if (is_array($decoded) && isset($decoded['error']['message'])) {
                throw new Exception('Ответ API (' . $status . '): ' . $decoded['error']['message']);
            }

            throw new Exception('Ответ API (' . $status . '): ' . $response);
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
