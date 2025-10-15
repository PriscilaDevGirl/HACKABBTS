import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { AlertTriangle, TrendingDown, Clock, CheckCircle } from "lucide-react";
import { Badge } from "@/components/ui/badge";

interface Alert {
  id: number;
  type: "warning" | "critical" | "info" | "success";
  title: string;
  description: string;
  time: string;
}

const alerts: Alert[] = [
  {
    id: 1,
    type: "critical",
    title: "Estoque Crítico",
    description: "Material X123 abaixo do nível mínimo (5 unidades)",
    time: "Há 15 min"
  },
  {
    id: 2,
    type: "warning",
    title: "Atraso Previsto",
    description: "Contrato Y456 pode atrasar em 3 dias",
    time: "Há 1 hora"
  },
  {
    id: 3,
    type: "success",
    title: "Economia Identificada",
    description: "Oportunidade de redução de 12% no fornecedor ABC",
    time: "Há 2 horas"
  }
];

export const AlertsPanel = () => {
  const getAlertIcon = (type: Alert["type"]) => {
    switch (type) {
      case "critical":
        return <AlertTriangle className="w-4 h-4" />;
      case "warning":
        return <Clock className="w-4 h-4" />;
      case "success":
        return <CheckCircle className="w-4 h-4" />;
      default:
        return <TrendingDown className="w-4 h-4" />;
    }
  };

  const getAlertVariant = (type: Alert["type"]) => {
    switch (type) {
      case "critical":
        return "destructive";
      case "warning":
        return "secondary";
      case "success":
        return "default";
      default:
        return "outline";
    }
  };

  return (
    <Card className="bg-gradient-card border-border shadow-md">
      <CardHeader>
        <CardTitle className="text-foreground">Alertas Inteligentes</CardTitle>
      </CardHeader>
      <CardContent>
        <div className="space-y-4">
          {alerts.map((alert) => (
            <div
              key={alert.id}
              className="p-4 rounded-lg bg-background border border-border hover:shadow-md transition-all duration-200"
            >
              <div className="flex items-start gap-3">
                <Badge variant={getAlertVariant(alert.type)} className="mt-1">
                  {getAlertIcon(alert.type)}
                </Badge>
                <div className="flex-1">
                  <div className="flex items-start justify-between mb-1">
                    <h4 className="font-semibold text-sm text-foreground">{alert.title}</h4>
                    <span className="text-xs text-muted-foreground">{alert.time}</span>
                  </div>
                  <p className="text-sm text-muted-foreground">{alert.description}</p>
                </div>
              </div>
            </div>
          ))}
        </div>
      </CardContent>
    </Card>
  );
};
