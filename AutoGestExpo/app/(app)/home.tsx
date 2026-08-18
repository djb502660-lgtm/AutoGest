import { Badge, Button, Empty, Loading, Muted, ScrollScreen, Section, Stat } from '../../src/components/ui';
import { fetchDashboard, hasMobileV1, type Appointment, type Order } from '../../src/lib/api';
import { useAuth } from '../../src/lib/auth';
import { colors, statusTone } from '../../src/lib/theme';
import { useQuery } from '@tanstack/react-query';
import { Link } from 'expo-router';
import { Pressable, StyleSheet, Text, View } from 'react-native';

export default function HomeScreen() {
  const { user, role, logout } = useAuth();
  const dashboard = useQuery({
    queryKey: ['dashboard'],
    queryFn: fetchDashboard,
  });
  const mobileV1 = useQuery({
    queryKey: ['mobile-v1'],
    queryFn: hasMobileV1,
    staleTime: 5 * 60 * 1000,
  });

  if (dashboard.isLoading) {
    return <Loading />;
  }

  if (dashboard.isError) {
    return (
      <ScrollScreen>
        <Empty>No se pudo cargar el panel. Revisa la API y vuelve a intentar.</Empty>
        <Button title="Reintentar" onPress={() => dashboard.refetch()} />
        <Button title="Cerrar sesión" variant="ghost" onPress={() => logout()} />
      </ScrollScreen>
    );
  }

  const stats = dashboard.data?.stats ?? {};
  const orders: Order[] = dashboard.data?.recent_orders ?? [];
  const appointments: Appointment[] = dashboard.data?.pending_appointments ?? dashboard.data?.appointments ?? [];

  return (
    <ScrollScreen>
      <View style={styles.hero}>
        <Text style={styles.hello}>{`Hola, ${user?.name?.split(' ')[0] ?? ''}`}</Text>
        <Badge tone="info">{roleLabel(role)}</Badge>
        {mobileV1.data === false ? (
          <Muted>Modo compatible: algunas funciones aparecen cuando despliegues la API móvil v1 en Render.</Muted>
        ) : null}
      </View>
      <View style={styles.stats}>
        {Object.entries(stats).map(([key, value]) => (
          <Stat key={key} label={labelFor(key)} value={String(value)} />
        ))}
      </View>
      {orders.length ? (
        <>
          <Section>Órdenes recientes</Section>
          {orders.map((order) => (
            <Link key={order.id} href={`/(app)/orders/${order.id}`} asChild>
              <Pressable>
                <View style={styles.rowCard}>
                  <View style={styles.rowBody}>
                    <Text style={styles.strong}>{order.order_number}</Text>
                    <Muted>
                      {order.vehicle
                        ? `${order.vehicle.brand} ${order.vehicle.model} · ${order.vehicle.plate}`
                        : order.description}
                    </Muted>
                  </View>
                  <Badge tone={statusTone(order.status)}>{order.status_label ?? order.status}</Badge>
                </View>
              </Pressable>
            </Link>
          ))}
        </>
      ) : (
        <Empty>Sin órdenes recientes.</Empty>
      )}
      {appointments.length ? (
        <>
          <Section>Citas / solicitudes</Section>
          {appointments.map((item) => (
            <Link key={item.id} href={`/(app)/appointments/${item.id}`} asChild>
              <Pressable>
                <View style={styles.rowCard}>
                  <View style={styles.rowBody}>
                    <Text style={styles.strong}>{item.vehicle?.plate ?? item.service_type}</Text>
                    <Muted>{item.service_type ?? ''}</Muted>
                  </View>
                  <Badge tone={statusTone(item.status)}>{item.status_label ?? item.status}</Badge>
                </View>
              </Pressable>
            </Link>
          ))}
        </>
      ) : null}
      <Button title="Cerrar sesión" variant="ghost" onPress={() => logout()} />
    </ScrollScreen>
  );
}

function roleLabel(role: string | null): string {
  return role === 'asesor' ? 'Asesor' : role === 'mecanico' ? 'Mecánico' : role === 'admin' ? 'Admin' : 'Cliente';
}

function labelFor(key: string): string {
  const map: Record<string, string> = {
    vehicles: 'Vehículos',
    in_shop: 'En taller',
    upcoming_services: 'Próximos',
    completed_services: 'Hechos',
    total_expenses: 'Gastos',
    open_orders: 'Abiertas',
    pending_appointments: 'Solicitudes',
    assigned: 'Asignadas',
    in_progress: 'En proceso',
    received: 'Recibidas',
  };
  return map[key] ?? key;
}

const styles = StyleSheet.create({
  hero: { gap: 8, marginBottom: 4 },
  hello: { fontSize: 26, fontWeight: '800', color: colors.text, letterSpacing: -0.4 },
  stats: { flexDirection: 'row', flexWrap: 'wrap', gap: 10 },
  rowCard: {
    backgroundColor: colors.card,
    borderRadius: 16,
    padding: 16,
    borderWidth: 1,
    borderColor: colors.border,
    gap: 8,
  },
  rowBody: { gap: 4 },
  strong: { fontWeight: '800', color: colors.text, fontSize: 16 },
});
