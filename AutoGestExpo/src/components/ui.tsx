import { colors } from '../lib/theme';
import {
  ActivityIndicator,
  Pressable,
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

export function Empty({ children }: { children: string }) {
  return (
    <View style={styles.emptyWrap}>
      <Text style={styles.empty}>{children}</Text>
    </View>
  );
}

export function ScrollScreen({ children }: { children: React.ReactNode }) {
  return (
    <ScrollView style={styles.screen} contentContainerStyle={styles.content} keyboardShouldPersistTaps="handled">
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
    <Pressable disabled={disabled} onPress={onPress} style={[styles.button, variantStyle, disabled && styles.btnDisabled]}>
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

export function Stat({ label, value }: { label: string; value: string | number }) {
  return (
    <View style={styles.stat}>
      <Text style={styles.statLabel}>{label}</Text>
      <Text style={styles.statValue}>{String(value)}</Text>
    </View>
  );
}

export function Section({ children }: { children: string }) {
  return <Text style={styles.section}>{children}</Text>;
}

const styles = StyleSheet.create({
  screen: {
    flex: 1,
    backgroundColor: colors.page,
  },
  content: {
    padding: 16,
    gap: 12,
    paddingBottom: 32,
  },
  card: {
    backgroundColor: colors.card,
    borderRadius: 16,
    padding: 16,
    borderWidth: 1,
    borderColor: colors.border,
    gap: 6,
    shadowColor: '#0f172a',
    shadowOpacity: 0.06,
    shadowRadius: 10,
    shadowOffset: { width: 0, height: 2 },
    elevation: 2,
  },
  title: {
    fontSize: 24,
    fontWeight: '800',
    color: colors.text,
    letterSpacing: -0.3,
  },
  muted: {
    color: colors.muted,
    fontSize: 14,
    lineHeight: 20,
  },
  emptyWrap: {
    backgroundColor: colors.card,
    borderRadius: 16,
    padding: 20,
    borderWidth: 1,
    borderColor: colors.border,
  },
  empty: {
    color: colors.muted,
    textAlign: 'center',
    fontSize: 14,
    lineHeight: 20,
  },
  center: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: colors.page,
  },
  button: {
    borderRadius: 12,
    paddingVertical: 14,
    alignItems: 'center',
  },
  btn_primary: { backgroundColor: colors.primary },
  btn_secondary: { backgroundColor: colors.primarySoft },
  btn_danger: { backgroundColor: colors.dangerSoft },
  btn_ghost: { backgroundColor: 'transparent' },
  btnDisabled: { opacity: 0.55 },
  buttonText: { fontWeight: '800', fontSize: 15 },
  btnText_primary: { color: '#fff' },
  btnText_secondary: { color: colors.primary },
  btnText_danger: { color: colors.danger },
  btnText_ghost: { color: colors.muted, fontWeight: '700' },
  badge: {
    alignSelf: 'flex-start',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 999,
  },
  badge_info: { backgroundColor: colors.primarySoft },
  badge_success: { backgroundColor: colors.successSoft },
  badge_warning: { backgroundColor: colors.warningSoft },
  badge_danger: { backgroundColor: colors.dangerSoft },
  badge_neutral: { backgroundColor: colors.soft },
  badgeText: { fontSize: 12, fontWeight: '700' },
  badgeText_info: { color: colors.primary },
  badgeText_success: { color: '#047857' },
  badgeText_warning: { color: '#b45309' },
  badgeText_danger: { color: colors.danger },
  badgeText_neutral: { color: colors.muted },
  stat: {
    backgroundColor: colors.card,
    borderRadius: 16,
    padding: 14,
    borderWidth: 1,
    borderColor: colors.border,
    width: '48%',
    flexGrow: 1,
    minWidth: 140,
  },
  statLabel: { color: colors.muted, fontSize: 12, fontWeight: '600' },
  statValue: { fontSize: 24, fontWeight: '800', color: colors.text, marginTop: 4 },
  section: {
    marginTop: 8,
    fontWeight: '800',
    color: colors.text,
    fontSize: 16,
  },
});
