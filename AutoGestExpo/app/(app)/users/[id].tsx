import { Badge, Card, Empty, Loading, Muted, ScrollScreen, Title } from '../../../src/components/ui';
import { api, apiErrorMessage, type StaffUser } from '../../../src/lib/api';
import { useAuth } from '../../../src/lib/auth';
import { roleLabel } from '../../../src/lib/format';
import { statusTone } from '../../../src/lib/theme';
import { useQuery } from '@tanstack/react-query';
import { Redirect, useLocalSearchParams } from 'expo-router';

export default function UserDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const { role } = useAuth();
  const query = useQuery({
    queryKey: ['user', id],
    queryFn: async () => (await api.get(`/users/${id}`)).data.user as StaffUser,
    enabled: role === 'admin' && Boolean(id && id !== 'index' && !Number.isNaN(Number(id))),
  });

  if (role !== 'admin') {
    return <Redirect href="/(app)/home" />;
  }

  if (!id || id === 'index' || Number.isNaN(Number(id))) {
    return <Redirect href="/(app)/users" />;
  }

  if (query.isLoading) {
    return <Loading />;
  }

  if (query.isError || !query.data) {
    return (
      <ScrollScreen onRefresh={() => query.refetch()} refreshing={query.isRefetching}>
        <Empty icon="cloud-offline-outline" title="No se pudo cargar el usuario" actionLabel="Reintentar" onAction={() => query.refetch()}>
          {apiErrorMessage(query.error, 'Revisa tu conexión e inténtalo de nuevo.')}
        </Empty>
      </ScrollScreen>
    );
  }

  const user = query.data;
  const vehicles = user.vehicles ?? [];

  return (
    <ScrollScreen onRefresh={() => query.refetch()} refreshing={query.isRefetching}>
      <Title>{user.name}</Title>
      <Badge tone={user.status === 'inactivo' ? 'danger' : statusTone(String(user.role))}>{roleLabel(String(user.role))}</Badge>
      <Card>
        <Muted>{user.email}</Muted>
        {user.phone ? <Muted>{`Teléfono: ${user.phone}`}</Muted> : null}
        <Muted>{`Estado: ${user.status ?? 'activo'}`}</Muted>
        {user.last_login_at ? <Muted>{`Último acceso: ${user.last_login_at}`}</Muted> : null}
        {typeof user.assigned_orders_count === 'number' ? (
          <Muted>{`Órdenes asignadas: ${user.assigned_orders_count}`}</Muted>
        ) : null}
      </Card>
      {vehicles.length ? (
        <Card>
          {vehicles.map((vehicle: NonNullable<StaffUser['vehicles']>[number]) => (
            <Muted key={vehicle.id}>{`${vehicle.plate} · ${vehicle.brand} ${vehicle.model}`}</Muted>
          ))}
        </Card>
      ) : (
        <Empty icon="car-sport-outline" title="Sin vehículos">
          Este usuario no tiene vehículos registrados.
        </Empty>
      )}
    </ScrollScreen>
  );
}
