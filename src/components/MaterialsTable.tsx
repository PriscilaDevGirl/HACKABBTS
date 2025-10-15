import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { MaterialData } from "@/hooks/useHackathonData";
import { Badge } from "@/components/ui/badge";

interface MaterialsTableProps {
  data: MaterialData[];
}

export const MaterialsTable = ({ data }: MaterialsTableProps) => {
  const displayData = data.slice(0, 10);

  return (
    <Card className="bg-gradient-card border-border shadow-md">
      <CardHeader>
        <CardTitle className="text-foreground">Materiais em Estoque (Top 10)</CardTitle>
      </CardHeader>
      <CardContent>
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Código</TableHead>
              <TableHead>Classe ABC</TableHead>
              <TableHead>Saldo Manutenção</TableHead>
              <TableHead>Providenciar Compras</TableHead>
              <TableHead>Em Trânsito</TableHead>
              <TableHead>CMM</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {displayData.map((item, index) => (
              <TableRow key={index}>
                <TableCell className="font-medium">{item.codigo}</TableCell>
                <TableCell>
                  <Badge variant={item.abc === 'A' ? 'destructive' : item.abc === 'B' ? 'default' : 'secondary'}>
                    {item.abc}
                  </Badge>
                </TableCell>
                <TableCell>{item.saldo_manut}</TableCell>
                <TableCell>{item.provid_compras}</TableCell>
                <TableCell>{item.transito_manut}</TableCell>
                <TableCell>{item.cmm.toFixed(2)}</TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </CardContent>
    </Card>
  );
};
