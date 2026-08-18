import { Badge, Button, Card, Loading, Muted, ScrollScreen, Title } from '../../../src/components/ui';
import { api, type Appointment } from '../../../src/lib/api';
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
      Alert.alert('Confirmada', 'La solicitud se convirtió en orden.');
      router.replace('/(app)/orders/index');
    },
    onError: (error: any) => Alert.alert('Error', error?.response?.data?.message ?? 'No se pudo confirmar.'),
  });

  const reject = useMutation({
    mutationFn: async () => api.post(`/appointments/${id}/reject`, { advisor_notes: notes }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['appointments'] });
      router.back();
    },
    onError: (error: any) => Alert.alert('Error', error?.response?.data?.message ?? 'Indica una nota para rechazar.'),
  });

  if (query.isLoading || !query.data) {
    return <Loading />;
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
          <Button title="Confirmar" onPress={() => confirm.mutate()} />
          <Button title="Rechazar" variant="danger" onPress={() => reject.mutate()} />
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
    borderRadius: 12,
    padding: 12,
    minHeight: 80,
    backgroundColor: colors.soft,
    color: colors.text,
    textAlignVertical: 'top',
  },
});
