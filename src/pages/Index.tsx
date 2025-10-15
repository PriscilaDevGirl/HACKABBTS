import { useState } from "react";
import { Sidebar } from "@/components/Sidebar";
import { DashboardHeader } from "@/components/DashboardHeader";
import { KPICard } from "@/components/KPICard";
import { ChatInterface } from "@/components/ChatInterface";
import { AlertsPanel } from "@/components/AlertsPanel";
import { MetricsChart } from "@/components/MetricsChart";
import { DollarSign, Package, TrendingUp, Clock } from "lucide-react";

const Index = () => {
  const [activeTab, setActiveTab] = useState("dashboard");

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
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <KPICard
                title="Custo Total Mensal"
                value="R$ 6.8M"
                change="+12%"
                changeType="positive"
                icon={DollarSign}
              />
              <KPICard
                title="Materiais em Estoque"
                value="1,247"
                change="-3%"
                changeType="negative"
                icon={Package}
              />
              <KPICard
                title="Economia Identificada"
                value="R$ 680K"
                change="+8%"
                changeType="positive"
                icon={TrendingUp}
              />
              <KPICard
                title="Contratos Ativos"
                value="38"
                change="0%"
                changeType="neutral"
                icon={Clock}
              />
            </div>

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
