<?php
/*****************************************************
 * dashboard_bbaib.php
 * Versão sem banco de dados
 * Lê os arquivos JSON:
 *   - catalogo_prefixos.json
 *   - catalogo_sap_bbts.json
 *****************************************************/

header('Content-Type: text/html; charset=utf-8');

// ===== Carregar JSON =====
function carregarJSON($arquivo) {
    if (!file_exists($arquivo)) {
        echo "<h3 style='color:red'>❌ Arquivo não encontrado: {$arquivo}</h3>";
        return [];
    }
    $conteudo = file_get_contents($arquivo);
    $dados = json_decode($conteudo, true);
    if (!is_array($dados)) {
        echo "<h3 style='color:red'>❌ Erro ao ler JSON: {$arquivo}</h3>";
        return [];
    }
    return $dados;
}

$prefixos = carregarJSON(__DIR__ . "../dados/catalogo_prefixos.json");
$catalogo = carregarJSON(__DIR__ . "../dados/catalogo_sap_bbts.json");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Catálogo BBTS - Descrições</title>

<style>
body {
  font-family: "Segoe UI", Arial, sans-serif;
  background: #f3f4f6;
  margin: 0;
  padding: 0;
  color: #111827;
}
header {
  background: #1e3a8a;
  color: white;
  text-align: center;
  padding: 16px;
  font-size: 22px;
  font-weight: 600;
}
.container {
  max-width: 1100px;
  margin: 30px auto;
  padding: 20px;
  background: white;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0,0,0,.15);
}
h2 {
  border-left: 5px solid #2563eb;
  padding-left: 10px;
  color: #1e3a8a;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
}
th, td {
  border: 1px solid #ddd;
  padding: 8px 10px;
  text-align: left;
  font-size: 14px;
}
th {
  background: #2563eb;
  color: white;
}
tr:nth-child(even) { background: #f9fafb; }
tr:hover { background: #eff6ff; }
footer {
  text-align: center;
  padding: 20px;
  font-size: 13px;
  color: #6b7280;
}
@media (max-width: 768px) {
  table, thead, tbody, th, td, tr {
    display: block;
  }
  thead tr { display: none; }
  td {
    padding: 10px;
    border: none;
    border-bottom: 1px solid #ddd;
  }
  td::before {
    content: attr(data-label);
    font-weight: 700;
    color: #1e3a8a;
    display: block;
  }
}
</style>
</head>
<body>
<header>📘 Catálogo BBTS - Descrições e Prefixos</header>

<div class="container">
  <h2>📗 Prefixos de Materiais</h2>
  <table>
    <thead>
      <tr>
        <th>Prefixo</th>
        <th>Descrição</th>
        <th>Função Operacional</th>
        <th>Exemplo de Uso</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($prefixos as $p): ?>
      <tr>
        <td data-label="Prefixo"><?= htmlspecialchars($p['prefixo'] ?? '') ?></td>
        <td data-label="Descrição"><?= htmlspecialchars($p['descricao'] ?? '') ?></td>
        <td data-label="Função"><?= htmlspecialchars($p['funcao_operacional'] ?? '') ?></td>
        <td data-label="Exemplo"><?= htmlspecialchars($p['exemplo_uso'] ?? '') ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="container">
  <h2>⚙️ Transações SAP e Prefixos Técnicos</h2>
  <table>
    <thead>
      <tr>
        <th>Código</th>
        <th>Tipo</th>
        <th>Descrição Funcional</th>
        <th>Finalidade</th>
        <th>Exemplo Prático</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($catalogo as $c): ?>
      <tr>
        <td data-label="Código"><?= htmlspecialchars($c['codigo'] ?? '') ?></td>
        <td data-label="Tipo"><?= htmlspecialchars($c['tipo'] ?? '') ?></td>
        <td data-label="Descrição"><?= htmlspecialchars($c['descricao_funcional'] ?? '') ?></td>
        <td data-label="Finalidade"><?= htmlspecialchars($c['finalidade'] ?? '') ?></td>
        <td data-label="Exemplo"><?= htmlspecialchars($c['exemplo_pratico'] ?? '') ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<footer>Desenvolvido por Tiago · Sistema BB-AIB · <?= date('Y') ?></footer>

</body>
</html>
