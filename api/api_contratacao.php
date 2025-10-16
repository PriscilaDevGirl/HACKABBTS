<?php
/**
 * api_contratacao.php
 * Recebe um arquivo Excel (.xlsx), lê com XML nativo, e padroniza campos via OpenAI.
 */

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=utf-8");

// Permitir requisições OPTIONS (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Aceita apenas POST com arquivo
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Use método POST"]);
    exit;
}

if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(["error" => "Nenhum arquivo enviado"]);
    exit;
}

$tmpFile = $_FILES['file']['tmp_name'];
$ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

if ($ext !== 'xlsx') {
    http_response_code(400);
    echo json_encode(["error" => "Formato inválido. Envie um arquivo .xlsx"]);
    exit;
}

// === FUNÇÃO PARA LER ARQUIVO XLSX SEM BIBLIOTECA ===
function lerExcelXLSX($arquivo) {
    $zip = new ZipArchive;
    if ($zip->open($arquivo) === true) {
        // Lê o arquivo com as planilhas (Sheet1)
        $xmlString = $zip->getFromName('xl/worksheets/sheet1.xml');
        $sharedStrings = [];

        // Lê os valores compartilhados (strings)
        if ($zip->locateName('xl/sharedStrings.xml') !== false) {
            $shared = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
            foreach ($shared->si as $item) {
                $sharedStrings[] = (string)$item->t;
            }
        }

        $sheet = simplexml_load_string($xmlString);
        $sheet->registerXPathNamespace('a', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $r = [];
            foreach ($row->c as $c) {
                $t = (string)$c['t'];
                $v = (string)$c->v;

                if ($t == 's') { // string compartilhada
                    $v = $sharedStrings[(int)$v] ?? '';
                }
                $r[] = $v;
            }
            $rows[] = $r;
        }

        $zip->close();
        return $rows;
    } else {
        return [];
    }
}

// === LER DADOS ===
$rows = lerExcelXLSX($tmpFile);
if (empty($rows)) {
    echo json_encode(["error" => "Erro ao ler o arquivo XLSX"]);
    exit;
}

// Extrair cabeçalhos e dados
$headers = array_map('trim', $rows[0]);
$data = [];
for ($i = 1; $i < count($rows); $i++) {
    $linha = [];
    foreach ($headers as $index => $coluna) {
        if (!empty($coluna)) {
            $linha[$coluna] = $rows[$i][$index] ?? null;
        }
    }
    if (array_filter($linha)) {
        $data[] = $linha;
    }
}

// === PADRONIZAR COM A IA ===
$apiKey = 'sk-proj-SEU_TOKEN_AQUI'; // substitua pela sua chave válida

$padraoCampos = [
    "id_contrato",
    "nome_funcionario",
    "cpf",
    "cargo",
    "setor",
    "data_admissao",
    "salario",
    "tipo_contrato",
    "status",
    "observacao"
];

// Prompt para IA
$prompt = "Padronize os campos do JSON a seguir conforme o padrão:\n" .
json_encode($padraoCampos, JSON_UNESCAPED_UNICODE) . "\n\n" .
"Retorne APENAS JSON puro e válido, com os nomes dos campos padronizados.\n\n" .
"Dados originais:\n" .
json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

// Montar payload
$payload = [
    "model" => "gpt-4o-mini",
    "messages" => [
        ["role" => "system", "content" => "Você transforma dados de planilhas em JSON padronizado."],
        ["role" => "user", "content" => $prompt]
    ],
    "temperature" => 0.2
];

// Enviar para API da OpenAI
$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer $apiKey"
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(500);
    echo json_encode(["error" => "Erro na comunicação com a IA: $error"]);
    exit;
}

$result = json_decode($response, true);
$reply = $result["choices"][0]["message"]["content"] ?? null;

// === Retornar JSON final ===
if ($reply) {
    $json = json_decode($reply, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        echo $reply; // caso a IA já retorne JSON válido como texto
    }
} else {
    http_response_code(500);
    echo json_encode(["error" => "A resposta da IA não pôde ser interpretada."]);
}
