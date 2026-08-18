import { Badge, Card, Empty, Loading, Muted, ScrollScreen } from '../../../src/components/ui';
import { api, normalizeOrder, type Order } from '../../../src/lib/api';
import { colors, statusTone } from '../../../src/lib/theme';
import { useQuery } from '@tanstack/react-query';
import { Link } from 'expo-router';
import { Pressable, StyleSheet, Text, View } from 'react-native';

export default function OrdersScreen() {
  const query = useQuery({
    queryKey: ['orders'],
    queryFn: async () =>
      ((await api.get('/orders')).data.orders ?? []).map((item: Record<string, unknown>) => normalizeOrder(item)),
  });

  if (query.isLoading) {
    return <Loading />;
  }

  const orders = query.data ?? [];

  return (
    <ScrollScreen>
      {orders.length === 0 ? <Empty>No hay órdenes.</Empty> : null}
      {orders.map((order: Order) => (
        <Link key={order.id} href={`/(app)/orders/${order.id}`} asChild>
          <Pressable>
            <Card>
              <View style={styles.head}>
                <Text style={styles.strong}>{order.order_number}</Text>
                <Badge tone={statusTone(order.status)}>{order.status_label ?? order.status}</Badge>
              </View>
              <Muted>{order.vehicle ? `${order.vehicle.plate} · ${order.vehicle.brand}` : order.description}</Muted>
            </Card>
          </Pressable>
        </Link>
      ))}
    </ScrollScreen>
  );
}

const styles = StyleSheet.create({
  head: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', gap: 8 },
  strong: { fontWeight: '800', color: colors.text, fontSize: 16, flex: 1 },
});
