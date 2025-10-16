<?php
/*****************************************************
 * pag2.php
 * Versão com cruzamento de dados JSON
 * Lê os arquivos JSON:
 *   - ../dados/dados_geral.json
 *   - ../dados/catalogo_prefixos.json
 *   - ../dados/catalogo_sap_bbts.json
 *****************************************************/

header('Content-Type: text/html; charset=utf-8');

/* ===== Função para carregar JSON ===== */
function carregarJSON($arquivo) {
    if (!file_exists($arquivo)) {
        echo "<div style='color:red; padding:10px; margin:10px; border:1px solid red; border-radius:5px;'>❌ Arquivo não encontrado: {$arquivo}</div>";
        return [];
    }

    $conteudo = file_get_contents($arquivo);
    if ($conteudo === false) {
        echo "<div style='color:red; padding:10px; margin:10px; border:1px solid red; border-radius:5px;'>❌ Erro ao ler arquivo: {$arquivo}</div>";
        return [];
    }

    $dados = json_decode($conteudo, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "<div style='color:red; padding:10px; margin:10px; border:1px solid red; border-radius:5px;'>❌ Erro ao decodificar JSON ({$arquivo}): " . json_last_error_msg() . "</div>";
        return [];
    }

    return $dados;
}

/* ===== Caminho base ===== */
 $basePath = __DIR__ . '/public/dados';
if (!is_dir($basePath)) {
    echo "<div style='color:red; padding:10px; margin:10px; border:1px solid red; border-radius:5px;'>❌ Diretório de dados não encontrado: {$basePath}</div>";
    exit;
}

/* ===== Carregar arquivos ===== */
 $dadosGerais = carregarJSON($basePath . '/dados_geral.json');
 $prefixos_json = carregarJSON( 'dados/catalogo_prefixos.json');
 $catalogo_json = carregarJSON( 'dados/catalogo_sap_bbts.json');

// Extrair arrays dos catálogos
 $prefixos = $prefixos_json['catalogo_prefixos'] ?? $prefixos_json;
 $catalogo = $catalogo_json['catalogo_sap_bbts'] ?? $catalogo_json;

/* ===== Verificar se os dados foram carregados ===== */
if (empty($dadosGerais)) {
    echo "<div style='color:orange; padding:10px; margin:10px; border:1px solid orange; border-radius:5px;'>⚠️ Nenhum dado encontrado em dados_gerais.json</div>";
}
if (empty($prefixos)) {
    echo "<div style='color:orange; padding:10px; margin:10px; border:1px solid orange; border-radius:5px;'>⚠️ Nenhum prefixo encontrado em catalogo_prefixos.json</div>";
}
if (empty($catalogo)) {
    echo "<div style='color:orange; padding:10px; margin:10px; border:1px solid orange; border-radius:5px;'>⚠️ Nenhum catálogo encontrado em catalogo_sap_bbts.json</div>";
}

/* ===== Criar índices para busca rápida ===== */
 $prefixosIndex = [];
foreach ($prefixos as $prefixo) {
    if (isset($prefixo['prefixo'])) {
        $prefixosIndex[$prefixo['prefixo']] = $prefixo;
    }
}

 $catalogoIndex = [];
foreach ($catalogo as $item) {
    if (isset($item['codigo'])) {
        $catalogoIndex[$item['codigo']] = $item;
    }
}

/* ===== Cruzar dados ===== */
 $dadosCombinados = [];
