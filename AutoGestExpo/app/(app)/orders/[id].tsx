import { Badge, Button, Card, Empty, Loading, Muted, ScrollScreen, Title } from '../../../src/components/ui';
import {
  api,
  fetchOrder,
  hasMobileV1,
  legacyStatuses,
  modernStatuses,
  updateOrderStatus,
  type Photo,
} from '../../../src/lib/api';
import { useAuth } from '../../../src/lib/auth';
import { colors, statusTone } from '../../../src/lib/theme';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import * as ImagePicker from 'expo-image-picker';
import { useLocalSearchParams } from 'expo-router';
import { useEffect, useState } from 'react';
import { Alert, Image, Pressable, StyleSheet, Text, TextInput, View } from 'react-native';

const photoTypes = [
  { value: 'reception' as const, label: 'Recepción' },
  { value: 'before' as const, label: 'Antes' },
  { value: 'after' as const, label: 'Después' },
  { value: 'evidence' as const, label: 'Evidencia' },
];

export default function OrderDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const { role } = useAuth();
  const queryClient = useQueryClient();
  const [comment, setComment] = useState('');
  const [status, setStatus] = useState('en_proceso');
  const [progress, setProgress] = useState('50');
  const [photoType, setPhotoType] = useState<'reception' | 'before' | 'after' | 'evidence'>('reception');

  const features = useQuery({
    queryKey: ['mobile-v1'],
    queryFn: hasMobileV1,
    staleTime: 5 * 60 * 1000,
  });

  const query = useQuery({
    queryKey: ['order', id],
    queryFn: () => fetchOrder(id),
  });

  useEffect(() => {
    if (query.data?.status) {
      setStatus(query.data.status);
    }
  }, [query.data?.status]);

  const statusMutation = useMutation({
    mutationFn: async () => updateOrderStatus(id, status, Number(progress) || 0),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['order', id] }),
    onError: (error: any) => Alert.alert('No se pudo actualizar', error?.response?.data?.message ?? 'Error'),
  });

  const commentMutation = useMutation({
    mutationFn: async () => api.post(`/orders/${id}/comments`, { comment }),
    onSuccess: () => {
      setComment('');
      queryClient.invalidateQueries({ queryKey: ['order', id] });
    },
    onError: (error: any) => Alert.alert('Comentario', error?.response?.data?.message ?? 'No disponible en producción.'),
  });

  function pickPhoto() {
    Alert.alert('Evidencia', 'Elige el origen de la foto', [
      { text: 'Cámara', onPress: () => uploadPhoto('camera') },
      { text: 'Galería', onPress: () => uploadPhoto('library') },
      { text: 'Cancelar', style: 'cancel' },
    ]);
  }

  async function uploadPhoto(source: 'camera' | 'library') {
    const permission =
      source === 'camera'
        ? await ImagePicker.requestCameraPermissionsAsync()
        : await ImagePicker.requestMediaLibraryPermissionsAsync();

    if (!permission.granted) {
      Alert.alert('Permiso requerido', 'Activa la cámara o galería para subir evidencias.');
      return;
    }

    const result =
      source === 'camera'
        ? await ImagePicker.launchCameraAsync({ quality: 0.7 })
        : await ImagePicker.launchImageLibraryAsync({
            mediaTypes: ImagePicker.MediaTypeOptions.Images,
            quality: 0.7,
          });

    if (result.canceled || !result.assets[0]) {
      return;
    }

    const asset = result.assets[0];
    const form = new FormData();
    form.append('type', photoType);
    form.append('description', 'Evidencia móvil');
    form.append('photo', {
      uri: asset.uri,
      name: asset.fileName ?? 'evidence.jpg',
      type: asset.mimeType ?? 'image/jpeg',
    } as any);

    try {
      await api.post(`/orders/${id}/photos`, form, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      queryClient.invalidateQueries({ queryKey: ['order', id] });
    } catch (error: any) {
      Alert.alert('Foto', error?.response?.data?.message ?? 'Subida de fotos no disponible en producción.');
    }
  }

  if (query.isLoading || !query.data) {
    return <Loading />;
  }

  const order = query.data;
  const canWork = role === 'mecanico' || role === 'asesor' || role === 'admin';
  const mobileV1 = features.data ?? false;
  const statuses = mobileV1 ? modernStatuses : legacyStatuses;

  return (
    <ScrollScreen>
      <View style={styles.head}>
        <Title>{order.order_number}</Title>
        <Badge tone={statusTone(order.status)}>{order.status_label ?? order.status}</Badge>
      </View>
      <Card>
        <Muted>{order.vehicle ? `${order.vehicle.brand} ${order.vehicle.model} · ${order.vehicle.plate}` : ''}</Muted>
        <Muted>{order.description}</Muted>
        {mobileV1 ? <Muted>{`Progreso: ${order.progress ?? 0}%`}</Muted> : null}
        {order.client ? <Muted>{`Cliente: ${order.client.name}`}</Muted> : null}
      </Card>

      {(order.photos ?? []).length ? (
        <Card>
          <Text style={styles.strong}>Evidencias</Text>
          <View style={styles.photos}>
            {(order.photos as Photo[]).map((photo) => (
              <View key={photo.id} style={styles.photoWrap}>
                <Image source={{ uri: photo.url }} style={styles.photo} />
                <Muted>{photo.type_label ?? photo.type ?? ''}</Muted>
              </View>
            ))}
          </View>
        </Card>
      ) : null}

      {canWork ? (
        <Card>
          <Text style={styles.strong}>Actualizar estado</Text>
          <View style={styles.row}>
            {statuses.map((item) => (
              <Pressable key={item.value} onPress={() => setStatus(item.value)} style={[styles.chip, status === item.value && styles.chipOn]}>
                <Text style={status === item.value ? styles.chipOnText : styles.chipText}>{item.label}</Text>
              </Pressable>
            ))}
          </View>
          {mobileV1 ? (
            <TextInput
              value={progress}
              onChangeText={setProgress}
              keyboardType="numeric"
              placeholder="Progreso %"
              placeholderTextColor={colors.muted}
              style={styles.input}
            />
          ) : null}
          <Button title={statusMutation.isPending ? 'Guardando…' : 'Guardar estado'} onPress={() => statusMutation.mutate()} />
        </Card>
      ) : null}

      {canWork && mobileV1 ? (
        <Card>
          <Text style={styles.strong}>Evidencia fotográfica</Text>
          <View style={styles.row}>
            {photoTypes.map((type) => (
              <Pressable key={type.value} onPress={() => setPhotoType(type.value)} style={[styles.chip, photoType === type.value && styles.chipOn]}>
                <Text style={photoType === type.value ? styles.chipOnText : styles.chipText}>{type.label}</Text>
              </Pressable>
            ))}
          </View>
          <Button title="Subir foto" onPress={pickPhoto} />
        </Card>
      ) : null}

      {canWork && mobileV1 ? (
        <Card>
          <Text style={styles.strong}>Comentario técnico</Text>
          <TextInput
            value={comment}
            onChangeText={setComment}
            placeholder="Observación"
            placeholderTextColor={colors.muted}
            style={styles.input}
          />
          <Button title="Publicar" disabled={!comment.trim()} onPress={() => commentMutation.mutate()} />
        </Card>
      ) : null}

      {canWork && !mobileV1 ? (
        <Empty>Fotos y comentarios móviles se habilitan cuando la API v1 esté desplegada en Render.</Empty>
      ) : null}
    </ScrollScreen>
  );
}

const styles = StyleSheet.create({
  head: { gap: 8 },
  strong: { fontWeight: '800', color: colors.text, fontSize: 16 },
  photos: { flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
  photoWrap: { width: 96 },
  photo: { width: 96, height: 72, borderRadius: 10, backgroundColor: colors.border },
  row: { flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
  chip: { paddingHorizontal: 12, paddingVertical: 8, borderRadius: 999, backgroundColor: colors.soft, borderWidth: 1, borderColor: colors.border },
  chipOn: { backgroundColor: colors.primary, borderColor: colors.primary },
  chipText: { color: colors.muted, fontSize: 12, fontWeight: '600' },
  chipOnText: { color: '#fff', fontSize: 12, fontWeight: '700' },
  input: {
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 12,
    padding: 12,
    backgroundColor: colors.soft,
    color: colors.text,
  },
});
