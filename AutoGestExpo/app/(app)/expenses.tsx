import { Card, Empty, Loading, Muted, ScrollScreen } from '../../src/components/ui';
import { api } from '../../src/lib/api';
import { colors } from '../../src/lib/theme';
import { useQuery } from '@tanstack/react-query';
import { StyleSheet, Text, View } from 'react-native';

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
      <ScrollScreen onRefresh={() => query.refetch()} refreshing={query.isRefetching}>
        <Empty icon="wallet-outline" title="Gastos no disponibles">
          El resumen aparecerá cuando haya servicios facturados en el taller.
        </Empty>
      </ScrollScreen>
    );
  }

  const data = query.data ?? { total: 0, categories: [], recent: [] };
  const total = Number(data.total ?? 0);
  const categories = (data.categories ?? []).filter((cat: { amount: number }) => Number(cat.amount) > 0);

  return (
    <ScrollScreen onRefresh={() => query.refetch()} refreshing={query.isRefetching}>
      <Card>
        <Text style={styles.kicker}>Resumen</Text>
        <Muted>Total 12 meses</Muted>
        <Text style={styles.total}>{`$${total.toFixed(2)}`}</Text>
      </Card>
      {total === 0 ? (
        <Empty icon="wallet-outline" title="Sin gastos todavía">
          Cuando haya servicios facturados, el total y las categorías aparecerán aquí. El cliente solo consulta, no registra gastos.
        </Empty>
      ) : (
        <>
          {categories.map((cat: { name: string; amount: number }) => (
            <View key={cat.name} style={styles.row}>
              <Text style={styles.cat}>{cat.name}</Text>
              <Text style={styles.amount}>{`$${Number(cat.amount).toFixed(2)}`}</Text>
            </View>
          ))}
          {(data.recent ?? []).length === 0 ? <Muted>Sin movimientos recientes.</Muted> : null}
        </>
      )}
    </ScrollScreen>
  );
}

const styles = StyleSheet.create({
  kicker: { color: colors.primary, fontWeight: '800', fontSize: 11, letterSpacing: 1, textTransform: 'uppercase' },
  total: { fontSize: 34, fontWeight: '800', color: colors.text, letterSpacing: -0.8, marginTop: 4 },
  row: {
    backgroundColor: colors.card,
    borderRadius: 16,
    padding: 16,
    borderWidth: 1,
    borderColor: colors.border,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  cat: { fontWeight: '700', color: colors.text, fontSize: 16 },
  amount: { fontWeight: '800', color: colors.textSecondary },
});
