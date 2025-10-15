import { LayoutDashboard, MessageSquare, Bell, Settings, BarChart3, Database } from "lucide-react";
import { cn } from "@/lib/utils";

interface SidebarProps {
  activeTab: string;
  onTabChange: (tab: string) => void;
}

const navigation = [
  { name: "Dashboard", icon: LayoutDashboard, id: "dashboard" },
  { name: "Analytics", icon: BarChart3, id: "analytics" },
  { name: "Chat IA", icon: MessageSquare, id: "chat" },
  { name: "Alertas", icon: Bell, id: "alerts" },
  { name: "Dados", icon: Database, id: "data" },
  { name: "Configurações", icon: Settings, id: "settings" },
];

export const Sidebar = ({ activeTab, onTabChange }: SidebarProps) => {
  return (
    <aside className="w-64 bg-sidebar border-r border-sidebar-border h-screen flex flex-col">
      <div className="p-6 border-b border-sidebar-border">
        <h1 className="text-2xl font-bold bg-gradient-primary bg-clip-text text-transparent">
          BBIAB
        </h1>
        <p className="text-xs text-sidebar-foreground mt-1">Inteligência Operacional</p>
      </div>
      
      <nav className="flex-1 p-4 space-y-1">
        {navigation.map((item) => {
          const Icon = item.icon;
          const isActive = activeTab === item.id;
          
          return (
            <button
              key={item.id}
              onClick={() => onTabChange(item.id)}
              className={cn(
                "w-full flex items-center gap-3 px-4 py-3 rounded-lg transition-all duration-200",
                isActive
                  ? "bg-sidebar-primary text-sidebar-primary-foreground shadow-glow"
                  : "text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground"
              )}
            >
              <Icon className="w-5 h-5" />
              <span className="font-medium">{item.name}</span>
            </button>
          );
        })}
      </nav>

      <div className="p-4 border-t border-sidebar-border">
        <div className="px-4 py-3 rounded-lg bg-sidebar-accent">
          <p className="text-xs font-semibold text-sidebar-accent-foreground mb-1">
            Versão 1.0
          </p>
          <p className="text-xs text-sidebar-foreground/70">
            Powered by Azure AI
          </p>
        </div>
      </div>
    </aside>
  );
};
