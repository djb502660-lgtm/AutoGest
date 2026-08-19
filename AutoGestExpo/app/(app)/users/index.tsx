import { Empty, ListRow, Loading, ScrollScreen } from '../../../src/components/ui';
import { api, type StaffUser } from '../../../src/lib/api';
import { useAuth } from '../../../src/lib/auth';
import { roleLabel } from '../../../src/lib/format';
import { colors, statusTone } from '../../../src/lib/theme';
import { useQuery } from '@tanstack/react-query';
import { Link, Redirect } from 'expo-router';
import { useMemo, useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';

const filters = [
  { value: 'todos', label: 'Todos' },
  { value: 'admin', label: 'Admin' },
  { value: 'asesor', label: 'Asesor' },
  { value: 'mecanico', label: 'Mecánico' },
  { value: 'cliente', label: 'Cliente' },
] as const;

export default function UsersScreen() {
  const { role } = useAuth();
  const [filter, setFilter] = useState<(typeof filters)[number]['value']>('todos');
  const query = useQuery({
    queryKey: ['users'],
    queryFn: async () => ((await api.get('/users')).data.users ?? []) as StaffUser[],
    enabled: role === 'admin',
  });

  const users = useMemo(() => {
    const items = query.data ?? [];
    if (filter === 'todos') {
      return items;
    }
    return items.filter((user) => user.role === filter);
  }, [filter, query.data]);

  if (role !== 'admin') {
    return <Redirect href="/(app)/home" />;
  }

  if (query.isLoading) {
    return <Loading />;
  }

  if (query.isError) {
    return (
      <ScrollScreen onRefresh={() => query.refetch()} refreshing={query.isRefetching}>
        <Empty icon="cloud-offline-outline" title="No se pudo cargar el equipo" actionLabel="Reintentar" onAction={() => query.refetch()}>
          Revisa tu conexión e inténtalo de nuevo.
        </Empty>
      </ScrollScreen>
    );
  }

  return (
    <ScrollScreen onRefresh={() => query.refetch()} refreshing={query.isRefetching}>
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
      {users.length === 0 ? (
        <Empty icon="people-outline" title="Sin usuarios en este filtro">
          El alta y la edición completa de cuentas se hace en la web.
        </Empty>
      ) : null}
      {users.map((user) => (
        <Link key={user.id} href={`/(app)/users/${user.id}`} asChild>
          <Pressable>
            <ListRow
              icon="person-outline"
              title={user.name}
              subtitle={[user.email, user.vehicles_count ? `${user.vehicles_count} vehículo${user.vehicles_count === 1 ? '' : 's'}` : null]
                .filter(Boolean)
                .join(' · ')}
              badge={roleLabel(String(user.role))}
              tone={user.status === 'inactivo' ? 'danger' : statusTone(String(user.role))}
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
