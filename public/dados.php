<?php
/*****************************************************
 * dashboard_bbaib.php
 * Painel único: lista + gráficos (responsivo)
 * DB: bbaib | user: root | senha: (vazia)
 *****************************************************/

header('Content-Type: text/html; charset=utf-8');

/* ========= CONEXÃO ========= */
require_once __DIR__ . '/../config.php';
$mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_errno) {
    http_response_code(500);
    echo "<h1>Erro de conexão</h1><pre>{$mysqli->connect_error}</pre>";
    exit;
}
$mysqli->set_charset("utf8mb4");

// ===== Consultas =====
$prefixos = $mysqli->query("SELECT prefixo, descricao, funcao_operacional, exemplo_uso FROM catalogo_prefixos ORDER BY prefixo ASC");
$catalogo = $mysqli->query("SELECT codigo, tipo, descricao_funcional, finalidade, exemplo_pratico FROM catalogo_sap_bbts ORDER BY codigo ASC");
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
  thead tr {
    display: none;
  }
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
      <?php while($p = $prefixos->fetch_assoc()): ?>
      <tr>
        <td data-label="Prefixo"><?= htmlspecialchars($p['prefixo']) ?></td>
        <td data-label="Descrição"><?= htmlspecialchars($p['descricao']) ?></td>
        <td data-label="Função"><?= htmlspecialchars($p['funcao_operacional']) ?></td>
        <td data-label="Exemplo"><?= htmlspecialchars($p['exemplo_uso']) ?></td>
      </tr>
      <?php endwhile; ?>
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
      <?php while($c = $catalogo->fetch_assoc()): ?>
      <tr>
        <td data-label="Código"><?= htmlspecialchars($c['codigo']) ?></td>
        <td data-label="Tipo"><?= htmlspecialchars($c['tipo']) ?></td>
        <td data-label="Descrição"><?= htmlspecialchars($c['descricao_funcional']) ?></td>
        <td data-label="Finalidade"><?= htmlspecialchars($c['finalidade']) ?></td>
        <td data-label="Exemplo"><?= htmlspecialchars($c['exemplo_pratico']) ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<footer>Desenvolvido por Tiago · Sistema BB-AIB · <?= date('Y') ?></footer>

</body>
</html>
