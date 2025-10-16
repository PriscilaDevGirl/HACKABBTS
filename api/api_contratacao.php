<?php
/**
 * api_contratacao.php
 * Lê um arquivo XLSX, envia para a IA e retorna JSON com nova ordem de colunas.
 * Sempre responde com JSON puro.
 */

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=utf-8");
error_reporting(0);
ini_set('display_errors', 0);

// === FUNÇÃO DE LOG ===
function log_debug($msg) {
    file_put_contents(__DIR__ . '/debug_api.log', "[" . date('Y-m-d H:i:s') . "] $msg\n", FILE_APPEND);
}

// === PERMITIR OPTIONS ===
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// === VALIDAR MÉTODO ===
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Use método POST"]);
    exit;
}

// === VALIDAR UPLOAD ===
if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(["error" => "Nenhum arquivo enviado"]);
    exit;
}

 $tmpFile = $_FILES['file']['tmp_name'];
 $fileName = $_FILES['file']['name'];
 $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
log_debug("📁 Recebido arquivo: $fileName ($ext)");

if ($ext !== 'xlsx') {
    http_response_code(400);
    echo json_encode(["error" => "Formato inválido. Envie um arquivo .xlsx"]);
    exit;
}

// === FUNÇÃO PARA LER XLSX SEM BIBLIOTECAS ===
function lerExcelXLSX($arquivo) {
    $zip = new ZipArchive;
    if ($zip->open($arquivo) === true) {
        $xmlString = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$xmlString) {
            log_debug("❌ sheet1.xml não encontrado");
            $zip->close();
            return [];
        }

        $sharedStrings = [];
        if ($zip->locateName('xl/sharedStrings.xml') !== false) {
            $shared = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
            foreach ($shared->si as $item) {
                if (isset($item->t)) {
                    $sharedStrings[] = (string)$item->t;
                } else {
                    $sharedStrings[] = trim(preg_replace('/\s+/', ' ', strip_tags($item->asXML())));
                }
            }
        }

        $sheet = simplexml_load_string($xmlString);
        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $r = [];
            foreach ($row->c as $c) {
                $t = (string)$c['t'];
                $v = (string)$c->v;
                if ($t === 's') {
                    $v = $sharedStrings[(int)$v] ?? '';
                }
                $r[] = $v;
            }
            $rows[] = $r;
        }
        $zip->close();
        return $rows;
    }
    log_debug("❌ Falha ao abrir arquivo XLSX");
    return [];
}

// === PROCESSAR PLANILHA ===
 $rows = lerExcelXLSX($tmpFile);
if (empty($rows)) {
    log_debug("❌ Planilha vazia ou leitura falhou");
    http_response_code(500);
    echo json_encode(["error" => "Erro ao ler o arquivo XLSX"]);
    exit;
}

log_debug("✅ Lidas " . count($rows) . " linhas de dados válidos");

// === CONSTRUIR DATASET ===
 $headers = array_map('trim', $rows[0]);
 $data = [];
for ($i = 1; $i < count($rows); $i++) {
    $linha = [];
    foreach ($headers as $index => $coluna) {
        if ($coluna !== '') {
            $linha[$coluna] = $rows[$i][$index] ?? null;
        }
    }
    if (array_filter($linha)) {
        $data[] = $linha;
    }
}

// === LIMITAR A 20 LINHAS PARA TESTE (aumentado para melhor análise) ===
 $MAX_ROWS = 20;
if (count($data) > $MAX_ROWS) {
    $data = array_slice($data, 0, $MAX_ROWS);
    log_debug("⚠️ Limitado a $MAX_ROWS linhas para teste (payload reduzido)");
}

// === IMPORTAR CONFIG ===
 $configPath = __DIR__ . '/../config.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(["error" => "config.php não encontrado"]);
    log_debug("❌ config.php ausente");
    exit;
}
require_once $configPath;

if (empty($apiKey)) {
    http_response_code(500);
    echo json_encode(["error" => "API Key ausente em config.php"]);
    log_debug("❌ API Key vazia");
    exit;
}
log_debug("✅ Chave OpenAI detectada: " . substr($apiKey, 0, 10) . "...");

// === PADRÃO DE CAMPOS DE REFERÊNCIA ===
 $padraoCampos = [
    "saldo_manut",
    "tipo",
    "codigo",
    "cmm",
    "pecas_teste_kit",
    "coef_perda",
    "laboratorio",
    "wr",
    "stage_wr"
];

// === PROMPT DE REORDENAÇÃO ===
 $prompt = "Você receberá as colunas e algumas linhas de exemplo de uma planilha.
Analise e retorne a ordem lógica correta dos campos, com base no padrão de referência abaixo.

Regras importantes:
1. A saída deve conter EXATAMENTE as colunas do padrão, na mesma ordem.
2. Se uma coluna do padrão não existir no arquivo, inclua-a com valores vazios (null ou string vazia).
3. Se o arquivo tiver colunas extras que não estão no padrão, IGNORE-AS completamente.
4. Mapeie as colunas do arquivo para as colunas do padrão quando houver correspondência lógica (mesmo nome ou similar).
5. Para colunas do padrão sem correspondência no arquivo, preencha com null.

Formato de saída obrigatório:
{
  \"ordered_headers\": [\"campo1\",\"campo2\",...],  // deve ser exatamente o padrão, na ordem
  \"mapping\": {\"coluna_original\": \"coluna_padronizada\"}, // mapeamento das colunas do arquivo para o padrão
  \"rows\": [{\"campo1\": valor, \"campo2\": valor, ...}] // cada linha deve ter todas as colunas do padrão
}

