<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BBIAB - Inteligência Operacional</title>
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

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background-color: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        .card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 14px;
            color: #999;
            font-weight: 600;
        }

        .card-icon {
            font-size: 24px;
            color: #4169e1;
        }

        .card-value {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .card-change {
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .positive {
            color: #4caf50;
        }

        .negative {
            color: #ff6b6b;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .chart-card {
            background-color: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .chart-title {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .chart-placeholder {
            height: 300px;
            background: linear-gradient(180deg, rgba(65,105,225,0.1) 0%, rgba(65,105,225,0.02) 100%);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ccc;
        }

        .alerts-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .alert-item {
            display: flex;
            gap: 12px;
            padding: 12px;
            background-color: #fff8f8;
            border-radius: 8px;
            border-left: 3px solid #ff4444;
        }

        .alert-icon {
            flex-shrink: 0;
            width: 24px;
            height: 24px;
            background-color: #ff4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
        }

        .alert-content {
            flex: 1;
        }

        .alert-title {
            font-size: 13px;
            font-weight: 600;
            color: #333;
            margin-bottom: 2px;
        }

        .alert-text {
            font-size: 12px;
            color: #999;
        }

        .alert-time {
            font-size: 12px;
            color: #999;
            text-align: right;
        }

        @media (max-width: 1200px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }

            .cards-grid {
                grid-template-columns: repeat(2, 1fr);
            }
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

            .cards-grid {
                grid-template-columns: 1fr;
            }

            .content {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="logo">BBIAR</div>
        <div class="sidebar-title">Inteligência Operacional</div>
        <nav class="sidebar-menu">
            <div class="sidebar-item active">
                <span>📊</span>
                <span>Dashboard</span>
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
                <input type="text" placeholder="Buscar materiais, contratos, insights...">
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
                <h1>Bem-vindo ao BBIAB</h1>
                <p>Plataforma de Inteligência Operacional com IA</p>
            </div>

            <div class="cards-grid">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Custo Total Estimado</div>
                        <div class="card-icon">💰</div>
                    </div>
                    <div class="card-value">R$ 190.9M</div>
                    <div class="card-change positive">
                        <span>▲</span> +12%
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Materiais em Estoque</div>
                        <div class="card-icon">📦</div>
                    </div>
                    <div class="card-value">5.000</div>
                    <div class="card-change negative">
                        <span>▼</span> -3%
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Economia Identificada</div>
                        <div class="card-icon">📈</div>
                    </div>
                    <div class="card-value">R$ 6557K</div>
                    <div class="card-change positive">
                        <span>▲</span> +8%
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Contratos Ativos</div>
                        <div class="card-icon">⏱️</div>
                    </div>
                    <div class="card-value">38</div>
                    <div class="card-change" style="color: #999;">0%</div>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-card">
                    <div class="chart-title">Tendência de Custos e Economias</div>
                    <div class="chart-placeholder">
                        <svg width="100%" height="250" viewBox="0 0 400 250">
                            <polyline points="20,200 80,190 140,170 200,120 260,80 320,40 380,30" 
                                    fill="none" stroke="#4169e1" stroke-width="3"/>
                            <circle cx="380" cy="30" r="5" fill="#4169e1"/>
                        </svg>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="chart-title">Alertas Inteligentes</div>
                    <div class="alerts-list">
                        <div class="alert-item">
                            <div class="alert-icon">⚠️</div>
                            <div style="flex: 1;">
                                <div class="alert-title">Estoque Crítico</div>
                                <div class="alert-text">Material X123 abaixo do nível de segurança</div>
                            </div>
                            <div class="alert-time">Há 15 min</div>
                        </div>
                        <div class="alert-item">
                            <div class="alert-icon">⚠️</div>
                            <div style="flex: 1;">
                                <div class="alert-title">Contrato Vencendo</div>
                                <div class="alert-text">Fornecedor ABC vence em 5 dias</div>
                            </div>
                            <div class="alert-time">Há 1 hora</div>
                        </div>
                        <div class="alert-item">
                            <div class="alert-icon">⚠️</div>
                            <div style="flex: 1;">
                                <div class="alert-title">Anomalia de Preço</div>
                                <div class="alert-text">Variação de 25% detectada</div>
                            </div>
                            <div class="alert-time">Há 3 horas</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>