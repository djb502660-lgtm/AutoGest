import { useAuth } from '../src/lib/auth';
import { Redirect } from 'expo-router';

export default function Index() {
  const { user, ready } = useAuth();

  if (!ready) {
    return null;
  }

  return <Redirect href={user ? '/(app)/home' : '/(auth)/login'} />;
}