foreach ($dadosGerais as $item) {
    $novoItem = $item;
    
    // Buscar descrição do prefixo
    $codigo = $item['codigo'] ?? '';
    if (!empty($codigo)) {
        // Extrair prefixo (primeiros 3 caracteres)
        $prefixoCodigo = substr($codigo, 0, 3);
        if (isset($prefixosIndex[$prefixoCodigo])) {
            $novoItem['prefixo_descricao'] = $prefixosIndex[$prefixoCodigo]['descricao'] ?? '';
            $novoItem['prefixo_funcao'] = $prefixosIndex[$prefixoCodigo]['funcao_operacional'] ?? '';
        } else {
            $novoItem['prefixo_descricao'] = '';
            $novoItem['prefixo_funcao'] = '';
        }
    } else {
        $novoItem['prefixo_descricao'] = '';
        $novoItem['prefixo_funcao'] = '';
    }
    
    // Buscar descrição do tipo
    $tipo = $item['tipo'] ?? '';
    if (!empty($tipo) && isset($catalogoIndex[$tipo])) {
        $novoItem['tipo_descricao'] = $catalogoIndex[$tipo]['descricao_funcional'] ?? '';
        $novoItem['tipo_finalidade'] = $catalogoIndex[$tipo]['finalidade'] ?? '';
    } else {
        $novoItem['tipo_descricao'] = '';
        $novoItem['tipo_finalidade'] = '';
    }
    
    $dadosCombinados[] = $novoItem;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BBIAB - Estoque de Produtos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            background-color: #f5f5f5;
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 260px;
            background-color: #2d2d2d;
            color: #fff;
            padding: 20px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .logo {
            width: 50px;
            height: 40px;
            background-color: #fff;
            margin-bottom: 30px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #4169e1;
        }

        .sidebar-title {
            font-size: 12px;
            color: #999;
            margin-bottom: 20px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 10px;
            flex: 1;
        }

        .sidebar-item {
            padding: 12px 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
            color: #ccc;
        }

        .sidebar-item.active {
            background-color: #4169e1;
            color: #fff;
        }

        .sidebar-item:hover {
            background-color: #3a3a3a;
        }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid #444;
            font-size: 12px;
            color: #999;
        }

        .sidebar-footer p {
            margin-bottom: 5px;
        }

        .main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .header {
            background-color: #fff;
            border-bottom: 1px solid #e0e0e0;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .search-bar {
            flex: 1;
            max-width: 600px;
            background-color: #f5f5f5;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-bar input {
            flex: 1;
            border: none;
            background: none;
            outline: none;
            font-size: 14px;
            color: #999;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notification-icon {
            position: relative;
            cursor: pointer;
            font-size: 20px;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #ff4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .welcome-section {
            margin-bottom: 30px;
        }

        .welcome-section h1 {
            font-size: 32px;
            color: #333;
            margin-bottom: 5px;
        }

        .welcome-section p {
            color: #999;
            font-size: 14px;
        }

        .debug-info {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .error-box {
            background-color: #fff1f0;
            border: 1px solid #ffa39e;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            color: #a8071a;
        }

        .warning-box {
            background-color: #fffbe6;
            border: 1px solid #ffe58f;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            color: #ad6800;
        }

        .filters {
            background-color: #fff;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-group label {
            font-size: 14px;
            color: #666;
        }

        .filter-group select,
        .filter-group input {
            padding: 8px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            color: #333;
            background-color: #fff;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: #4169e1;
            color: white;
        }

        .btn-primary:hover {
            background-color: #3151c7;
        }

        .btn-secondary {
            background-color: #f5f5f5;
            color: #333;
            border: 1px solid #e0e0e0;
        }

        .btn-secondary:hover {
            background-color: #e9e9e9;
        }

        .products-table {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .table-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .table-actions {
            display: flex;
            gap: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #f9f9f9;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            color: #666;
            border-bottom: 1px solid #e0e0e0;
        }

        td {
            padding: 15px;
            font-size: 14px;
            color: #333;
            border-bottom: 1px solid #f0f0f0;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .product-code {
            font-weight: 600;
            color: #4169e1;
        }

        .status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-in-stock {
            background-color: #e6f7ee;
            color: #00a854;
        }

        .status-low-stock {
            background-color: #fff7e6;
            color: #fa8c16;
        }

        .status-out-of-stock {
            background-color: #fff1f0;
            color: #f5222d;
        }

        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .pagination-info {
            font-size: 14px;
            color: #666;
        }

        .pagination-controls {
            display: flex;
            gap: 5px;
        }

        .page-btn {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e0e0e0;
            background-color: #fff;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            color: #333;
            transition: all 0.3s;
        }

        .page-btn:hover {
            background-color: #f5f5f5;
        }

        .page-btn.active {
            background-color: #4169e1;
            color: white;
            border-color: #4169e1;
        }

        .loading {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
            font-size: 16px;
            color: #666;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

        .detail-row {
            display: flex;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .detail-label {
            font-weight: bold;
            width: 200px;
            color: #555;
        }

        .detail-value {
            flex: 1;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                padding: 10px;
            }

            .sidebar-title,
            .sidebar-item span:last-child,
            .sidebar-footer {
                display: none;
            }

            .filters {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-group {
                flex-direction: column;
                align-items: flex-start;
            }

            .content {
                padding: 15px;
            }

            .table-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .products-table {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="logo">BBIAR</div>
        <div class="sidebar-title">Inteligência Operacional</div>
        <nav class="sidebar-menu">
            <div class="sidebar-item">
                <span>📊</span>
                <span>Dashboard</span>
            </div>
            <div class="sidebar-item active">
                <span>📦</span>
                <span>Estoque</span>
            </div>
            <div class="sidebar-item">
                <span>📈</span>
                <span>Analytics</span>
            </div>
            <div class="sidebar-item">
                <span>💬</span>
                <span>Chat IA</span>
            </div>
            <div class="sidebar-item">
                <span>🔔</span>
                <span>Alertas</span>
            </div>
            <div class="sidebar-item">
                <span>📋</span>
                <span>Dados</span>
            </div>
            <div class="sidebar-item">
                <span>⚙️</span>
                <span>Configurações</span>
            </div>
        </nav>
        <div class="sidebar-footer">
            <p><strong>Versão 1.0</strong></p>
            <p>Powered by Azure AI</p>
        </div>
    </aside>

    <main class="main">
        <header class="header">
            <div class="search-bar">
                <span>🔍</span>
                <input type="text" id="search-input" placeholder="Buscar produtos por código, tipo ou laboratório...">
            </div>
            <div class="header-right">
                <div class="notification-icon">
                    🔔
                    <div class="notification-badge">3</div>
                </div>
                <div class="user-info">
                    <div>
                        <div style="font-size: 13px; font-weight: 600;">Admin BBTS</div>
                        <div style="font-size: 12px; color: #999;">Gerente de Operações</div>
                    </div>
                    <div class="user-avatar">O</div>
                </div>
            </div>
        </header>

        <section class="content">
            <div class="welcome-section">
                <h1>Estoque de Produtos</h1>
                <p>Gerenciamento de produtos do Banco do Brasil em estoque</p>
            </div>

            <div class="debug-info">
                <strong>Informações de Depuração:</strong><br>
                Diretório de dados: <?= htmlspecialchars($basePath) ?><br>
                Dados gerais carregados: <?= count($dadosGerais) ?> registros<br>
                Prefixos carregados: <?= count($prefixos) ?> registros<br>
                Catálogo SAP carregados: <?= count($catalogo) ?> registros<br>
                Dados combinados: <?= count($dadosCombinados) ?> registros
            </div>

            <?php if (empty($dadosCombinados)): ?>
            <div class="error-box">
                <strong>Atenção:</strong> Não foi possível carregar os dados. Verifique se os arquivos JSON existem e se estão no formato correto.
            </div>
            <?php endif; ?>

            <div class="filters">
                <div class="filter-group">
                    <label for="tipo-filter">Tipo:</label>
                    <select id="tipo-filter">
                        <option value="">Todos os tipos</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="laboratorio-filter">Laboratório:</label>
                    <select id="laboratorio-filter">
                        <option value="">Todos os laboratórios</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="status-filter">Status:</label>
                    <select id="status-filter">
                        <option value="">Todos os status</option>
                        <option value="in-stock">Em estoque</option>
                        <option value="low-stock">Estoque baixo</option>
                        <option value="out-of-stock">Sem estoque</option>
                    </select>
                </div>
                <div class="filter-group">
                    <button class="btn btn-primary" id="apply-filters">Aplicar Filtros</button>
                    <button class="btn btn-secondary" id="clear-filters">Limpar</button>
                </div>
            </div>

            <div class="products-table">
                <div class="table-header">
                    <div class="table-title">Produtos em Estoque</div>
                    <div class="table-actions">
                        <button class="btn btn-secondary" id="export-btn">Exportar</button>
                        <button class="btn btn-primary" id="refresh-btn">Atualizar Dados</button>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Tipo</th>
                            <th>Saldo Manutenção</th>
                            <th>CMM</th>
                            <th>Peças Teste Kit</th>
                            <th>Coef. Perda</th>
                            <th>Laboratório</th>
                            <th>WR</th>
                            <th>Stage WR</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody id="products-tbody">
                        <?php if (empty($dadosCombinados)): ?>
                            <tr>
                                <td colspan="11" style="text-align: center; color: #999;">Nenhum produto encontrado</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach (array_slice($dadosCombinados, 0, 10) as $product): ?>
                                <?php 
                                    // Determinar status
                                    $saldo = floatval($product['saldo_manut'] ?? 0);
                                    if ($saldo === 0) {
                                        $statusClass = 'status-out-of-stock';
                                        $statusText = 'Sem estoque';
                                    } elseif ($saldo < 10) {
                                        $statusClass = 'status-low-stock';
                                        $statusText = 'Estoque baixo';
                                    } else {
                                        $statusClass = 'status-in-stock';
                                        $statusText = 'Em estoque';
                                    }
                                ?>
                                <tr>
                                    <td class="product-code" title="<?= htmlspecialchars($product['prefixo_descricao'] ?? '') ?>"><?= htmlspecialchars($product['codigo'] ?? '-') ?></td>
                                    <td title="<?= htmlspecialchars($product['tipo_descricao'] ?? '') ?>"><?= htmlspecialchars($product['tipo'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($product['saldo_manut'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($product['cmm'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($product['pecas_teste_kit'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($product['coef_perda'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($product['laboratorio'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($product['wr'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($product['stage_wr'] ?? '-') ?></td>
                                    <td><span class="status <?= $statusClass ?>"><?= $statusText ?></span></td>
                                    <td>
                                        <button class="btn btn-secondary" style="padding: 5px 10px; font-size: 12px;" onclick="viewDetails('<?= htmlspecialchars($product['codigo'] ?? '') ?>')">Detalhes</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="pagination">
                    <div class="pagination-info" id="pagination-info">Mostrando 1 a <?= min(10, count($dadosCombinados)) ?> de <?= count($dadosCombinados) ?> produtos</div>
                    <div class="pagination-controls" id="pagination-controls">
                        <!-- Controles de paginação serão adicionados dinamicamente -->
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Modal para detalhes do produto -->
    <div id="product-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Detalhes do Produto</h2>
            <div id="product-details">
                <!-- Detalhes do produto serão inseridos aqui -->
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elementos do DOM
            const productsTableBody = document.getElementById('products-tbody');
            const paginationInfo = document.getElementById('pagination-info');
            const paginationControls = document.getElementById('pagination-controls');
            const searchInput = document.getElementById('search-input');
            const tipoFilter = document.getElementById('tipo-filter');
            const laboratorioFilter = document.getElementById('laboratorio-filter');
            const statusFilter = document.getElementById('status-filter');
            const applyFiltersBtn = document.getElementById('apply-filters');
            const clearFiltersBtn = document.getElementById('clear-filters');
            const exportBtn = document.getElementById('export-btn');
            const refreshBtn = document.getElementById('refresh-btn');
            const modal = document.getElementById('product-modal');
            const modalClose = document.querySelector('.close');
            const productDetails = document.getElementById('product-details');
            
            // Variáveis de estado
            let allProducts = <?= json_encode($dadosCombinados); ?>;
            let filteredProducts = [...allProducts];
            let currentPage = 1;
            const itemsPerPage = 10;
            
            // Inicializar filtros
            populateFilters();
            
            // Função para preencher os filtros
            function populateFilters() {
                // Obter valores únicos para os filtros
                const tipos = [...new Set(allProducts.map(p => p.tipo).filter(Boolean))];
                const laboratorios = [...new Set(allProducts.map(p => p.laboratorio).filter(Boolean))];
                
                // Preencher filtro de tipo
                tipoFilter.innerHTML = '<option value="">Todos os tipos</option>';
                tipos.forEach(tipo => {
                    const option = document.createElement('option');
                    option.value = tipo;
                    option.textContent = tipo;
                    tipoFilter.appendChild(option);
                });
                
                // Preencher filtro de laboratório
                laboratorioFilter.innerHTML = '<option value="">Todos os laboratórios</option>';
                laboratorios.forEach(laboratorio => {
                    const option = document.createElement('option');
                    option.value = laboratorio;
                    option.textContent = laboratorio;
                    laboratorioFilter.appendChild(option);
                });
            }
            
            // Função para determinar o status com base no saldo
            function getStatus(saldo) {
                if (saldo === null || saldo === undefined || saldo === 0) {
                    return { class: 'status-out-of-stock', text: 'Sem estoque' };
                } else if (saldo < 10) {
                    return { class: 'status-low-stock', text: 'Estoque baixo' };
                } else {
                    return { class: 'status-in-stock', text: 'Em estoque' };
                }
            }
            
            // Função para filtrar produtos
            function filterProducts() {
                const searchTerm = searchInput.value.toLowerCase();
                const tipoValue = tipoFilter.value;
                const laboratorioValue = laboratorioFilter.value;
                const statusValue = statusFilter.value;
                
                filteredProducts = allProducts.filter(product => {
                    // Filtro de busca
                    const matchesSearch = !searchTerm || 
                        (product.codigo && product.codigo.toLowerCase().includes(searchTerm)) ||
                        (product.tipo && product.tipo.toLowerCase().includes(searchTerm)) ||
                        (product.laboratorio && product.laboratorio.toLowerCase().includes(searchTerm)) ||
                        (product.prefixo_descricao && product.prefixo_descricao.toLowerCase().includes(searchTerm)) ||
                        (product.tipo_descricao && product.tipo_descricao.toLowerCase().includes(searchTerm));
                    
                    // Filtro de tipo
                    const matchesTipo = !tipoValue || product.tipo === tipoValue;
                    
                    // Filtro de laboratório
                    const matchesLaboratorio = !laboratorioValue || product.laboratorio === laboratorioValue;
                    
                    // Filtro de status
                    let matchesStatus = true;
                    if (statusValue) {
                        const saldo = parseFloat(product.saldo_manut) || 0;
                        const status = getStatus(saldo);
                        
                        if (statusValue === 'in-stock' && status.class !== 'status-in-stock') {
                            matchesStatus = false;
                        } else if (statusValue === 'low-stock' && status.class !== 'status-low-stock') {
                            matchesStatus = false;
                        } else if (statusValue === 'out-of-stock' && status.class !== 'status-out-of-stock') {
                            matchesStatus = false;
                        }
                    }
                    
                    return matchesSearch && matchesTipo && matchesLaboratorio && matchesStatus;
                });
                
                currentPage = 1; // Resetar para a primeira página
                displayProducts();
            }
            
            // Função para exibir os produtos
            function displayProducts() {
                // Calcular índices para paginação
                const startIndex = (currentPage - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;
                const paginatedProducts = filteredProducts.slice(startIndex, endIndex);
                
                // Limpar tabela
                productsTableBody.innerHTML = '';
                
                if (paginatedProducts.length === 0) {
                    productsTableBody.innerHTML = '<tr><td colspan="11" style="text-align: center;">Nenhum produto encontrado</td></tr>';
                    paginationInfo.textContent = 'Mostrando 0 de 0 produtos';
                    paginationControls.innerHTML = '';
                    return;
                }
                
                // Adicionar produtos à tabela
                paginatedProducts.forEach(product => {
                    const row = document.createElement('tr');
                    
                    // Determinar status
                    const saldo = parseFloat(product.saldo_manut) || 0;
                    const status = getStatus(saldo);
                    
                    // Preencher células
                    row.innerHTML = `
                        <td class="product-code" title="${product.prefixo_descricao || ''}">${product.codigo || '-'}</td>
                        <td title="${product.tipo_descricao || ''}">${product.tipo || '-'}</td>
                        <td>${product.saldo_manut !== null && product.saldo_manut !== undefined ? product.saldo_manut : '-'}</td>
                        <td>${product.cmm || '-'}</td>
                        <td>${product.pecas_teste_kit || '-'}</td>
                        <td>${product.coef_perda || '-'}</td>
                        <td>${product.laboratorio || '-'}</td>
                        <td>${product.wr || '-'}</td>
                        <td>${product.stage_wr || '-'}</td>
                        <td><span class="status ${status.class}">${status.text}</span></td>
                        <td>
                            <button class="btn btn-secondary" style="padding: 5px 10px; font-size: 12px;" onclick="viewDetails('${product.codigo}')">Detalhes</button>
                        </td>
                    `;
                    
                    productsTableBody.appendChild(row);
                });
                
                // Atualizar informações de paginação
                paginationInfo.textContent = `Mostrando ${startIndex + 1} a ${Math.min(endIndex, filteredProducts.length)} de ${filteredProducts.length} produtos`;
                
                // Criar controles de paginação
                setupPagination();
            }
            
            // Função para configurar a paginação
            function setupPagination() {
                paginationControls.innerHTML = '';
                
                const totalPages = Math.ceil(filteredProducts.length / itemsPerPage);
                
                if (totalPages <= 1) {
                    return; // Não precisa de paginação
                }
                
                // Botão Anterior
                const prevBtn = document.createElement('div');
                prevBtn.className = 'page-btn';
                prevBtn.textContent = '◀';
                prevBtn.disabled = currentPage === 1;
                prevBtn.addEventListener('click', () => {
                    if (currentPage > 1) {
                        currentPage--;
                        displayProducts();
                    }
                });
                paginationControls.appendChild(prevBtn);
                
                // Números das páginas
                const maxVisiblePages = 5;
                let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
                let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);
                
                if (endPage - startPage + 1 < maxVisiblePages) {
                    startPage = Math.max(1, endPage - maxVisiblePages + 1);
                }
                
                for (let i = startPage; i <= endPage; i++) {
                    const pageBtn = document.createElement('div');
                    pageBtn.className = `page-btn ${i === currentPage ? 'active' : ''}`;
                    pageBtn.textContent = i;
                    pageBtn.addEventListener('click', () => {
                        currentPage = i;
                        displayProducts();
                    });
                    paginationControls.appendChild(pageBtn);
                }
                
                // Botão Próximo
                const nextBtn = document.createElement('div');
                nextBtn.className = 'page-btn';
                nextBtn.textContent = '▶';
                nextBtn.disabled = currentPage === totalPages;
                nextBtn.addEventListener('click', () => {
                    if (currentPage < totalPages) {
                        currentPage++;
                        displayProducts();
                    }
                });
                paginationControls.appendChild(nextBtn);
            }
            
            // Função para ver detalhes do produto
            window.viewDetails = function(codigo) {
                const product = allProducts.find(p => p.codigo === codigo);
                if (product) {
                    // Preencher detalhes do produto no modal
                    productDetails.innerHTML = `
                        <div class="detail-row">
                            <div class="detail-label">Código:</div>
                            <div class="detail-value">${product.codigo || '-'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Tipo:</div>
                            <div class="detail-value">${product.tipo || '-'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Descrição do Tipo:</div>
                            <div class="detail-value">${product.tipo_descricao || '-'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Finalidade do Tipo:</div>
                            <div class="detail-value">${product.tipo_finalidade || '-'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Prefixo:</div>
                            <div class="detail-value">${product.codigo ? product.codigo.substring(0, 3) : '-'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Descrição do Prefixo:</div>
                            <div class="detail-value">${product.prefixo_descricao || '-'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Função do Prefixo:</div>
                            <div class="detail-value">${product.prefixo_funcao || '-'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Saldo Manutenção:</div>
                            <div class="detail-value">${product.saldo_manut !== null && product.saldo_manut !== undefined ? product.saldo_manut : '-'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">CMM:</div>
                            <div class="detail-value">${product.cmm || '-'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Peças Teste Kit:</div>
                            <div class="detail-value">${product.pecas_teste_kit || '-'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Coef. Perda:</div>
                            <div class="detail-value">${product.coef_perda || '-'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Laboratório:</div>
                            <div class="detail-value">${product.laboratorio || '-'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">WR:</div>
                            <div class="detail-value">${product.wr || '-'}</div>
                        </div>
                        <div class="detail-row">
                            <div class="detail-label">Stage WR:</div>
                            <div class="detail-value">${product.stage_wr || '-'}</div>
                        </div>
                    `;
                    
                    // Exibir modal
                    modal.style.display = 'block';
                }
            };
            
            // Função para exportar dados
            function exportData() {
                if (filteredProducts.length === 0) {
                    alert('Não há dados para exportar.');
                    return;
                }
                
                // Criar CSV
                let csv = 'Código,Tipo,Descrição do Tipo,Saldo Manutenção,CMM,Peças Teste Kit,Coef. Perda,Laboratório,WR,Stage WR,Prefixo,Descrição do Prefixo\n';
                
                filteredProducts.forEach(product => {
                    const prefixo = product.codigo ? product.codigo.substring(0, 3) : '';
                    csv += `"${product.codigo || ''}","${product.tipo || ''}","${product.tipo_descricao || ''}","${product.saldo_manut || ''}","${product.cmm || ''}","${product.pecas_teste_kit || ''}","${product.coef_perda || ''}","${product.laboratorio || ''}","${product.wr || ''}","${product.stage_wr || ''}","${prefixo}","${product.prefixo_descricao || ''}"\n`;
                });
                
                // Criar blob e download
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.setAttribute('href', url);
                link.setAttribute('download', 'estoque_produtos.csv');
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
            
            // Event Listeners
            searchInput.addEventListener('input', filterProducts);
            applyFiltersBtn.addEventListener('click', filterProducts);
            clearFiltersBtn.addEventListener('click', () => {
                searchInput.value = '';
                tipoFilter.value = '';
                laboratorioFilter.value = '';
                statusFilter.value = '';
                filterProducts();
            });
            exportBtn.addEventListener('click', exportData);
            refreshBtn.addEventListener('click', () => {
                location.reload();
            });
            
            // Fechar modal
            modalClose.addEventListener('click', () => {
                modal.style.display = 'none';
            });
            
            window.addEventListener('click', (event) => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>