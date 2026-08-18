import { Badge, Card, Empty, Loading, Muted, ScrollScreen } from '../../../src/components/ui';
import { api, type Vehicle } from '../../../src/lib/api';
import { colors, statusTone } from '../../../src/lib/theme';
import { useQuery } from '@tanstack/react-query';
import { Link } from 'expo-router';
import { Pressable, StyleSheet, Text, View } from 'react-native';

export default function VehiclesScreen() {
  const query = useQuery({
    queryKey: ['vehicles'],
    queryFn: async () => ((await api.get('/vehicles')).data.vehicles ?? []) as Vehicle[],
  });

  if (query.isLoading) {
    return <Loading />;
  }

  const vehicles = query.data ?? [];

  return (
    <ScrollScreen>
      {vehicles.length === 0 ? <Empty>No hay vehículos.</Empty> : null}
      {vehicles.map((vehicle: Vehicle) => (
        <Link key={vehicle.id} href={`/(app)/vehicles/${vehicle.id}`} asChild>
          <Pressable>
            <Card>
              <View style={styles.head}>
                <Text style={styles.strong}>{`${vehicle.brand} ${vehicle.model}`}</Text>
                <Badge tone={statusTone(vehicle.status)}>{vehicle.status_label ?? vehicle.status ?? 'activo'}</Badge>
              </View>
              <Muted>{`Placa ${vehicle.plate} · ${vehicle.mileage ?? 0} km`}</Muted>
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
