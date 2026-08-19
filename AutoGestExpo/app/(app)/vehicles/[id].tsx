import { Badge, Card, Loading, Muted, ScrollScreen, Title } from '../../../src/components/ui';
import { api, type Vehicle } from '../../../src/lib/api';
import { statusTone } from '../../../src/lib/theme';
import { useQuery } from '@tanstack/react-query';
import { useLocalSearchParams } from 'expo-router';

export default function VehicleDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const query = useQuery({
    queryKey: ['vehicle', id],
    queryFn: async () => (await api.get(`/vehicles/${id}`)).data.vehicle as Vehicle & {
      color?: string;
      vin?: string;
      client?: { name: string; email?: string; phone?: string };
    },
  });

  if (query.isLoading || !query.data) {
    return <Loading />;
  }

  const vehicle = query.data;

  return (
    <ScrollScreen>
      <Title>{`${vehicle.brand} ${vehicle.model}`}</Title>
      <Badge tone={statusTone(vehicle.status)}>{vehicle.status_label ?? vehicle.status ?? 'activo'}</Badge>
      <Card>
        <Muted>{`Placa: ${vehicle.plate}`}</Muted>
        <Muted>{`Año: ${vehicle.year ?? '—'}`}</Muted>
        <Muted>{`Km: ${vehicle.mileage ?? 0}`}</Muted>
        {vehicle.client ? <Muted>{`Cliente: ${vehicle.client.name}`}</Muted> : null}
        {vehicle.client?.phone ? <Muted>{`Teléfono: ${vehicle.client.phone}`}</Muted> : null}
        {vehicle.client?.email ? <Muted>{vehicle.client.email}</Muted> : null}
      </Card>
    </ScrollScreen>
  );
}
