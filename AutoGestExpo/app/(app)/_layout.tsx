import { useAuth } from '../../src/lib/auth';
import { colors } from '../../src/lib/theme';
import { Ionicons } from '@expo/vector-icons';
import { Redirect, Tabs, useRouter } from 'expo-router';
import { StatusBar } from 'expo-status-bar';
import { ActivityIndicator, Alert, Platform, Text, TouchableOpacity, View } from 'react-native';

function tabIcon(on: keyof typeof Ionicons.glyphMap, off: keyof typeof Ionicons.glyphMap) {
  return ({ color, focused }: { color: string; focused: boolean }) => (
    <View
      style={{
        width: 42,
        height: 28,
        borderRadius: 14,
        alignItems: 'center',
        justifyContent: 'center',
        backgroundColor: focused ? colors.primarySoft : 'transparent',
      }}
    >
      <Ionicons name={focused ? on : off} size={20} color={color} />
    </View>
  );
}

function LogoutButton() {
  const { logout } = useAuth();
  const router = useRouter();

  async function signOut() {
    await logout();
    router.replace('/(auth)/login');
  }

  function onPress() {
    if (Platform.OS === 'web') {
      const confirmed = typeof window !== 'undefined' && window.confirm('¿Quieres salir de AutoGest?');
      if (confirmed) {
        void signOut();
      }
      return;
    }

    Alert.alert('Cerrar sesión', '¿Quieres salir de AutoGest?', [
      { text: 'Cancelar', style: 'cancel' },
      { text: 'Salir', style: 'destructive', onPress: () => void signOut() },
    ]);
  }

  return (
    <TouchableOpacity
      onPress={onPress}
      activeOpacity={0.7}
      hitSlop={{ top: 12, bottom: 12, left: 12, right: 12 }}
      style={{
        marginRight: 8,
        minWidth: 72,
        height: 36,
        paddingHorizontal: 12,
        borderRadius: 18,
        backgroundColor: colors.soft,
        alignItems: 'center',
        justifyContent: 'center',
        flexDirection: 'row',
        gap: 6,
      }}
      accessibilityRole="button"
      accessibilityLabel="Cerrar sesión"
    >
      <Ionicons name="log-out-outline" size={18} color={colors.text} />
      <Text style={{ color: colors.text, fontWeight: '800', fontSize: 13 }}>Salir</Text>
    </TouchableOpacity>
  );
}

export default function AppLayout() {
  const { user, role, ready, loggingIn } = useAuth();

  if (!ready || loggingIn) {
    return (
      <View style={{ flex: 1, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.bg }}>
        <ActivityIndicator color="#fff" size="large" />
      </View>
    );
  }

  if (!user) {
    return <Redirect href="/(auth)/login" />;
  }

  const showVehicles = role === 'cliente' || role === 'admin';
  const showChat = role === 'cliente';
  const showAppointments = role === 'asesor' || role === 'admin';
  const showExpenses = role === 'cliente';
  const showUsers = role === 'admin';

  return (
    <>
      <StatusBar style="dark" />
      <Tabs
        screenOptions={{
          headerStyle: { backgroundColor: colors.page },
          headerTitleStyle: { fontWeight: '800', color: colors.text, fontSize: 18, letterSpacing: -0.3 },
          headerShadowVisible: false,
          headerTintColor: colors.text,
          headerRight: () => <LogoutButton />,
          tabBarActiveTintColor: colors.primary,
          tabBarInactiveTintColor: colors.muted,
          tabBarLabelStyle: { fontSize: 11, fontWeight: '700', marginTop: 2 },
          tabBarItemStyle: { minHeight: 54, paddingTop: 6 },
          tabBarStyle: {
            backgroundColor: colors.card,
            borderTopColor: colors.border,
            height: 70,
            paddingTop: 6,
            paddingBottom: 10,
          },
        }}
      >
        <Tabs.Screen
          name="home"
          options={{
            title: 'Inicio',
            tabBarIcon: tabIcon('home', 'home-outline'),
          }}
        />
        <Tabs.Screen
          name="vehicles/index"
          options={{
            title: role === 'admin' ? 'Flota' : 'Vehículos',
            href: showVehicles ? undefined : null,
            tabBarIcon: tabIcon('car-sport', 'car-sport-outline'),
          }}
        />
        <Tabs.Screen name="vehicles/[id]" options={{ href: null, title: 'Vehículo' }} />
        <Tabs.Screen
          name="orders/index"
          options={{ title: 'Órdenes', tabBarIcon: tabIcon('clipboard', 'clipboard-outline') }}
        />
        <Tabs.Screen name="orders/[id]" options={{ href: null, title: 'Orden' }} />
        <Tabs.Screen
          name="appointments/index"
          options={{
            title: 'Solicitudes',
            href: showAppointments ? undefined : null,
            tabBarIcon: tabIcon('calendar', 'calendar-outline'),
          }}
        />
        <Tabs.Screen name="appointments/[id]" options={{ href: null, title: 'Solicitud' }} />
        <Tabs.Screen
          name="chatbot"
          options={{ title: 'Chat', href: showChat ? undefined : null, tabBarIcon: tabIcon('chatbubbles', 'chatbubbles-outline') }}
        />
        <Tabs.Screen
          name="expenses"
          options={{ title: 'Gastos', href: showExpenses ? undefined : null, tabBarIcon: tabIcon('wallet', 'wallet-outline') }}
        />
        <Tabs.Screen
          name="users/index"
          options={{
            title: 'Equipo',
            href: showUsers ? undefined : null,
            tabBarIcon: tabIcon('people', 'people-outline'),
          }}
        />
        <Tabs.Screen name="users/[id]" options={{ href: null, title: 'Usuario' }} />
      </Tabs>
    </>
  );
}
