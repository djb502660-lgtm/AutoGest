import { colors, radius, shadow } from '../lib/theme';
import { Ionicons } from '@expo/vector-icons';
import {
  ActivityIndicator,
  Pressable,
  RefreshControl,
  ScrollView,
  StyleSheet,
  Text,
  View,
  type ViewProps,
} from 'react-native';

export function Screen({ children, style, ...props }: ViewProps) {
  return (
    <View style={[styles.screen, style]} {...props}>
      {children}
    </View>
  );
}

export function Card({ children }: { children: React.ReactNode }) {
  return <View style={styles.card}>{children}</View>;
}

export function Title({ children }: { children: string }) {
  return <Text style={styles.title}>{children}</Text>;
}

export function Muted({ children }: { children: string }) {
  return <Text style={styles.muted}>{children}</Text>;
}

export function Loading() {
  return (
    <View style={styles.center}>
      <ActivityIndicator color={colors.primary} size="large" />
    </View>
  );
}

export function Empty({
  icon = 'information-circle-outline',
  title,
  children,
  actionLabel,
  onAction,
}: {
  icon?: keyof typeof Ionicons.glyphMap;
  title?: string;
  children: string;
  actionLabel?: string;
  onAction?: () => void;
}) {
  return (
    <View style={styles.emptyWrap}>
      <View style={styles.emptyIcon}>
        <Ionicons name={icon} size={30} color={colors.primary} />
      </View>
      {title ? <Text style={styles.emptyTitle}>{title}</Text> : null}
      <Text style={styles.empty}>{children}</Text>
      {actionLabel && onAction ? (
        <Pressable onPress={onAction} style={styles.emptyAction}>
          <Text style={styles.emptyActionText}>{actionLabel}</Text>
        </Pressable>
      ) : null}
    </View>
  );
}

export function ScrollScreen({
  children,
  onRefresh,
  refreshing = false,
}: {
  children: React.ReactNode;
  onRefresh?: () => void;
  refreshing?: boolean;
}) {
  return (
    <ScrollView
      style={styles.screen}
      contentContainerStyle={styles.content}
      keyboardShouldPersistTaps="handled"
      refreshControl={
        onRefresh ? (
          <RefreshControl refreshing={refreshing} onRefresh={onRefresh} tintColor={colors.primary} />
        ) : undefined
      }
    >
      {children}
    </ScrollView>
  );
}

export function Button({
  title,
  onPress,
  variant = 'primary',
  disabled,
}: {
  title: string;
  onPress: () => void;
  variant?: 'primary' | 'secondary' | 'danger' | 'ghost';
  disabled?: boolean;
}) {
  const variantStyle = {
    primary: styles.btn_primary,
    secondary: styles.btn_secondary,
    danger: styles.btn_danger,
    ghost: styles.btn_ghost,
  }[variant];
  const textStyle = {
    primary: styles.btnText_primary,
    secondary: styles.btnText_secondary,
    danger: styles.btnText_danger,
    ghost: styles.btnText_ghost,
  }[variant];

  return (
    <Pressable
      disabled={disabled}
      onPress={onPress}
      style={[styles.button, variantStyle, disabled && styles.btnDisabled]}
      accessibilityRole="button"
    >
      <Text style={[styles.buttonText, textStyle]}>{title}</Text>
    </Pressable>
  );
}

export function Badge({
  children,
  tone = 'neutral',
}: {
  children: string;
  tone?: 'info' | 'success' | 'warning' | 'danger' | 'neutral';
}) {
  const wrap = {
    info: styles.badge_info,
    success: styles.badge_success,
    warning: styles.badge_warning,
    danger: styles.badge_danger,
    neutral: styles.badge_neutral,
  }[tone];
  const text = {
    info: styles.badgeText_info,
    success: styles.badgeText_success,
    warning: styles.badgeText_warning,
    danger: styles.badgeText_danger,
    neutral: styles.badgeText_neutral,
  }[tone];

  return (
    <View style={[styles.badge, wrap]}>
      <Text style={[styles.badgeText, text]}>{children}</Text>
    </View>
  );
}

export function Stat({ label, value, wide = false }: { label: string; value: string | number; wide?: boolean }) {
  return (
    <View style={[styles.stat, wide && styles.statWide]}>
      <Text style={styles.statLabel}>{label}</Text>
      <Text style={styles.statValue}>{String(value)}</Text>
    </View>
  );
}

export function Section({ children }: { children: string }) {
  return <Text style={styles.section}>{children}</Text>;
}

export function Toast({
  tone = 'success',
  title,
  message,
}: {
  tone?: 'success' | 'danger' | 'info';
  title: string;
  message?: string;
}) {
  const icon =
    tone === 'success' ? 'checkmark-circle' : tone === 'danger' ? 'alert-circle' : 'information-circle';
  const color = tone === 'success' ? colors.success : tone === 'danger' ? colors.danger : colors.primary;

  return (
    <View style={[styles.toast, tone === 'success' && styles.toast_success, tone === 'danger' && styles.toast_danger, tone === 'info' && styles.toast_info]}>
      <Ionicons name={icon} size={22} color={color} />
      <View style={styles.toastBody}>
        <Text style={styles.toastTitle}>{title}</Text>
        {message ? <Text style={styles.toastMessage}>{message}</Text> : null}
      </View>
    </View>
  );
}

