import { apiErrorMessage } from '../../src/lib/api';
import { useAuth } from '../../src/lib/auth';
import { apiHost, colors, radius } from '../../src/lib/theme';
import { Redirect } from 'expo-router';
import { useState } from 'react';
import { Image, KeyboardAvoidingView, Platform, Pressable, StyleSheet, Text, TextInput, View } from 'react-native';

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
    if (!email.trim() || !password) {
      setError('Escribe tu correo y contraseña.');
      setBusy(false);
      return;
    }
    try {
      await login(email.trim(), password);
    } catch (e: unknown) {
      setError(apiErrorMessage(e, 'No se pudo iniciar sesión. Revisa tu conexión e intenta de nuevo.'));
    } finally {
      setBusy(false);
    }
  }

  return (
    <KeyboardAvoidingView style={styles.wrap} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <View style={styles.hero}>
        <Image source={require('../../assets/icon.png')} style={styles.logo} />
        <Text style={styles.badge}>AutoGest</Text>
        <Text style={styles.heroTitle}>Tu taller, en el celular</Text>
        <Text style={styles.heroCopy}>Consulta vehículos, órdenes y evidencias con tu cuenta.</Text>
      </View>
      <View style={styles.card}>
        <Text style={styles.title}>Inicia sesión</Text>
        <TextInput
          autoCapitalize="none"
          keyboardType="email-address"
          autoComplete="email"
          placeholder="Correo"
          placeholderTextColor={colors.muted}
          style={styles.input}
          value={email}
          onChangeText={setEmail}
        />
        <TextInput
          placeholder="Contraseña"
          placeholderTextColor={colors.muted}
          autoComplete="password"
          secureTextEntry
          style={styles.input}
          value={password}
          onChangeText={setPassword}
        />
        {error ? <Text style={styles.error}>{error}</Text> : null}
        <Pressable disabled={busy} onPress={onSubmit} style={[styles.button, busy && styles.buttonOff]}>
          <Text style={styles.buttonText}>{busy ? 'Entrando…' : 'Entrar'}</Text>
        </Pressable>
        {__DEV__ ? <Text style={styles.apiHint}>Servidor: {apiHost}</Text> : null}
      </View>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  wrap: { flex: 1, backgroundColor: colors.bg },
  hero: { padding: 28, paddingTop: 64, paddingBottom: 28, alignItems: 'center' },
  logo: { width: 88, height: 88, borderRadius: 22, marginBottom: 16 },
  badge: { color: '#BFDBFE', fontWeight: '800', marginBottom: 8, letterSpacing: 1.4, textTransform: 'uppercase', fontSize: 12 },
  heroTitle: { color: '#fff', fontSize: 28, fontWeight: '800', letterSpacing: -0.6, textAlign: 'center' },
  heroCopy: { color: '#DBEAFE', marginTop: 8, fontSize: 15, lineHeight: 22, textAlign: 'center', maxWidth: 280 },
  card: {
    flex: 1,
    backgroundColor: colors.page,
    borderTopLeftRadius: radius.xl,
    borderTopRightRadius: radius.xl,
    padding: 24,
    gap: 14,
  },
  title: { fontSize: 22, fontWeight: '800', color: colors.text, marginBottom: 4 },
  input: {
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.md,
    padding: 14,
    fontSize: 16,
    backgroundColor: colors.card,
    color: colors.text,
  },
  button: { backgroundColor: colors.primary, borderRadius: radius.md, padding: 16, alignItems: 'center', marginTop: 4 },
  buttonOff: { opacity: 0.7 },
  buttonText: { color: '#fff', fontWeight: '800', fontSize: 16 },
  error: { color: colors.danger, fontWeight: '600' },
  apiHint: { color: colors.muted, fontSize: 12, textAlign: 'center', marginTop: 4 },
});
