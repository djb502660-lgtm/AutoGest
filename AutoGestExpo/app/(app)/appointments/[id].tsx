import { Badge, Button, Card, Empty, Loading, Muted, ScrollScreen, Title } from '../../../src/components/ui';
import { api, apiErrorMessage, type Appointment } from '../../../src/lib/api';
import { useAuth } from '../../../src/lib/auth';
import { colors, statusTone } from '../../../src/lib/theme';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useState } from 'react';
import { Alert, StyleSheet, TextInput, View } from 'react-native';

export default function AppointmentDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const { role } = useAuth();
  const router = useRouter();
  const queryClient = useQueryClient();
  const [notes, setNotes] = useState('');

  const query = useQuery({
    queryKey: ['appointment', id],
    queryFn: async () => (await api.get(`/appointments/${id}`)).data.appointment as Appointment,
  });

  const confirm = useMutation({
    mutationFn: async () => api.post(`/appointments/${id}/confirm`, { advisor_notes: notes || null }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['appointments'] });
      queryClient.invalidateQueries({ queryKey: ['dashboard'] });
      queryClient.invalidateQueries({ queryKey: ['orders'] });
      Alert.alert('Confirmada', 'La solicitud se convirtió en orden.');
      router.replace('/(app)/orders');
    },
    onError: (error: unknown) => Alert.alert('Error', apiErrorMessage(error, 'No se pudo confirmar.')),
  });

  const reject = useMutation({
    mutationFn: async () => api.post(`/appointments/${id}/reject`, { advisor_notes: notes }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['appointments'] });
      queryClient.invalidateQueries({ queryKey: ['dashboard'] });
      router.back();
    },
    onError: (error: unknown) => Alert.alert('Error', apiErrorMessage(error, 'Indica una nota para rechazar.')),
  });

  if (query.isLoading) {
    return <Loading />;
  }

  if (query.isError || !query.data) {
    return (
      <ScrollScreen onRefresh={() => query.refetch()} refreshing={query.isRefetching}>
        <Empty
          icon="warning-outline"
          title="No se pudo cargar la solicitud"
          actionLabel="Reintentar"
          onAction={() => void query.refetch()}
        >
          {apiErrorMessage(query.error, 'Revisa tu conexión e intenta de nuevo.')}
        </Empty>
      </ScrollScreen>
    );
  }

  const item = query.data;

  return (
    <ScrollScreen>
      <View style={styles.head}>
        <Title>{item.vehicle?.plate ?? 'Solicitud'}</Title>
        <Badge tone={statusTone(item.status)}>{item.status_label ?? item.status}</Badge>
      </View>
      <Card>
        <Muted>{item.client?.name ?? ''}</Muted>
        <Muted>{item.service_type ?? ''}</Muted>
        <Muted>{item.description ?? ''}</Muted>
        <Muted>{`${item.requested_date ?? ''} ${item.requested_time ?? ''}`}</Muted>
      </Card>
      {item.status === 'pendiente' && (role === 'asesor' || role === 'admin') ? (
        <Card>
          <TextInput
            placeholder="Notas del asesor"
            placeholderTextColor={colors.muted}
            value={notes}
            onChangeText={setNotes}
            style={styles.input}
            multiline
          />
          <Button title="Confirmar" disabled={confirm.isPending || reject.isPending} onPress={() => confirm.mutate()} />
          <Button title="Rechazar" variant="danger" disabled={confirm.isPending || reject.isPending} onPress={() => reject.mutate()} />
        </Card>
      ) : null}
    </ScrollScreen>
  );
}

const styles = StyleSheet.create({
  head: { gap: 8 },
  input: {
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 14,
    padding: 12,
    minHeight: 80,
    backgroundColor: colors.soft,
    color: colors.text,
    textAlignVertical: 'top',
  },
});