Padrão de referência:
" . json_encode($padraoCampos, JSON_UNESCAPED_UNICODE) . "

Colunas encontradas:
" . json_encode($headers, JSON_UNESCAPED_UNICODE) . "

Linhas de exemplo:
" . json_encode(array_slice($data, 0, 5), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "

⚠️ Retorne APENAS o JSON puro, sem markdown, texto extra, nem ```.";

// === PAYLOAD OPENAI ===
 $payload = [
    "model" => "gpt-4o-mini",
    "messages" => [
        ["role" => "system", "content" => "Você é um especialista em reorganizar colunas de planilhas e retornar JSON puro. Seu foco é trabalhar com campos de manutenção como saldo_manut, tipo, codigo, cmm, pecas_teste_kit, coef_perda, laboratorio, wr e stage_wr."],
        ["role" => "user", "content" => $prompt]
    ],
    "temperature" => 0.0,
    "max_tokens" => 4000  // AUMENTADO PARA EVITAR TRUNCAMENTO
];

// === ENVIO PARA OPENAI ===
 $ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer $apiKey"
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 120,  // AUMENTADO TIMEOUT
    CURLOPT_SSL_VERIFYPEER => false
]);

 $response = curl_exec($ch);
 $error = curl_error($ch);
 $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

log_debug("🌐 HTTP_CODE: $httpCode");
if ($error) log_debug("❌ CURL ERROR: $error");

// === ERROS DE CONEXÃO ===
if ($error) {
    http_response_code(500);
    echo json_encode(["error" => "Erro na comunicação com OpenAI", "details" => $error]);
    exit;
}
if ($httpCode >= 400) {
    http_response_code($httpCode);
    echo json_encode(["error" => "Erro HTTP da OpenAI", "code" => $httpCode, "response" => $response]);
    exit;
}

// === PROCESSAR RESPOSTA ===
 $result = json_decode($response, true);
 $content = $result["choices"][0]["message"]["content"] ?? null;

// Verificar se a resposta foi truncada
if (isset($result["choices"][0]["finish_reason"]) && $result["choices"][0]["finish_reason"] === "length") {
    log_debug("⚠️ Resposta truncada - aumente max_tokens");
    http_response_code(500);
    echo json_encode([
        "error" => "Resposta da IA truncada. Aumente max_tokens ou reduza os dados.",
        "raw" => substr($content, 0, 1000)
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

if (!$content) {
    http_response_code(500);
    echo json_encode(["error" => "Sem conteúdo retornado da IA", "raw" => substr($response, 0, 500)]);
    exit;
}

// === LIMPEZA UNIVERSAL (REMOVE ```json, ESPAÇOS, ETC) ===
 $content = trim($content);
 $content = preg_replace('/^```(?:json)?/i', '', $content);
 $content = preg_replace('/```$/', '', $content);
 $content = trim($content);

// === TENTAR DECODIFICAR DIRETAMENTE ===
 $parsed = json_decode($content, true);
if (json_last_error() === JSON_ERROR_NONE) {
    // Validar estrutura da resposta
    if (isset($parsed['ordered_headers']) && isset($parsed['mapping']) && isset($parsed['rows'])) {
        // Garantir que ordered_headers seja exatamente o padrão
        if ($parsed['ordered_headers'] !== $padraoCampos) {
            log_debug("⚠️ Headers não correspondem ao padrão. Corrigindo...");
            $parsed['ordered_headers'] = $padraoCampos;
            
            // Reorganizar linhas conforme o padrão
            $newRows = [];
            foreach ($parsed['rows'] as $row) {
                $newRow = [];
                foreach ($padraoCampos as $campo) {
                    $newRow[$campo] = $row[$campo] ?? null;
                }
                $newRows[] = $newRow;
            }
            $parsed['rows'] = $newRows;
        }
        
        $parsed["source"] = "openai_reorder";
        log_debug("✅ JSON puro detectado e enviado");
        echo json_encode($parsed, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

// === TENTAR EXTRAÇÃO ENTRE CHAVES ===
 $start = strpos($content, '{');
 $end = strrpos($content, '}');
if ($start !== false && $end !== false && $end > $start) {
    $maybe = substr($content, $start, $end - $start + 1);
    $try = json_decode($maybe, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        // Validar estrutura da resposta
        if (isset($try['ordered_headers']) && isset($try['mapping']) && isset($try['rows'])) {
            // Garantir que ordered_headers seja exatamente o padrão
            if ($try['ordered_headers'] !== $padraoCampos) {
                log_debug("⚠️ Headers não correspondem ao padrão. Corrigindo...");
                $try['ordered_headers'] = $padraoCampos;
                
                // Reorganizar linhas conforme o padrão
                $newRows = [];
                foreach ($try['rows'] as $row) {
                    $newRow = [];
                    foreach ($padraoCampos as $campo) {
                        $newRow[$campo] = $row[$campo] ?? null;
                    }
                    $newRows[] = $newRow;
                }
                $try['rows'] = $newRows;
            }
            
            $try["source"] = "openai_reorder";
            log_debug("✅ JSON extraído parcialmente e enviado");
            echo json_encode($try, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
    }
}

// === FALHA FINAL ===
log_debug("⚠️ A IA retornou texto não parseável. Head: " . substr($content, 0, 500));
http_response_code(500);
echo json_encode([
    "error" => "A IA não retornou JSON válido.",
    "raw" => substr($content, 0, 1000)
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit;
?>