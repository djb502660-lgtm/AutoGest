import { PhotoLightbox } from '../../../src/components/photo-lightbox';
import { Badge, Button, Card, Empty, Loading, Muted, ScrollScreen, Title, Toast } from '../../../src/components/ui';
import {
  api,
  apiErrorMessage,
  fetchOrder,
  photoRequirements,
  statusesForRole,
  updateOrderStatus,
  uploadOrderPhoto,
  type Order,
  type Photo,
  type PhotoType,
} from '../../../src/lib/api';
import { useAuth } from '../../../src/lib/auth';
import { colors, statusTone } from '../../../src/lib/theme';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { Ionicons } from '@expo/vector-icons';
import * as ImagePicker from 'expo-image-picker';
import { Redirect, useLocalSearchParams } from 'expo-router';

type OrderComment = NonNullable<Order['comments']>[number];
import { useEffect, useMemo, useState } from 'react';
import { Image, Pressable, StyleSheet, Text, TextInput, View } from 'react-native';

const photoTypes: { value: PhotoType; label: string }[] = [
  { value: 'reception', label: 'Recepción' },
  { value: 'before', label: 'Antes' },
  { value: 'evidence', label: 'Evidencia' },
  { value: 'after', label: 'Después' },
];

export default function OrderDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const { role } = useAuth();
  const queryClient = useQueryClient();
  const [comment, setComment] = useState('');
  const [status, setStatus] = useState('en_proceso');
  const [progress, setProgress] = useState('0');
  const [diagnosis, setDiagnosis] = useState('');
  const [recommendations, setRecommendations] = useState('');
  const [photoType, setPhotoType] = useState<PhotoType>('reception');
  const [photoNote, setPhotoNote] = useState('');
  const [uploading, setUploading] = useState(false);
  const [notice, setNotice] = useState<{ tone: 'success' | 'danger'; title: string; message: string } | null>(null);
  const [viewerIndex, setViewerIndex] = useState<number | null>(null);

  useEffect(() => {
    if (!notice) {
      return;
    }
    const timer = setTimeout(() => setNotice(null), 4000);
    return () => clearTimeout(timer);
  }, [notice]);

  function notify(tone: 'success' | 'danger', title: string, message: string) {
    setNotice({ tone, title, message });
  }

  const query = useQuery({
    queryKey: ['order', id],
    queryFn: () => fetchOrder(id),
    enabled: Boolean(id && id !== 'index' && !Number.isNaN(Number(id))),
  });

  useEffect(() => {
    if (!query.data) {
      return;
    }
    setStatus(query.data.status);
    setProgress(String(query.data.progress ?? 0));
    setDiagnosis(query.data.diagnosis ?? '');
    setRecommendations(query.data.recommendations ?? '');
  }, [query.data?.id, query.data?.status, query.data?.progress, query.data?.diagnosis, query.data?.recommendations]);

  const canWork = role === 'mecanico' || role === 'asesor' || role === 'admin';
  const isMechanic = role === 'mecanico';
  const statuses = statusesForRole(role);
  const photos = (query.data?.photos ?? []) as Photo[];
  const gallery = useMemo(
    () =>
      photoTypes.flatMap((type) =>
        photos
          .filter((photo) => photo.type === type.value)
          .map((photo) => ({
            url: photo.url,
            title: photo.type_label ?? type.label,
            caption: [photo.description, photo.user, photo.created_at].filter(Boolean).join(' · '),
            id: photo.id,
          })),
      ),
    [photos],
  );
  const requirements = photoRequirements(photos);

  function refreshOrder() {
    void queryClient.invalidateQueries({ queryKey: ['order', id] });
    void queryClient.invalidateQueries({ queryKey: ['orders'] });
    void queryClient.invalidateQueries({ queryKey: ['dashboard'] });
  }

  const statusMutation = useMutation({
    mutationFn: async () => {
      if ((status === 'completada' || status === 'entregada') && !requirements.ready) {
        throw new Error(
          !requirements.hasInitial
            ? 'Sube fotos de recepción o antes del trabajo para finalizar.'
            : 'Sube fotos de después del trabajo para finalizar.',
        );
      }

      await updateOrderStatus(id, {
        status,
        progress: Math.min(100, Math.max(0, Number(progress) || 0)),
        diagnosis,
        recommendations,
      });
    },
    onSuccess: () => {
      refreshOrder();
      notify('success', 'Avance guardado', 'El estado, el progreso y las notas se actualizaron.');
    },
    onError: (error: unknown) => notify('danger', 'No se pudo guardar el avance', apiErrorMessage(error)),
  });

  const commentMutation = useMutation({
    mutationFn: async () => api.post(`/orders/${id}/comments`, { comment: comment.trim() }),
    onSuccess: () => {
      setComment('');
      refreshOrder();
      notify('success', 'Comentario publicado', 'La observación quedó registrada en la orden.');
    },
    onError: (error: unknown) => notify('danger', 'No se pudo publicar', apiErrorMessage(error, 'Inténtalo de nuevo.')),
  });

  function selectStatus(next: string) {
    setStatus(next);
    if (next === 'recibida') {
      setProgress('0');
    }
    if (next === 'completada' || next === 'entregada') {
      setProgress('100');
    }
  }

  async function uploadPhoto(source: 'camera' | 'library') {
    const permission =
      source === 'camera'
        ? await ImagePicker.requestCameraPermissionsAsync()
        : await ImagePicker.requestMediaLibraryPermissionsAsync();

    if (!permission.granted) {
      notify('danger', 'Permiso requerido', 'Activa la cámara o galería para subir evidencias.');
      return;
    }

    const pickerOptions = {
      quality: 0.8 as const,
      exif: false,
      allowsEditing: false,
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
    };

    const result =
      source === 'camera'
        ? await ImagePicker.launchCameraAsync(pickerOptions)
        : await ImagePicker.launchImageLibraryAsync(pickerOptions);

    if (result.canceled || !result.assets[0]) {
      return;
    }

    setUploading(true);
    try {
      await uploadOrderPhoto(id, result.assets[0], photoType, photoNote);
      setPhotoNote('');
      refreshOrder();
      notify('success', 'Foto publicada', 'La evidencia se agregó a la orden.');
    } catch (error: unknown) {
      notify('danger', 'No se pudo subir la foto', apiErrorMessage(error, 'Inténtalo de nuevo.'));
    } finally {
      setUploading(false);
    }
  }

  if (!id || id === 'index' || Number.isNaN(Number(id))) {
    return <Redirect href="/(app)/orders" />;
  }

  if (query.isLoading) {
    return <Loading />;
  }

  if (query.isError || !query.data) {
    return (
      <ScrollScreen onRefresh={() => query.refetch()} refreshing={query.isRefetching}>
        <Empty icon="cloud-offline-outline" title="No se pudo cargar la orden" actionLabel="Reintentar" onAction={() => query.refetch()}>
          {apiErrorMessage(query.error, 'Revisa tu conexión e inténtalo de nuevo.')}
        </Empty>
      </ScrollScreen>
    );
  }

  const order = query.data;

  return (
    <View style={styles.page}>
      {notice ? <Toast tone={notice.tone} title={notice.title} message={notice.message} /> : null}
      <PhotoLightbox
        photos={gallery}
        index={viewerIndex}
        onClose={() => setViewerIndex(null)}
        onChange={setViewerIndex}
      />
      <ScrollScreen onRefresh={() => query.refetch()} refreshing={query.isRefetching}>
      <View style={styles.head}>
        <Title>{order.order_number}</Title>
        <Badge tone={statusTone(order.status)}>{order.status_label ?? order.status}</Badge>
      </View>
      <Card>
        <Muted>{order.vehicle ? `${order.vehicle.brand} ${order.vehicle.model} · ${order.vehicle.plate}` : ''}</Muted>
        {order.description ? <Muted>{order.description}</Muted> : null}
        {order.client ? <Muted>{`Cliente: ${order.client.name}`}</Muted> : null}
        <View style={styles.progressTrack}>
          <View style={[styles.progressFill, { width: `${Math.min(100, order.progress ?? 0)}%` }]} />
        </View>
        <Muted>{`Avance: ${order.progress ?? 0}%`}</Muted>
        {order.diagnosis ? <Muted>{`Diagnóstico: ${order.diagnosis}`}</Muted> : null}
      </Card>

      {photoTypes.map((type) => {
        const items = photos.filter((photo) => photo.type === type.value);
        if (!items.length && !canWork) {
          return null;
        }
        return (
          <Card key={type.value}>
            <Text style={styles.strong}>{`${type.label} (${items.length})`}</Text>
            {items.length ? (
              <View style={styles.photos}>
                {items.map((photo) => {
                  const index = gallery.findIndex((item) => item.id === photo.id);
                  return (
                    <Pressable
                      key={photo.id}
                      style={styles.photoWrap}
                      onPress={() => setViewerIndex(index >= 0 ? index : 0)}
                      accessibilityRole="button"
                      accessibilityLabel={`Ver foto de ${type.label}`}
                    >
                      <Image source={{ uri: photo.url }} style={styles.photo} />
                      <View style={styles.photoZoom}>
                        <Ionicons name="expand-outline" size={14} color="#fff" />
                      </View>
                      {photo.description ? <Muted>{photo.description}</Muted> : null}
                    </Pressable>
                  );
                })}
              </View>
            ) : (
              <Muted>Aún no hay fotos en esta categoría.</Muted>
            )}
          </Card>
        );
      })}

      {(order.comments ?? []).length ? (
        <Card>
          <Text style={styles.strong}>Observaciones</Text>
          {(order.comments ?? []).map((item: OrderComment) => (
            <View key={item.id} style={styles.comment}>
              <Text style={styles.commentAuthor}>{item.user?.name ?? 'Taller'}</Text>
              <Muted>{item.comment}</Muted>
              {item.created_at ? <Muted>{item.created_at}</Muted> : null}
            </View>
          ))}
        </Card>
      ) : null}

      {canWork ? (
        <Card>
          <Text style={styles.strong}>{isMechanic ? 'Registro de mantenimiento' : 'Actualizar estado'}</Text>
          <View style={styles.row}>
            {statuses.map((item) => (
              <Pressable
                key={item.value}
                onPress={() => selectStatus(item.value)}
                style={[styles.chip, status === item.value && styles.chipOn]}
              >
                <Text style={status === item.value ? styles.chipOnText : styles.chipText}>{item.label}</Text>
              </Pressable>
            ))}
          </View>
          <Text style={styles.label}>Avance del trabajo (%)</Text>
          <TextInput
            value={progress}
            onChangeText={(value) => setProgress(value.replace(/[^0-9]/g, '').slice(0, 3))}
            keyboardType="numeric"
            placeholder="0 a 100"
            placeholderTextColor={colors.muted}
            style={styles.input}
          />
          <Text style={styles.label}>Diagnóstico técnico</Text>
          <TextInput
            value={diagnosis}
            onChangeText={setDiagnosis}
            placeholder="Novedades encontradas en la revisión"
            placeholderTextColor={colors.muted}
            style={[styles.input, styles.multiline]}
            multiline
          />
          <Text style={styles.label}>Recomendaciones para el cliente</Text>
          <TextInput
            value={recommendations}
            onChangeText={setRecommendations}
            placeholder="Sugerencias de mantenimiento"
            placeholderTextColor={colors.muted}
            style={[styles.input, styles.multiline]}
            multiline
          />
          {(status === 'completada' || status === 'entregada') && !requirements.ready ? (
            <Muted>
              {!requirements.hasInitial
                ? 'Para finalizar faltan fotos de recepción o antes del trabajo.'
                : 'Para finalizar faltan fotos de después del trabajo.'}
            </Muted>
          ) : null}
          <Button
            title={statusMutation.isPending ? 'Guardando…' : 'Guardar avance'}
            disabled={statusMutation.isPending}
            onPress={() => statusMutation.mutate()}
          />
        </Card>
      ) : null}

      {canWork ? (
        <Card>
          <Text style={styles.strong}>Evidencia fotográfica</Text>
          <View style={styles.row}>
            {photoTypes.map((type) => (
              <Pressable
                key={type.value}
                onPress={() => setPhotoType(type.value)}
                style={[styles.chip, photoType === type.value && styles.chipOn]}
              >
                <Text style={photoType === type.value ? styles.chipOnText : styles.chipText}>{type.label}</Text>
              </Pressable>
            ))}
          </View>
          <TextInput
            value={photoNote}
            onChangeText={setPhotoNote}
            placeholder="Descripción técnica (opcional)"
            placeholderTextColor={colors.muted}
            style={styles.input}
          />
          <View style={styles.actions}>
            <View style={styles.action}>
              <Button title={uploading ? 'Subiendo…' : 'Cámara'} disabled={uploading} onPress={() => void uploadPhoto('camera')} />
            </View>
            <View style={styles.action}>
              <Button
                title="Galería"
                variant="secondary"
                disabled={uploading}
                onPress={() => void uploadPhoto('library')}
              />
            </View>
          </View>
        </Card>
      ) : null}

      {canWork ? (
        <Card>
          <Text style={styles.strong}>Comentario técnico</Text>
          <TextInput
            value={comment}
            onChangeText={setComment}
            placeholder="Observación para el expediente"
            placeholderTextColor={colors.muted}
            style={[styles.input, styles.multiline]}
            multiline
          />
          <Button
            title="Publicar"
            disabled={!comment.trim() || commentMutation.isPending}
            onPress={() => commentMutation.mutate()}
          />
        </Card>
      ) : null}
      </ScrollScreen>
    </View>
  );
}

