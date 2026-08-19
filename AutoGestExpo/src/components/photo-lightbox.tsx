import { colors, radius } from '../lib/theme';
import { Ionicons } from '@expo/vector-icons';
import { Image, Modal, Pressable, StyleSheet, Text, useWindowDimensions, View } from 'react-native';

export type LightboxPhoto = {
  url: string;
  title?: string;
  caption?: string;
};

export function PhotoLightbox({
  photos,
  index,
  onClose,
  onChange,
}: {
  photos: LightboxPhoto[];
  index: number | null;
  onClose: () => void;
  onChange: (index: number) => void;
}) {
  const { width, height } = useWindowDimensions();
  const photo = index !== null ? photos[index] : undefined;
  const hasPrev = index !== null && index > 0;
  const hasNext = index !== null && index < photos.length - 1;

  return (
    <Modal visible={photo != null} transparent animationType="fade" onRequestClose={onClose}>
      <View style={styles.backdrop}>
        <Pressable style={styles.close} onPress={onClose} accessibilityRole="button" accessibilityLabel="Cerrar">
          <Ionicons name="close" size={26} color="#fff" />
        </Pressable>

        {photo ? (
          <>
            <Image
              source={{ uri: photo.url }}
              resizeMode="contain"
              style={{ width: width - 16, height: height * 0.62, backgroundColor: '#000' }}
            />
            <View style={styles.meta}>
              {photo.title ? <Text style={styles.title}>{photo.title}</Text> : null}
              {photo.caption ? <Text style={styles.caption}>{photo.caption}</Text> : null}
              <Text style={styles.counter}>{`${(index ?? 0) + 1} de ${photos.length}`}</Text>
            </View>
            <View style={styles.nav}>
              <Pressable
                disabled={!hasPrev}
                onPress={() => hasPrev && onChange((index ?? 0) - 1)}
                style={[styles.navBtn, !hasPrev && styles.navOff]}
                accessibilityLabel="Foto anterior"
              >
                <Ionicons name="chevron-back" size={24} color="#fff" />
              </Pressable>
              <Pressable
                disabled={!hasNext}
                onPress={() => hasNext && onChange((index ?? 0) + 1)}
                style={[styles.navBtn, !hasNext && styles.navOff]}
                accessibilityLabel="Foto siguiente"
              >
                <Ionicons name="chevron-forward" size={24} color="#fff" />
              </Pressable>
            </View>
          </>
        ) : null}
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  backdrop: {
    flex: 1,
    backgroundColor: 'rgba(15, 31, 51, 0.94)',
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 8,
    paddingVertical: 24,
    gap: 14,
  },
  close: {
    position: 'absolute',
    top: 18,
    right: 18,
    zIndex: 2,
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: 'rgba(255,255,255,0.16)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  meta: { alignItems: 'center', gap: 4, paddingHorizontal: 16, maxWidth: 520 },
  title: { color: '#fff', fontWeight: '800', fontSize: 16 },
  caption: { color: '#D7E3F0', fontSize: 13, textAlign: 'center', lineHeight: 18 },
  counter: { color: colors.primarySoft, fontSize: 12, fontWeight: '700', marginTop: 4 },
  nav: { flexDirection: 'row', gap: 12 },
  navBtn: {
    width: 48,
    height: 48,
    borderRadius: radius.pill,
    backgroundColor: colors.primary,
    alignItems: 'center',
    justifyContent: 'center',
  },
  navOff: { opacity: 0.35 },
});
