import { Empty, ListRow, Loading, ScrollScreen } from '../../../src/components/ui';
import { api, normalizeOrder, type Order } from '../../../src/lib/api';
import { useAuth } from '../../../src/lib/auth';
import { colors, statusTone } from '../../../src/lib/theme';
import { useQuery } from '@tanstack/react-query';
import { Link, useRouter } from 'expo-router';
import { useMemo, useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';

const filters = [
  { value: 'abiertas', label: 'En curso' },
  { value: 'recibida', label: 'En espera' },
  { value: 'en_proceso', label: 'En proceso' },
  { value: 'completada', label: 'Terminadas' },
  { value: 'todas', label: 'Todas' },
] as const;

export default function OrdersScreen() {
  const { role, user } = useAuth();
  const router = useRouter();
  const [filter, setFilter] = useState<(typeof filters)[number]['value']>('abiertas');
  const query = useQuery({
    queryKey: ['orders', user?.id],
    queryFn: async () =>
      ((await api.get('/orders')).data.orders ?? []).map((item: Record<string, unknown>) => normalizeOrder(item)),
  });

  const isClient = role === 'cliente';
  const isStaff = role === 'mecanico' || role === 'admin' || role === 'asesor';
  const orders = useMemo(() => {
    const items = query.data ?? [];
    if (!isStaff || filter === 'todas') {
      return items;
    }
    if (filter === 'abiertas') {
      return items.filter((order) => !['completada', 'entregada', 'cancelada'].includes(order.status));
    }
    if (filter === 'completada') {
      return items.filter((order) => ['completada', 'entregada'].includes(order.status));
    }
    return items.filter((order) => order.status === filter);
  }, [filter, isStaff, query.data]);

  if (query.isLoading) {
    return <Loading />;
  }

  if (query.isError) {
    return (
      <ScrollScreen onRefresh={() => query.refetch()} refreshing={query.isRefetching}>
        <Empty
          icon="cloud-offline-outline"
          title="No se pudieron cargar las órdenes"
          actionLabel="Reintentar"
          onAction={() => query.refetch()}
        >
          Revisa tu conexión e inténtalo de nuevo.
        </Empty>
      </ScrollScreen>
    );
  }

  return (
    <ScrollScreen onRefresh={() => query.refetch()} refreshing={query.isRefetching}>
      {isStaff ? (
        <View style={styles.filters}>
          {filters.map((item) => (
            <Pressable
              key={item.value}
              onPress={() => setFilter(item.value)}
              style={[styles.chip, filter === item.value && styles.chipOn]}
            >
              <Text style={filter === item.value ? styles.chipOnText : styles.chipText}>{item.label}</Text>
            </Pressable>
          ))}
        </View>
      ) : null}
      {orders.length === 0 ? (
        <Empty
          icon="clipboard-outline"
          title={isStaff ? 'Sin órdenes en este filtro' : 'No hay servicios en curso'}
          actionLabel={isClient ? 'Solicitar cita' : undefined}
          onAction={isClient ? () => router.push('/(app)/chatbot') : undefined}
        >
          {isClient
            ? 'Cuando el taller reciba tu vehículo, verás el estado aquí. Puedes pedir una cita en el chat.'
            : isStaff
              ? 'Cuando haya trabajo en el taller, aparecerán las órdenes aquí.'
              : 'No hay órdenes pendientes por ahora.'}
        </Empty>
      ) : null}
      {orders.map((order: Order) => (
        <Link key={order.id} href={`/(app)/orders/${order.id}`} asChild>
          <Pressable>
            <ListRow
              icon="clipboard-outline"
              title={order.order_number}
              subtitle={
                order.vehicle
                  ? [
                      order.vehicle.plate,
                      order.vehicle.brand,
                      order.client?.name,
                      typeof order.progress === 'number' ? `${order.progress}%` : null,
                    ]
                      .filter(Boolean)
                      .join(' · ')
                  : order.description
              }
              badge={order.status_label ?? order.status}
              tone={statusTone(order.status)}
            />
          </Pressable>
        </Link>
      ))}
    </ScrollScreen>
  );
}

const styles = StyleSheet.create({
  filters: { flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
  chip: {
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 999,
    backgroundColor: colors.card,
    borderWidth: 1,
    borderColor: colors.border,
    minHeight: 36,
    justifyContent: 'center',
  },
  chipOn: { backgroundColor: colors.primary, borderColor: colors.primary },
  chipText: { color: colors.muted, fontSize: 12, fontWeight: '600' },
  chipOnText: { color: '#fff', fontSize: 12, fontWeight: '700' },
});
