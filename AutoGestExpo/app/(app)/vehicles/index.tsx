import { Empty, ListRow, Loading, ScrollScreen } from '../../../src/components/ui';
import { api, type Vehicle } from '../../../src/lib/api';
import { useAuth } from '../../../src/lib/auth';
import { statusTone } from '../../../src/lib/theme';
import { useQuery } from '@tanstack/react-query';
import { Link, useRouter } from 'expo-router';
import { Pressable } from 'react-native';

export default function VehiclesScreen() {
  const { role } = useAuth();
  const router = useRouter();
  const query = useQuery({
    queryKey: ['vehicles'],
    queryFn: async () => ((await api.get('/vehicles')).data.vehicles ?? []) as Vehicle[],
  });

  if (query.isLoading) {
    return <Loading />;
  }

  const vehicles = query.data ?? [];

  return (
    <ScrollScreen onRefresh={() => query.refetch()} refreshing={query.isRefetching}>
      {vehicles.length === 0 ? (
        <Empty
          icon="car-sport-outline"
          title="Aún no hay vehículos"
          actionLabel={role === 'cliente' ? 'Hablar con el asistente' : undefined}
          onAction={role === 'cliente' ? () => router.push('/(app)/chatbot') : undefined}
        >
          {role === 'cliente'
            ? 'El taller registra tus vehículos. Si ya los dejaste, pídele al asesor o escríbelo en el chat.'
            : role === 'admin'
              ? 'Cuando se registren vehículos de clientes, la flota aparecerá aquí.'
              : 'No hay vehículos para mostrar.'}
        </Empty>
      ) : null}
      {vehicles.map((vehicle: Vehicle) => (
        <Link key={vehicle.id} href={`/(app)/vehicles/${vehicle.id}`} asChild>
          <Pressable>
            <ListRow
              icon="car-sport-outline"
              title={`${vehicle.brand} ${vehicle.model}`}
              subtitle={
                vehicle.client?.name
                  ? `Placa ${vehicle.plate} · ${vehicle.client.name}`
                  : `Placa ${vehicle.plate} · ${vehicle.mileage ?? 0} km`
              }
              badge={vehicle.status_label ?? vehicle.status ?? 'activo'}
              tone={statusTone(vehicle.status)}
            />
          </Pressable>
        </Link>
      ))}
    </ScrollScreen>
  );
}
