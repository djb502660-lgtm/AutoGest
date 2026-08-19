import { Empty, ListRow, Loading, ScrollScreen } from '../../../src/components/ui';
import { api, type Appointment } from '../../../src/lib/api';
import { statusTone } from '../../../src/lib/theme';
import { useQuery } from '@tanstack/react-query';
import { Link } from 'expo-router';
import { Pressable } from 'react-native';

export default function AppointmentsScreen() {
  const query = useQuery({
    queryKey: ['appointments'],
    queryFn: async () => ((await api.get('/appointments?status=pendiente')).data.appointments ?? []) as Appointment[],
  });

  if (query.isLoading) {
    return <Loading />;
  }

  if (query.isError) {
    return (
      <ScrollScreen onRefresh={() => query.refetch()} refreshing={query.isRefetching}>
        <Empty icon="cloud-offline-outline" title="No se pudieron cargar" actionLabel="Reintentar" onAction={() => query.refetch()}>
          Las solicitudes del chatbot no están disponibles ahora.
        </Empty>
      </ScrollScreen>
    );
  }

  const items = query.data ?? [];

  return (
    <ScrollScreen onRefresh={() => query.refetch()} refreshing={query.isRefetching}>
      {items.length === 0 ? (
        <Empty icon="calendar-outline" title="Bandeja vacía">
          No hay solicitudes pendientes del chatbot.
        </Empty>
      ) : null}
      {items.map((item: Appointment) => (
        <Link key={item.id} href={`/(app)/appointments/${item.id}`} asChild>
          <Pressable>
            <ListRow
              icon="calendar-outline"
              title={item.client?.name ?? 'Cliente'}
              subtitle={item.vehicle ? `${item.vehicle.plate} · ${item.service_type}` : item.service_type ?? ''}
              badge={item.status_label ?? item.status}
              tone={statusTone(item.status)}
            />
          </Pressable>
        </Link>
      ))}
    </ScrollScreen>
  );
}
