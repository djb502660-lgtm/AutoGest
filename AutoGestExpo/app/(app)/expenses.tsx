import { Card, Empty, Loading, Muted, ScrollScreen } from '../../src/components/ui';
import { api } from '../../src/lib/api';
import { colors } from '../../src/lib/theme';
import { useQuery } from '@tanstack/react-query';
import { StyleSheet, Text } from 'react-native';

export default function ExpensesScreen() {
  const query = useQuery({
    queryKey: ['expenses'],
    queryFn: async () => (await api.get('/expenses')).data,
  });

  if (query.isLoading) {
    return <Loading />;
  }

  if (query.isError) {
    return (
      <ScrollScreen>
        <Empty>El resumen de gastos aún no está disponible en la API de producción.</Empty>
      </ScrollScreen>
    );
  }

  const data = query.data ?? { total: 0, categories: [], recent: [] };

  return (
    <ScrollScreen>
      <Card>
        <Muted>Total 12 meses</Muted>
        <Text style={styles.total}>{`$${Number(data.total ?? 0).toFixed(2)}`}</Text>
      </Card>
      {(data.categories ?? []).map((cat: { name: string; amount: number }) => (
        <Card key={cat.name}>
          <Text style={styles.cat}>{cat.name}</Text>
          <Muted>{`$${Number(cat.amount).toFixed(2)}`}</Muted>
        </Card>
      ))}
      {(data.recent ?? []).length === 0 ? <Empty>Sin gastos recientes.</Empty> : null}
    </ScrollScreen>
  );
}

const styles = StyleSheet.create({
  total: { fontSize: 32, fontWeight: '800', color: colors.text, letterSpacing: -0.6 },
  cat: { fontWeight: '700', color: colors.text, fontSize: 16 },
});
