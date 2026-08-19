import { Badge, Card, Empty, Loading, Muted, ScrollScreen, Title } from '../../../src/components/ui';
import { api, apiErrorMessage, type Vehicle } from '../../../src/lib/api';
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

  if (query.isLoading) {
    return <Loading />;
  }

  if (query.isError || !query.data) {
    return (
      <ScrollScreen onRefresh={() => query.refetch()} refreshing={query.isRefetching}>
        <Empty
          icon="warning-outline"
          title="No se pudo cargar el vehículo"
          actionLabel="Reintentar"
          onAction={() => void query.refetch()}
        >
          {apiErrorMessage(query.error, 'Revisa tu conexión e intenta de nuevo.')}
        </Empty>
      </ScrollScreen>
    );
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
