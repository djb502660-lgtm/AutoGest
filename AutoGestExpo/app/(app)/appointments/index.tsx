import { Badge, Card, Empty, Loading, Muted, ScrollScreen } from '../../../src/components/ui';
import { api, type Appointment } from '../../../src/lib/api';
import { colors, statusTone } from '../../../src/lib/theme';
import { useQuery } from '@tanstack/react-query';
import { Link } from 'expo-router';
import { Pressable, StyleSheet, Text, View } from 'react-native';

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
      <ScrollScreen>
        <Empty>Las solicitudes chatbot aún no están disponibles en la API de producción.</Empty>
      </ScrollScreen>
    );
  }

  const items = query.data ?? [];

  return (
    <ScrollScreen>
      {items.length === 0 ? <Empty>No hay solicitudes pendientes.</Empty> : null}
      {items.map((item: Appointment) => (
        <Link key={item.id} href={`/(app)/appointments/${item.id}`} asChild>
          <Pressable>
            <Card>
              <View style={styles.head}>
                <Text style={styles.strong}>{item.client?.name ?? 'Cliente'}</Text>
                <Badge tone={statusTone(item.status)}>{item.status_label ?? item.status}</Badge>
              </View>
              <Muted>{item.vehicle ? `${item.vehicle.plate} · ${item.service_type}` : item.service_type ?? ''}</Muted>
              <Muted>{`${item.requested_date ?? ''} ${item.requested_time ?? ''}`}</Muted>
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
