import { useState, useEffect } from 'react';

export interface MaterialData {
  codigo: string;
  abc: string;
  tipo: number;
  saldo_manut: number;
  provid_compras: number;
  recebimento_esperado: number;
  transito_manut: number;
  stage_manut: number;
  recepcao_manut: number;
  pendente_ri: number;
  pecas_teste_kit: number;
  pecas_teste: number;
  fornecedor_reparo: number;
  laboratorio: number;
  wr: number;
  wrcr: number;
  stage_wr: number;
  cmm: number;
  coef_perda: number;
}

export const useHackathonData = () => {
  const [data, setData] = useState<MaterialData[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const loadData = async () => {
      try {
        const response = await fetch('/data/dados_hackathon.csv');
        const text = await response.text();
        
        const lines = text.split('\n');
        const headers = lines[0].split(';');
        
        const parsedData: MaterialData[] = lines.slice(1)
          .filter(line => line.trim())
          .map(line => {
            const values = line.split(';');
            return {
              codigo: values[0]?.replace('﻿', ''),
              abc: values[1],
              tipo: Number(values[2]),
              saldo_manut: Number(values[3]),
              provid_compras: Number(values[4]),
              recebimento_esperado: Number(values[5]),
              transito_manut: Number(values[6]),
              stage_manut: Number(values[7]),
              recepcao_manut: Number(values[8]),
              pendente_ri: Number(values[9]),
              pecas_teste_kit: Number(values[10]),
              pecas_teste: Number(values[11]),
              fornecedor_reparo: Number(values[12]),
              laboratorio: Number(values[13]),
              wr: Number(values[14]),
              wrcr: Number(values[15]),
              stage_wr: Number(values[16]),
              cmm: Number(values[17]),
              coef_perda: Number(values[18]),
            };
          });
        
        setData(parsedData);
      } catch (error) {
        console.error('Erro ao carregar dados:', error);
      } finally {
        setLoading(false);
      }
    };

    loadData();
  }, []);

  return { data, loading };
};
