import { useAuth } from '../../src/lib/auth';
import { colors } from '../../src/lib/theme';
import { Ionicons } from '@expo/vector-icons';
import { Redirect, Tabs } from 'expo-router';
import { StatusBar } from 'expo-status-bar';

function tabIcon(on: keyof typeof Ionicons.glyphMap, off: keyof typeof Ionicons.glyphMap) {
  return ({ color, size, focused }: { color: string; size: number; focused: boolean }) => (
    <Ionicons name={focused ? on : off} size={size} color={color} />
  );
}

export default function AppLayout() {
  const { user, role } = useAuth();

  if (!user) {
    return <Redirect href="/(auth)/login" />;
  }

  const showVehicles = role === 'cliente';
  const showChat = role === 'cliente';
  const showAppointments = role === 'asesor' || role === 'admin';
  const showExpenses = role === 'cliente';

  return (
    <>
      <StatusBar style="dark" />
      <Tabs
        screenOptions={{
        headerStyle: { backgroundColor: colors.card },
        headerTitleStyle: { fontWeight: '800', color: colors.text, fontSize: 18 },
        headerShadowVisible: false,
        headerTintColor: colors.text,
        tabBarActiveTintColor: colors.primary,
        tabBarInactiveTintColor: colors.muted,
        tabBarLabelStyle: { fontSize: 11, fontWeight: '700' },
        tabBarStyle: {
          backgroundColor: colors.card,
          borderTopColor: colors.border,
          height: 64,
          paddingTop: 6,
          paddingBottom: 8,
        },
      }}
    >
      <Tabs.Screen
        name="home"
        options={{ title: 'Inicio', tabBarIcon: tabIcon('home', 'home-outline') }}
      />
      <Tabs.Screen
        name="vehicles/index"
        options={{ title: 'Vehículos', href: showVehicles ? undefined : null, tabBarIcon: tabIcon('car-sport', 'car-sport-outline') }}
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
    </Tabs>
    </>
  );
}
