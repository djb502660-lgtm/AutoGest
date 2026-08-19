import 'react-native-gesture-handler';
import { AuthProvider, useAuth } from '../src/lib/auth';
import { colors } from '../src/lib/theme';
import Ionicons from '@expo/vector-icons/Ionicons';
import { useFonts } from 'expo-font';
import { Stack } from 'expo-router';
import * as SplashScreen from 'expo-splash-screen';
import { StatusBar } from 'expo-status-bar';
import { Component, useEffect, useState, type ErrorInfo, type ReactNode } from 'react';
import { GestureHandlerRootView } from 'react-native-gesture-handler';
import { ActivityIndicator, Text, View } from 'react-native';

SplashScreen.preventAutoHideAsync().catch(() => undefined);

function hideSplash() {
  SplashScreen.hideAsync().catch(() => undefined);
}

class RootErrorBoundary extends Component<{ children: ReactNode }, { error: Error | null }> {
  state = { error: null as Error | null };

  static getDerivedStateFromError(error: Error) {
    return { error };
  }

  componentDidCatch(error: Error, info: ErrorInfo) {
    console.error(error, info);
  }

  render() {
    if (this.state.error) {
      return (
        <View style={{ flex: 1, backgroundColor: colors.page, padding: 28, justifyContent: 'center', gap: 8 }}>
          <Text style={{ color: colors.text, fontWeight: '800', fontSize: 22 }}>No se pudo abrir AutoGest</Text>
          <Text style={{ color: colors.muted, fontSize: 15, lineHeight: 22 }}>{this.state.error.message}</Text>
        </View>
      );
    }

    return this.props.children;
  }
}

function RootNav() {
  const { ready } = useAuth();
  const [fontsLoaded] = useFonts({
    ...Ionicons.font,
  });
  const [fontWaitExpired, setFontWaitExpired] = useState(false);
  const uiReady = ready && (fontsLoaded || fontWaitExpired);

  useEffect(() => {
    if (uiReady) {
      hideSplash();
    }
  }, [uiReady]);

  useEffect(() => {
    const splashTimer = setTimeout(hideSplash, 4000);
    const fontTimer = setTimeout(() => setFontWaitExpired(true), 6000);
    return () => {
      clearTimeout(splashTimer);
      clearTimeout(fontTimer);
    };
  }, []);

  if (!uiReady) {
    return (
      <View style={{ flex: 1, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.bg }}>
        <ActivityIndicator color="#fff" />
      </View>
    );
  }

  return (
    <Stack screenOptions={{ headerShown: false, contentStyle: { backgroundColor: colors.page } }}>
      <Stack.Screen name="index" />
      <Stack.Screen name="(auth)" />
      <Stack.Screen name="(app)" />
    </Stack>
  );
}

export default function RootLayout() {
  return (
    <GestureHandlerRootView style={{ flex: 1, backgroundColor: colors.bg }}>
      <RootErrorBoundary>
        <AuthProvider>
          <StatusBar style="light" />
          <RootNav />
        </AuthProvider>
      </RootErrorBoundary>
    </GestureHandlerRootView>
  );
}
