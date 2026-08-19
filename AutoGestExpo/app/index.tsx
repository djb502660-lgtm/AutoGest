import { useAuth } from '../src/lib/auth';
import { colors } from '../src/lib/theme';
import { Redirect } from 'expo-router';
import { ActivityIndicator, View } from 'react-native';

export default function Index() {
  const { user, ready } = useAuth();

  if (!ready) {
    return (
      <View style={{ flex: 1, alignItems: 'center', justifyContent: 'center', backgroundColor: colors.bg }}>
        <ActivityIndicator color="#fff" />
      </View>
    );
  }

  return <Redirect href={user ? '/(app)/home' : '/(auth)/login'} />;
}