export function ListRow({
  title,
  subtitle,
  badge,
  tone = 'info',
  icon,
}: {
  title: string;
  subtitle?: string;
  badge?: string;
  tone?: 'info' | 'success' | 'warning' | 'danger' | 'neutral';
  icon?: keyof typeof Ionicons.glyphMap;
}) {
  return (
    <View style={styles.listRow}>
      {icon ? (
        <View style={styles.listIcon}>
          <Ionicons name={icon} size={18} color={colors.primary} />
        </View>
      ) : null}
      <View style={styles.listBody}>
        <Text style={styles.listTitle}>{title}</Text>
        {subtitle ? <Text style={styles.listSub}>{subtitle}</Text> : null}
      </View>
      {badge ? <Badge tone={tone}>{badge}</Badge> : <Ionicons name="chevron-forward" size={18} color={colors.border} />}
    </View>
  );
}

const styles = StyleSheet.create({
  screen: {
    flex: 1,
    backgroundColor: colors.page,
  },
  content: {
    padding: 18,
    gap: 14,
    paddingBottom: 28,
    flexGrow: 1,
  },
  card: {
    backgroundColor: colors.card,
    borderRadius: radius.lg,
    padding: 16,
    borderWidth: 1,
    borderColor: colors.border,
    gap: 8,
    ...shadow.card,
  },
  title: {
    fontSize: 24,
    fontWeight: '800',
    color: colors.text,
    letterSpacing: -0.4,
  },
  muted: {
    color: colors.muted,
    fontSize: 14,
    lineHeight: 21,
  },
  emptyWrap: {
    backgroundColor: colors.card,
    borderRadius: radius.lg,
    padding: 28,
    borderWidth: 1,
    borderColor: colors.border,
    alignItems: 'center',
    gap: 8,
    ...shadow.card,
  },
  emptyIcon: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: colors.primarySoft,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 6,
  },
  emptyTitle: {
    fontWeight: '800',
    fontSize: 17,
    color: colors.text,
    textAlign: 'center',
  },
  empty: {
    color: colors.muted,
    textAlign: 'center',
    fontSize: 14,
    lineHeight: 22,
    maxWidth: 280,
  },
  emptyAction: {
    marginTop: 10,
    backgroundColor: colors.primary,
    borderRadius: radius.md,
    paddingVertical: 12,
    paddingHorizontal: 20,
    minHeight: 46,
    justifyContent: 'center',
  },
  emptyActionText: { color: '#fff', fontWeight: '800' },
  center: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: colors.page,
  },
  button: {
    borderRadius: radius.md,
    paddingVertical: 14,
    alignItems: 'center',
    minHeight: 50,
    justifyContent: 'center',
  },
  btn_primary: { backgroundColor: colors.primary },
  btn_secondary: { backgroundColor: colors.primarySoft },
  btn_danger: { backgroundColor: colors.dangerSoft },
  btn_ghost: { backgroundColor: 'transparent' },
  btnDisabled: { opacity: 0.55 },
  buttonText: { fontWeight: '800', fontSize: 15 },
  btnText_primary: { color: '#fff' },
  btnText_secondary: { color: colors.primaryDark },
  btnText_danger: { color: colors.danger },
  btnText_ghost: { color: colors.textSecondary, fontWeight: '700' },
  badge: {
    alignSelf: 'flex-start',
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: radius.pill,
  },
  badge_info: { backgroundColor: colors.primarySoft },
  badge_success: { backgroundColor: colors.successSoft },
  badge_warning: { backgroundColor: colors.warningSoft },
  badge_danger: { backgroundColor: colors.dangerSoft },
  badge_neutral: { backgroundColor: colors.soft },
  badgeText: { fontSize: 11, fontWeight: '800', letterSpacing: 0.2 },
  badgeText_info: { color: colors.primaryDark },
  badgeText_success: { color: '#047857' },
  badgeText_warning: { color: '#B45309' },
  badgeText_danger: { color: colors.danger },
  badgeText_neutral: { color: colors.muted },
  stat: {
    backgroundColor: colors.card,
    borderRadius: radius.lg,
    paddingVertical: 16,
    paddingHorizontal: 14,
    borderWidth: 1,
    borderColor: colors.border,
    width: '31%',
    flexGrow: 1,
    minWidth: 96,
    ...shadow.card,
  },
  statWide: {
    width: '47%',
    minWidth: '47%',
  },
  statLabel: { color: colors.muted, fontSize: 11, fontWeight: '700', textTransform: 'uppercase', letterSpacing: 0.4 },
  statValue: { fontSize: 26, fontWeight: '800', color: colors.text, marginTop: 6, letterSpacing: -0.6 },
  section: {
    marginTop: 4,
    fontWeight: '800',
    color: colors.text,
    fontSize: 15,
    letterSpacing: -0.2,
  },
  listRow: {
    backgroundColor: colors.card,
    borderRadius: radius.lg,
    padding: 14,
    borderWidth: 1,
    borderColor: colors.border,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    ...shadow.card,
  },
  listIcon: {
    width: 40,
    height: 40,
    borderRadius: 12,
    backgroundColor: colors.primarySoft,
    alignItems: 'center',
    justifyContent: 'center',
  },
  listBody: { flex: 1, gap: 3 },
  listTitle: { fontWeight: '800', color: colors.text, fontSize: 15 },
  listSub: { color: colors.muted, fontSize: 13 },
  toast: {
    position: 'absolute',
    left: 18,
    right: 18,
    bottom: 24,
    zIndex: 20,
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 10,
    borderRadius: radius.lg,
    padding: 14,
    borderWidth: 1,
    ...shadow.card,
  },
  toast_success: { backgroundColor: colors.successSoft, borderColor: '#A7F3D0' },
  toast_danger: { backgroundColor: colors.dangerSoft, borderColor: '#FECACA' },
  toast_info: { backgroundColor: colors.primarySoft, borderColor: colors.border },
  toastBody: { flex: 1, gap: 2 },
  toastTitle: { fontWeight: '800', color: colors.text, fontSize: 15 },
  toastMessage: { color: colors.textSecondary, fontSize: 13, lineHeight: 18 },
});
