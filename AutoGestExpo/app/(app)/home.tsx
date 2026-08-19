import { Badge, Empty, ListRow, Loading, ScrollScreen, Section, Stat } from '../../src/components/ui';
import { fetchDashboard, type Appointment, type Order } from '../../src/lib/api';
import { useAuth } from '../../src/lib/auth';
import { displayFirstName, roleLabel } from '../../src/lib/format';
import { colors, statusTone } from '../../src/lib/theme';
import { Ionicons } from '@expo/vector-icons';
import { useQuery } from '@tanstack/react-query';
import { Link, useRouter } from 'expo-router';
import { Pressable, StyleSheet, Text, View } from 'react-native';

export default function HomeScreen() {
  const { user, role } = useAuth();
  const router = useRouter();
  const dashboard = useQuery({
    queryKey: ['dashboard'],
    queryFn: fetchDashboard,
  });

  if (dashboard.isLoading) {
    return <Loading />;
  }

  if (dashboard.isError) {
    return (
      <ScrollScreen onRefresh={() => dashboard.refetch()} refreshing={dashboard.isRefetching}>
        <Empty
          icon="cloud-offline-outline"
          title="No se pudo cargar"
          actionLabel="Reintentar"
          onAction={() => dashboard.refetch()}
        >
          Revisa tu conexión e inténtalo de nuevo.
        </Empty>
      </ScrollScreen>
    );
  }

  const stats = pickStats(dashboard.data?.stats ?? {}, role);
  const orders: Order[] = dashboard.data?.recent_orders ?? [];
  const appointments: Appointment[] = dashboard.data?.pending_appointments ?? dashboard.data?.appointments ?? [];
  const isClient = role === 'cliente';
  const isMechanic = role === 'mecanico';
  const isAdmin = role === 'admin';
  const isAdvisor = role === 'asesor';

  return (
    <ScrollScreen onRefresh={() => dashboard.refetch()} refreshing={dashboard.isRefetching}>
      <View style={styles.hero}>
        <View>
          <Text style={styles.kicker}>AutoGest</Text>
          <Text style={styles.hello}>{`Hola, ${displayFirstName(user?.name)}`}</Text>
        </View>
        <Badge tone="info">{roleLabel(role)}</Badge>
      </View>

      {stats.length ? (
        <View style={styles.stats}>
          {stats.map(([key, value]) => (
            <Stat key={key} label={labelFor(key)} value={value} wide={isAdmin} />
          ))}
        </View>
      ) : null}

      {isClient || isMechanic || isAdmin || isAdvisor ? (
        <View style={styles.shortcuts}>
          {isClient ? (
            <>
              <Shortcut icon="car-sport-outline" label="Mis vehículos" onPress={() => router.push('/(app)/vehicles')} />
              <Shortcut icon="chatbubbles-outline" label="Pedir cita" onPress={() => router.push('/(app)/chatbot')} />
            </>
          ) : null}
          {isAdmin ? (
            <>
              <Shortcut icon="clipboard-outline" label="Órdenes" onPress={() => router.push('/(app)/orders')} />
              <Shortcut icon="calendar-outline" label="Solicitudes" onPress={() => router.push('/(app)/appointments')} />
              <Shortcut icon="car-sport-outline" label="Flota" onPress={() => router.push('/(app)/vehicles')} />
              <Shortcut icon="people-outline" label="Equipo" onPress={() => router.push('/(app)/users')} />
            </>
          ) : null}
          {isMechanic ? (
            <Shortcut icon="clipboard-outline" label="Mis órdenes" onPress={() => router.push('/(app)/orders')} />
          ) : null}
          {isAdvisor ? (
            <>
              <Shortcut icon="clipboard-outline" label="Ver órdenes" onPress={() => router.push('/(app)/orders')} />
              <Shortcut icon="calendar-outline" label="Solicitudes" onPress={() => router.push('/(app)/appointments')} />
            </>
          ) : null}
        </View>
      ) : null}

      {orders.length ? (
        <>
          <Section>Órdenes recientes</Section>
          {orders.map((order) => (
            <Link key={order.id} href={`/(app)/orders/${order.id}`} asChild>
              <Pressable>
                <ListRow
                  icon="clipboard-outline"
                  title={order.order_number}
                  subtitle={
                    order.vehicle
                      ? [order.vehicle.brand, order.vehicle.model, order.vehicle.plate, order.client?.name]
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
        </>
      ) : (
        <Empty
          icon="clipboard-outline"
          title="Sin órdenes recientes"
          actionLabel={isClient ? 'Pedir una cita' : undefined}
          onAction={isClient ? () => router.push('/(app)/chatbot') : undefined}
        >
          {isClient
            ? 'Cuando el taller reciba un servicio, aparecerá aquí. Puedes pedir una cita en el chat.'
            : isMechanic
              ? 'Cuando te asignen una orden, aparecerá aquí para actualizar estado, fotos y avance.'
              : isAdmin
                ? 'Las órdenes del taller aparecerán aquí para que puedas supervisar el trabajo.'
                : isAdvisor
                  ? 'Las órdenes de tus clientes aparecerán aquí cuando se confirmen las solicitudes.'
                  : 'Aún no hay órdenes para mostrar.'}
        </Empty>
      )}

      {appointments.length ? (
        <>
          <Section>{isAdmin || isAdvisor ? 'Solicitudes pendientes' : 'Citas / solicitudes'}</Section>
          {appointments.map((item) => (
            <Link key={item.id} href={`/(app)/appointments/${item.id}`} asChild>
              <Pressable>
                <ListRow
                  icon="calendar-outline"
                  title={item.client?.name ?? item.vehicle?.plate ?? item.service_type ?? 'Solicitud'}
                  subtitle={
                    item.vehicle
                      ? `${item.vehicle.plate} · ${item.service_type ?? 'Solicitud'}`
                      : item.service_type ?? ''
                  }
                  badge={item.status_label ?? item.status}
                  tone={statusTone(item.status)}
                />
              </Pressable>
            </Link>
          ))}
        </>
      ) : isAdmin || isAdvisor ? (
        <Empty icon="calendar-outline" title="Sin solicitudes pendientes">
          Cuando un cliente pida cita en el chatbot, aparecerá aquí para confirmarla o rechazarla.
        </Empty>
      ) : null}
    </ScrollScreen>
  );
}

function Shortcut({
  icon,
  label,
  onPress,
}: {
  icon: keyof typeof Ionicons.glyphMap;
  label: string;
  onPress: () => void;
}) {
  return (
    <Pressable onPress={onPress} style={styles.shortcut}>
      <View style={styles.shortcutIcon}>
        <Ionicons name={icon} size={20} color={colors.primary} />
      </View>
      <Text style={styles.shortcutLabel}>{label}</Text>
    </Pressable>
  );
}

function pickStats(stats: Record<string, number | string>, role: string | null): [string, string][] {
  const preferred =
    role === 'asesor'
      ? ['pending_appointments', 'open_orders']
      : role === 'mecanico'
        ? ['assigned', 'in_progress', 'received']
        : role === 'admin'
          ? ['open_orders', 'pending_appointments', 'vehicles', 'users']
          : ['vehicles', 'open_orders', 'upcoming_services'];
  const limit = role === 'admin' ? 4 : 3;

  const picked = preferred
    .filter((key) => stats[key] !== undefined)
    .map((key) => [key, String(stats[key])] as [string, string]);

  if (picked.length) {
    return picked.slice(0, limit);
  }

  return Object.entries(stats)
    .slice(0, limit)
    .map(([key, value]) => [key, String(value)]);
}

function labelFor(key: string): string {
  const map: Record<string, string> = {
    vehicles: 'Vehículos',
    in_shop: 'En taller',
    upcoming_services: 'Próximo servicio',
    completed_services: 'Hechos',
    total_expenses: 'Gastos',
    open_orders: 'Órdenes abiertas',
    pending_appointments: 'Solicitudes',
    assigned: 'Asignadas',
    in_progress: 'En proceso',
    received: 'Recibidas',
    users: 'Usuarios',
  };
  return map[key] ?? key;
}

const styles = StyleSheet.create({
  hero: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', gap: 12, marginBottom: 2 },
  kicker: { color: colors.primary, fontWeight: '800', fontSize: 11, letterSpacing: 1, textTransform: 'uppercase', marginBottom: 4 },
  hello: { fontSize: 28, fontWeight: '800', color: colors.text, letterSpacing: -0.5 },
  stats: { flexDirection: 'row', flexWrap: 'wrap', gap: 10 },
  shortcuts: { flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
  shortcut: {
    flexGrow: 1,
    flexBasis: '44%',
    minWidth: '44%',
    minHeight: 76,
    backgroundColor: colors.card,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: colors.border,
    paddingVertical: 14,
    paddingHorizontal: 10,
    alignItems: 'center',
    gap: 8,
  },
  shortcutIcon: {
    width: 36,
    height: 36,
    borderRadius: 12,
    backgroundColor: colors.primarySoft,
    alignItems: 'center',
    justifyContent: 'center',
  },
  shortcutLabel: { color: colors.text, fontWeight: '700', fontSize: 12, textAlign: 'center' },
});
