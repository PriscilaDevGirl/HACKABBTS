<?php
/**
 * test_openai_chat.php
 * Teste rápido de conexão e resposta da API OpenAI
 */

header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
ini_set('display_errors', 0);

// Caminho seguro para config.php (um nível acima)
$configPath = __DIR__ . '/../config.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(["ok" => false, "error" => "config.php não encontrado em $configPath"]);
    exit;
}

require_once $configPath;

// Verifica se chave existe
if (empty($apiKey)) {
    http_response_code(500);
    echo json_encode(["ok" => false, "error" => "Variável \$apiKey ausente ou vazia em config.php"]);
    exit;
}

// Monta prompt de teste simples
$messages = [
    ["role" => "system", "content" => "Você é um assistente conciso que responde em português."],
    ["role" => "user", "content" => "Me dê 3 campos de cadastro de funcionário no formato JSON (field, description)."]
];

$payload = [
    "model" => "gpt-4o-mini",
    "messages" => $messages,
    "temperature" => 0.1,
    "max_tokens" => 400
];

// === Envia requisição ===
$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer $apiKey"
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// === Tratamento de erro ===
if ($error) {
    http_response_code(500);
    echo json_encode([
        "ok" => false,
        "error" => "Erro de comunicação com OpenAI",
        "details" => $error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// === Processamento da resposta ===
$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode([
        "ok" => false,
        "error" => "Erro ao decodificar resposta da OpenAI",
        "raw_response" => substr($response, 0, 500)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$content = $data["choices"][0]["message"]["content"] ?? null;

if (!$content) {
    http_response_code(500);
    echo json_encode([
        "ok" => false,
        "error" => "Resposta vazia ou inválida da IA",
        "raw" => substr($response, 0, 500)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Tenta converter conteúdo em JSON
$tryJson = json_decode($content, true);

$result = [
    "ok" => true,
    "httpCode" => $httpCode,
    "assistant_content" => $content,
    "assistant_json" => $tryJson ?? "Conteúdo não é JSON puro"
];

// Retorna JSON final sem quebrar
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit;
