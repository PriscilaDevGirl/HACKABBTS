<?php
// Configuração da API

// Função para enviar arquivo para a API
function sendFileToAPI($filePath, $fileName) {
    $ch = curl_init();
    
    // Preparar arquivo para envio
    $cfile = new CURLFile($filePath, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $fileName);
    
    // Configurar requisição cURL
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/HACKABBTS/api/api_contratacao.php');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => $cfile]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: multipart/form-data'
    ]);
    
    // Executar requisição
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Retornar resposta
    return [
        'success' => $httpCode == 200,
        'response' => $response,
        'httpCode' => $httpCode
    ];
}

// Função para gerar JSON padronizado a partir dos dados da API
function generateStandardizedJSON($apiData) {
    if (!$apiData || !isset($apiData['data']) || !isset($apiData['data']['mapping']) || !isset($apiData['data']['rows'])) {
        return null;
    }
    
    $mapping = $apiData['data']['mapping'];
    $rows = $apiData['data']['rows'];
    
    // Criar diretório dados se não existir
    if (!file_exists('dados')) {
        mkdir('dados', 0777, true);
    }
    
    // Transformar dados usando o mapeamento
    $standardizedData = [];
    foreach ($rows as $row) {
        $newRow = [];
        foreach ($row as $key => $value) {
            // Se a chave original existe no mapping, usar o novo nome
            if (isset($mapping[$key]) && !empty($mapping[$key])) {
                $newKey = $mapping[$key];
                $newRow[$newKey] = $value;
            }
        }
        $standardizedData[] = $newRow;
    }
    
    // Salvar JSON em dados/dados_geral.json
    $filePath = 'dados/dados_geral.json';
    file_put_contents($filePath, json_encode($standardizedData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    
    return [
        'file_path' => $filePath,
        'data' => $standardizedData
    ];
}

// Processar upload se houver envio de arquivo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $uploadDir = 'uploads/';
    
    // Criar diretório se não existir
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = $_FILES['excel_file']['name'];
    $filePath = $uploadDir . basename($fileName);
    
    // Mover arquivo temporário
    if (move_uploaded_file($_FILES['excel_file']['tmp_name'], $filePath)) {
        // Enviar para API
        $apiResult = sendFileToAPI($filePath, $fileName);
        
        if ($apiResult['success']) {
            // Decodificar resposta da API
            $apiData = json_decode($apiResult['response'], true);
            
            // Gerar JSON padronizado
            $jsonResult = generateStandardizedJSON($apiData);
            
            // Preparar resposta
            $response = [
                'success' => true,
                'message' => 'Arquivo enviado e JSON padronizado gerado com sucesso!',
                'api_data' => $apiData,
                'json_data' => $jsonResult
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Erro ao enviar arquivo para API: ' . $apiResult['httpCode']
            ];
        }
        
        // Remover arquivo temporário
        unlink($filePath);
    } else {
        $response = [
            'success' => false,
            'message' => 'Erro ao fazer upload do arquivo.'
        ];
    }
    
    // Retornar resposta como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BIAB - Upload de Planilhas</title>
  <style>
    :root {
      --brand-color-primary-pure: #465EFF;
      --brand-color-primary-light: #C2D6FF;
      --brand-color-primary-dark: #262E89;
      --brand-color-secondary-pure: #FCFC30;
      --highlight-color: #54DCFC;
      --neutral-color-lightest: #FAFAFA;
      --neutral-color-lighter: #E4E4E7;
      --neutral-color-light: #D4D4D8;
      --neutral-color-medium: #71717B;
      --neutral-color-dark: #3F3F46;
      --neutral-color-darkest: #09090B;
      --feedback-color-success: #23D80A;
      --feedback-color-warning: #F99207;
      --feedback-color-danger: #FF2E3B;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: var(--neutral-color-lightest);
      display: flex;
      height: 100vh;
    }

    /* Sidebar */
    .sidebar {
      width: 250px;
      background-color: var(--neutral-color-darkest);
      color: #fff;
      padding: 20px;
      display: flex;
      flex-direction: column;
    }

    .logo {
      background-color: #fff;
      color: var(--brand-color-primary-pure);
      font-weight: bold;
      font-size: 20px;
      height: 45px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 6px;
      margin-bottom: 30px;
    }

    .sidebar-title {
      font-size: 12px;
      color: #ccc;
      text-transform: uppercase;
      margin-bottom: 15px;
    }

    .sidebar-item {
      padding: 12px 16px;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      gap: 10px;
      color: #ccc;
      font-size: 15px;
    }

    .sidebar-item.active {
      background-color: var(--brand-color-primary-pure);
      color: white;
    }

    .sidebar-item:hover {
      background-color: var(--neutral-color-dark);
    }

    .sidebar-footer {
      margin-top: auto;
      font-size: 12px;
      color: #aaa;
      border-top: 1px solid #333;
      padding-top: 15px;
    }

    /* Main area */
    .main {
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .header {
      background-color: #fff;
      border-bottom: 1px solid var(--neutral-color-lighter);
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .search-bar {
      background-color: var(--neutral-color-lightest);
      border: 1px solid var(--neutral-color-lighter);
      border-radius: 8px;
      padding: 10px 15px;
      display: flex;
      align-items: center;
      gap: 10px;
      width: 60%;
    }

    .search-bar input {
      border: none;
      outline: none;
      flex: 1;
      background: none;
      font-size: 14px;
      color: var(--neutral-color-medium);
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .user-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: linear-gradient(135deg, #465EFF, #262E89);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
    }

    .content {
      padding: 30px;
      overflow-y: auto;
      flex: 1;
    }

    .welcome-section h1 {
      font-size: 28px;
      color: var(--neutral-color-darkest);
    }

    .welcome-section p {
      color: var(--neutral-color-medium);
      margin-bottom: 25px;
    }

    /* Upload Section */
    .upload-section {
      background-color: white;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      padding: 30px;
      margin-bottom: 30px;
    }

    .upload-section h2 {
      font-size: 22px;
      color: var(--neutral-color-darkest);
      margin-bottom: 15px;
    }

    .upload-area {
      border: 2px dashed var(--neutral-color-light);
      border-radius: 10px;
      padding: 40px;
      text-align: center;
      transition: all 0.3s;
      cursor: pointer;
      background-color: var(--neutral-color-lightest);
    }

    .upload-area:hover, .upload-area.dragover {
      border-color: var(--brand-color-primary-pure);
      background-color: var(--brand-color-primary-light);
    }

    .upload-icon {
      font-size: 48px;
      color: var(--brand-color-primary-pure);
      margin-bottom: 15px;
    }

    .upload-text {
      font-size: 16px;
      color: var(--neutral-color-medium);
      margin-bottom: 15px;
    }

    .upload-button {
      background-color: var(--brand-color-primary-pure);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    .upload-button:hover {
      background-color: var(--brand-color-primary-dark);
    }

    #file-input {
      display: none;
    }

    .upload-status {
      margin-top: 20px;
      padding: 15px;
      border-radius: 8px;
      display: none;
    }

    .upload-status.success {
      background-color: rgba(35, 216, 10, 0.1);
      color: var(--feedback-color-success);
      border: 1px solid var(--feedback-color-success);
    }

    .upload-status.error {
      background-color: rgba(255, 46, 59, 0.1);
      color: var(--feedback-color-danger);
      border: 1px solid var(--feedback-color-danger);
    }

    .progress-container {
      margin-top: 15px;
      height: 6px;
      background-color: var(--neutral-color-lighter);
      border-radius: 3px;
      overflow: hidden;
      display: none;
    }

    .progress-bar {
      height: 100%;
      background-color: var(--brand-color-primary-pure);
      width: 0%;
      transition: width 0.3s;
    }

    /* Cards */
    .cards-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .card {
      background-color: white;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      transition: 0.3s;
    }

    .card:hover {
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    .card-title {
      font-size: 14px;
      color: var(--neutral-color-medium);
      margin-bottom: 10px;
    }

    .card-value {
      font-size: 26px;
      font-weight: bold;
      color: var(--neutral-color-darkest);
    }

    .card-change {
      font-size: 13px;
      font-weight: 600;
      margin-top: 5px;
    }

    .positive { color: var(--feedback-color-success); }
    .negative { color: var(--feedback-color-danger); }

    /* Chat Assistente */
    .assistant-box {
      background-color: white;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      text-align: center;
    }

    .assistant-box h2 {
      color: var(--neutral-color-darkest);
      font-size: 20px;
      margin-bottom: 10px;
    }

    .assistant-box p {
      color: var(--neutral-color-medium);
      margin-bottom: 20px;
      font-size: 14px;
    }

    .btn {
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      background-color: var(--brand-color-primary-pure);
      color: white;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
    }

    .btn:hover {
      background-color: var(--brand-color-primary-dark);
    }
    
    /* Resultados da API */
    .results-section {
      background-color: white;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      padding: 30px;
      margin-bottom: 30px;
      display: none;
    }
    
    .results-section h2 {
      font-size: 22px;
      color: var(--neutral-color-darkest);
      margin-bottom: 15px;
    }
    
    .tabs {
      display: flex;
      border-bottom: 1px solid var(--neutral-color-lighter);
      margin-bottom: 20px;
    }
    
    .tab {
      padding: 10px 20px;
      cursor: pointer;
      border-bottom: 2px solid transparent;
      transition: all 0.3s;
    }
    
    .tab.active {
      border-bottom-color: var(--brand-color-primary-pure);
      color: var(--brand-color-primary-pure);
      font-weight: 600;
    }
    
    .tab-content {
      display: none;
    }
    
    .tab-content.active {
      display: block;
    }
    
    .json-viewer {
      background-color: var(--neutral-color-lightest);
      border: 1px solid var(--neutral-color-lighter);
      border-radius: 8px;
      padding: 15px;
      font-family: 'Courier New', monospace;
      font-size: 14px;
      max-height: 400px;
      overflow-y: auto;
      white-space: pre-wrap;
    }
    
    .action-buttons {
      margin-top: 20px;
      display: flex;
      gap: 10px;
    }
    
    .btn-secondary {
      background-color: var(--neutral-color-medium);
    }
    
    .btn-secondary:hover {
      background-color: var(--neutral-color-dark);
    }
    
    .mapping-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    
    .mapping-table th, .mapping-table td {
      padding: 8px 12px;
      text-align: left;
      border-bottom: 1px solid var(--neutral-color-lighter);
    }
    
    .mapping-table th {
      background-color: var(--neutral-color-lightest);
      font-weight: 600;
    }
  </style>
</head>
<body>
  <aside class="sidebar">
    <div class="logo">BIAB</div>
    <div class="sidebar-title">Menu</div>
    <div class="sidebar-item">📊 Dashboard</div>
    <div class="sidebar-item active">📤 Upload de Planilhas</div>
    <div class="sidebar-item">💬 Chat IA</div>
    <div class="sidebar-item">📦 Meus Itens</div>
    <div class="sidebar-item">⚙️ Configurações</div>
    <div class="sidebar-footer">
      <p><strong>Versão 1.0</strong></p>
      <p>Powered by Azure AI</p>
    </div>
  </aside>

  <main class="main">
    <header class="header">
      <div class="search-bar">
        🔍 <input type="text" placeholder="Buscar materiais, status, insights...">
      </div>
      <div class="user-info">
        <div>
          <div style="font-size: 13px; font-weight: 600;">Usuário BBTS</div>
          <div style="font-size: 12px; color: var(--neutral-color-medium);">Colaborador</div>
        </div>
        <div class="user-avatar">U</div>
      </div>
    </header>

    <section class="content">
      <div class="welcome-section">
        <h1>Upload de Planilhas</h1>
        <p>Envie suas planilhas Excel para processamento pela plataforma</p>
      </div>

      <div class="upload-section">
        <h2>Enviar Nova Planilha</h2>
        <div class="upload-area" id="upload-area">
          <div class="upload-icon">📊</div>
          <p class="upload-text">Arraste e solte sua planilha Excel aqui</p>
          <p class="upload-text">ou</p>
          <button class="upload-button" id="upload-button">Selecionar Arquivo</button>
          <input type="file" id="file-input" accept=".xlsx,.xls">
        </div>
        
        <div class="progress-container" id="progress-container">
          <div class="progress-bar" id="progress-bar"></div>
        </div>
        
        <div class="upload-status" id="upload-status"></div>
      </div>

      <div class="results-section" id="results-section">
        <h2>Resultados do Processamento</h2>
        
        <div class="tabs">
          <div class="tab active" data-tab="mapping">Mapeamento de Campos</div>
          <div class="tab" data-tab="json">JSON Padronizado</div>
        </div>
        
        <div class="tab-content active" id="mapping-tab">
          <p>A IA identificou os seguintes campos na sua planilha e os mapeou para o padrão do sistema:</p>
          <div id="mapping-container"></div>
        </div>
        
        <div class="tab-content" id="json-tab">
          <p>Dados padronizados gerados a partir da sua planilha:</p>
          <div class="json-viewer" id="json-viewer"></div>
        </div>
        
        <div class="action-buttons" id="action-buttons">
          <!-- Botões serão adicionados dinamicamente -->
        </div>
      </div>

      <div class="cards-grid">
        <div class="card">
          <div class="card-title">Planilhas Enviadas</div>
          <div class="card-value">24</div>
          <div class="card-change positive">▲ +3 esta semana</div>
        </div>

        <div class="card">
          <div class="card-title">Processados com Sucesso</div>
          <div class="card-value">22</div>
          <div class="card-change positive">▲ +5%</div>
        </div>

        <div class="card">
          <div class="card-title">Erros de Processamento</div>
          <div class="card-value">2</div>
          <div class="card-change negative">▼ 1 hoje</div>
        </div>

        <div class="card">
          <div class="card-title">Último Envio</div>
          <div class="card-value">Hoje</div>
          <div class="card-change" style="color:#999;">10:45 AM</div>
        </div>
      </div>

      <div class="assistant-box">
        <h2>Assistente IA</h2>
        <p>Precisa de ajuda com suas planilhas? Converse com nossa IA para obter insights e assistência.</p>
        <button class="btn">💬 Iniciar Conversa</button>
      </div>
    </section>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const uploadArea = document.getElementById('upload-area');
      const fileInput = document.getElementById('file-input');
      const uploadButton = document.getElementById('upload-button');
      const uploadStatus = document.getElementById('upload-status');
      const progressContainer = document.getElementById('progress-container');
      const progressBar = document.getElementById('progress-bar');
      const resultsSection = document.getElementById('results-section');
      const jsonViewer = document.getElementById('json-viewer');
      const actionButtons = document.getElementById('action-buttons');
      const mappingContainer = document.getElementById('mapping-container');
      
      // Tabs
      const tabs = document.querySelectorAll('.tab');
      const tabContents = document.querySelectorAll('.tab-content');
      
      tabs.forEach(tab => {
        tab.addEventListener('click', () => {
          const tabId = tab.getAttribute('data-tab');
          
          // Remover classe active de todas as tabs e conteúdos
          tabs.forEach(t => t.classList.remove('active'));
          tabContents.forEach(c => c.classList.remove('active'));
          
          // Adicionar classe active na tab e conteúdo selecionados
          tab.classList.add('active');
          document.getElementById(tabId + '-tab').classList.add('active');
        });
      });
      
      // Evento de clique no botão de upload
      uploadButton.addEventListener('click', () => {
        fileInput.click();
      });
      
      // Evento de clique na área de upload
      uploadArea.addEventListener('click', () => {
        fileInput.click();
      });
      
      // Evento de seleção de arquivo
      fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
          handleFile(fileInput.files[0]);
        }
      });
      
      // Eventos de arrastar e soltar
      ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
      });
      
      function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
      }
      
      ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => {
          uploadArea.classList.add('dragover');
        }, false);
      });
      
      ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => {
          uploadArea.classList.remove('dragover');
        }, false);
      });
      
      uploadArea.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
          handleFile(files[0]);
        }
      }, false);
      
      // Função para processar o arquivo
      function handleFile(file) {
        // Verificar se é um arquivo Excel
        if (!file.name.match(/\.(xlsx|xls)$/)) {
          showStatus('Por favor, selecione um arquivo Excel (.xlsx ou .xls)', 'error');
          return;
        }
        
        // Mostrar barra de progresso
        progressContainer.style.display = 'block';
        progressBar.style.width = '0%';
        uploadStatus.style.display = 'none';
        resultsSection.style.display = 'none';
        actionButtons.innerHTML = '';
        
        // Preparar FormData
        const formData = new FormData();
        formData.append('excel_file', file);
        
        // Enviar arquivo via AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'subir.php', true);
        
        xhr.upload.onprogress = (e) => {
          if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            progressBar.style.width = percent + '%';
          }
        };
        
        xhr.onload = () => {
          if (xhr.status === 200) {
            try {
              const response = JSON.parse(xhr.responseText);
              if (response.success) {
                showStatus(response.message, 'success');
                
                // Exibir resultados da API e JSON gerado
                if (response.api_data && response.json_data) {
                  // Exibir mapeamento
                  displayMapping(response.api_data.data.mapping);
                  
                  // Exibir JSON padronizado
                  jsonViewer.textContent = JSON.stringify(response.json_data.data, null, 2);
                  resultsSection.style.display = 'block';
                  
                  // Adicionar botão de download
                  const downloadButton = document.createElement('a');
                  downloadButton.textContent = 'Baixar JSON';
                  downloadButton.href = response.json_data.file_path;
                  downloadButton.download = 'dados_geral.json';
                  downloadButton.className = 'btn';
                  
                  actionButtons.appendChild(downloadButton);
                }
              } else {
                showStatus(response.message, 'error');
              }
            } catch (e) {
              showStatus('Erro ao processar resposta do servidor', 'error');
            }
          } else {
            showStatus('Erro no servidor: ' + xhr.status, 'error');
          }
          
          // Esconder barra de progresso após 1 segundo
          setTimeout(() => {
            progressContainer.style.display = 'none';
          }, 1000);
        };
        
        xhr.onerror = () => {
          showStatus('Erro de rede. Tente novamente.', 'error');
          progressContainer.style.display = 'none';
        };
        
        xhr.send(formData);
      }
      
      // Função para exibir o mapeamento de campos
      function displayMapping(mapping) {
        let tableHTML = '<table class="mapping-table"><thead><tr><th>Campo Original</th><th>Campo Padrão</th></tr></thead><tbody>';
        
        for (const [original, standard] of Object.entries(mapping)) {
          if (standard) { // Apenas exibir campos mapeados
            tableHTML += `<tr><td>${original}</td><td>${standard}</td></tr>`;
          }
        }
        
        tableHTML += '</tbody></table>';
        mappingContainer.innerHTML = tableHTML;
      }
      
      // Função para mostrar status
      function showStatus(message, type) {
        uploadStatus.textContent = message;
        uploadStatus.className = 'upload-status ' + type;
        uploadStatus.style.display = 'block';
      }
    });
  </script>
</body>
</html>