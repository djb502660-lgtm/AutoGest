import { useAuth } from '../../src/lib/auth';
import { colors } from '../../src/lib/theme';
import { Redirect } from 'expo-router';
import { useState } from 'react';
import { KeyboardAvoidingView, Platform, Pressable, StyleSheet, Text, TextInput, View } from 'react-native';

export default function LoginScreen() {
  const { login, user } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  if (user) {
    return <Redirect href="/(app)/home" />;
  }

  async function onSubmit() {
    setBusy(true);
    setError('');
    try {
      await login(email.trim(), password);
    } catch (e: any) {
      setError(e?.response?.data?.message ?? e?.response?.data?.email?.[0] ?? 'No se pudo iniciar sesión.');
    } finally {
      setBusy(false);
    }
  }

  return (
    <KeyboardAvoidingView style={styles.wrap} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <View style={styles.hero}>
        <Text style={styles.badge}>AutoGest</Text>
        <Text style={styles.heroTitle}>Tu taller, en el celular</Text>
        <Text style={styles.heroCopy}>Consulta vehículos, órdenes y evidencias con tu cuenta Sanctum.</Text>
      </View>
      <View style={styles.card}>
        <Text style={styles.title}>Inicia sesión</Text>
        <TextInput
          autoCapitalize="none"
          keyboardType="email-address"
          placeholder="correo@ejemplo.com"
          placeholderTextColor={colors.muted}
          style={styles.input}
          value={email}
          onChangeText={setEmail}
        />
        <TextInput
          placeholder="Contraseña"
          placeholderTextColor={colors.muted}
          secureTextEntry
          style={styles.input}
          value={password}
          onChangeText={setPassword}
        />
        {error ? <Text style={styles.error}>{error}</Text> : null}
        <Pressable disabled={busy} onPress={onSubmit} style={[styles.button, busy && styles.buttonOff]}>
          <Text style={styles.buttonText}>{busy ? 'Entrando…' : 'Entrar'}</Text>
        </Pressable>
        <Text style={styles.hint}>Demo: cliente1@autogest.test / password</Text>
      </View>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  wrap: { flex: 1, backgroundColor: colors.bg },
  hero: { padding: 28, paddingTop: 80, paddingBottom: 32 },
  badge: { color: '#7dd3fc', fontWeight: '800', marginBottom: 10, letterSpacing: 1, textTransform: 'uppercase', fontSize: 12 },
  heroTitle: { color: '#fff', fontSize: 30, fontWeight: '800', letterSpacing: -0.6 },
  heroCopy: { color: '#cbd5e1', marginTop: 10, fontSize: 15, lineHeight: 22 },
  card: {
    flex: 1,
    backgroundColor: colors.page,
    borderTopLeftRadius: 28,
    borderTopRightRadius: 28,
    padding: 24,
    gap: 14,
  },
  title: { fontSize: 22, fontWeight: '800', color: colors.text },
  input: {
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 12,
    padding: 14,
    fontSize: 16,
    backgroundColor: colors.card,
    color: colors.text,
  },
  button: { backgroundColor: colors.primary, borderRadius: 12, padding: 16, alignItems: 'center', marginTop: 4 },
  buttonOff: { opacity: 0.7 },
  buttonText: { color: '#fff', fontWeight: '800', fontSize: 16 },
  error: { color: colors.danger, fontWeight: '600' },
  hint: { color: colors.muted, fontSize: 12, textAlign: 'center' },
});