const styles = StyleSheet.create({
  page: { flex: 1 },
  head: { gap: 8 },
  strong: { fontWeight: '800', color: colors.text, fontSize: 16 },
  label: { fontWeight: '700', color: colors.textSecondary, fontSize: 12, textTransform: 'uppercase', letterSpacing: 0.3 },
  photos: { flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
  photoWrap: { width: 108 },
  photo: { width: 108, height: 82, borderRadius: 10, backgroundColor: colors.border },
  photoZoom: {
    position: 'absolute',
    top: 6,
    right: 6,
    width: 24,
    height: 24,
    borderRadius: 12,
    backgroundColor: 'rgba(15, 31, 51, 0.55)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  row: { flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
  chip: {
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 999,
    backgroundColor: colors.soft,
    borderWidth: 1,
    borderColor: colors.border,
    minHeight: 36,
    justifyContent: 'center',
  },
  chipOn: { backgroundColor: colors.primary, borderColor: colors.primary },
  chipText: { color: colors.muted, fontSize: 12, fontWeight: '600' },
  chipOnText: { color: '#fff', fontSize: 12, fontWeight: '700' },
  input: {
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 14,
    padding: 12,
    backgroundColor: colors.soft,
    color: colors.text,
  },
  multiline: { minHeight: 88, textAlignVertical: 'top' },
  actions: { flexDirection: 'row', gap: 8 },
  action: { flex: 1 },
  comment: { gap: 2, paddingTop: 8, borderTopWidth: 1, borderTopColor: colors.border },
  commentAuthor: { fontWeight: '700', color: colors.text, fontSize: 13 },
  progressTrack: { height: 8, borderRadius: 999, backgroundColor: colors.border, overflow: 'hidden' },
  progressFill: { height: 8, backgroundColor: colors.primary },
});
