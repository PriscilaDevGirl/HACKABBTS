import { useState, useMemo } from "react";
import { Sidebar } from "@/components/Sidebar";
import { DashboardHeader } from "@/components/DashboardHeader";
import { KPICard } from "@/components/KPICard";
import { ChatInterface } from "@/components/ChatInterface";
import { AlertsPanel } from "@/components/AlertsPanel";
import { MetricsChart } from "@/components/MetricsChart";
import { MaterialsTable } from "@/components/MaterialsTable";
import { useHackathonData } from "@/hooks/useHackathonData";
import { DollarSign, Package, TrendingUp, Clock } from "lucide-react";

const Index = () => {
  const [activeTab, setActiveTab] = useState("dashboard");
  const { data, loading } = useHackathonData();

  const kpis = useMemo(() => {
    if (!data.length) return null;
    
    const totalMateriais = data.length;
    const totalEstoque = data.reduce((sum, item) => sum + item.saldo_manut, 0);
    const totalCompras = data.reduce((sum, item) => sum + item.provid_compras, 0);
    const totalTransito = data.reduce((sum, item) => sum + item.transito_manut, 0);
    const custoEstimado = totalEstoque * 850; // Estimativa de R$ 850 por item
    
    return {
      custoTotal: `R$ ${(custoEstimado / 1000000).toFixed(1)}M`,
      materiaisEstoque: totalMateriais.toLocaleString('pt-BR'),
      economiaIdentificada: `R$ ${((totalCompras * 850 * 0.1) / 1000).toFixed(0)}K`,
      contratosAtivos: "38"
    };
  }, [data]);

  return (
    <div className="flex min-h-screen bg-background">
      <Sidebar activeTab={activeTab} onTabChange={setActiveTab} />
      
      <div className="flex-1 flex flex-col">
        <DashboardHeader />
        
        <main className="flex-1 p-8 overflow-auto">
          <div className="max-w-7xl mx-auto space-y-8">
            {/* Welcome Section */}
            <div>
              <h2 className="text-3xl font-bold text-foreground mb-2">
                Bem-vindo ao BBIAB
              </h2>
              <p className="text-muted-foreground">
                Plataforma de Inteligência Operacional com IA
              </p>
            </div>

            {/* KPI Cards */}
            {loading ? (
              <div className="text-center text-muted-foreground">Carregando dados...</div>
            ) : kpis ? (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <KPICard
                  title="Custo Total Estimado"
                  value={kpis.custoTotal}
                  change="+12%"
                  changeType="positive"
                  icon={DollarSign}
                />
                <KPICard
                  title="Materiais em Estoque"
                  value={kpis.materiaisEstoque}
                  change="-3%"
                  changeType="negative"
                  icon={Package}
                />
                <KPICard
                  title="Economia Identificada"
                  value={kpis.economiaIdentificada}
                  change="+8%"
                  changeType="positive"
                  icon={TrendingUp}
                />
                <KPICard
                  title="Contratos Ativos"
                  value={kpis.contratosAtivos}
                  change="0%"
                  changeType="neutral"
                  icon={Clock}
                />
              </div>
            ) : null}

            {/* Main Content Grid */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
              {/* Chart - Takes 2 columns */}
              <div className="lg:col-span-2">
                <MetricsChart />
              </div>

              {/* Alerts Panel - Takes 1 column */}
              <div>
                <AlertsPanel />
              </div>
            </div>

            {/* Materials Table */}
            {!loading && data.length > 0 && (
              <MaterialsTable data={data} />
            )}

            {/* Chat Interface */}
            <div className="h-[500px]">
              <ChatInterface />
            </div>
          </div>
        </main>
      </div>
    </div>
  );
};

export default Index;
