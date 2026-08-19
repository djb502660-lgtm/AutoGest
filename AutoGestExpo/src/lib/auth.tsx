import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { createContext, useContext, useEffect, useMemo, useState, type ReactNode } from 'react';
import { api, type AuthUser, type Role } from './api';
import { clearSession, getStoredUser, getToken, saveSession } from './session';

type AuthContextValue = {
  user: AuthUser | null;
  role: Role | null;
  ready: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
};

const AuthContext = createContext<AuthContextValue | undefined>(undefined);
const queryClient = new QueryClient();

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [ready, setReady] = useState(false);

  useEffect(() => {
    (async () => {
      const token = await getToken();
      const stored = await getStoredUser();
      if (token && stored) {
        try {
          const { data } = await api.get('/user');
          setUser(data.user);
        } catch {
          await clearSession();
          setUser(null);
        }
      }
      setReady(true);
    })();
  }, []);

  const value = useMemo<AuthContextValue>(
    () => ({
      user,
      role: user?.role ?? null,
      ready,
      login: async (email: string, password: string) => {
        const { data } = await api.post('/login', { email, password });
        await saveSession(data.token, data.user);
        setUser(data.user);
      },
      logout: async () => {
        await clearSession();
        setUser(null);
        queryClient.clear();
        api.post('/logout').catch(() => undefined);
      },
    }),
    [ready, user],
  );

  return (
    <QueryClientProvider client={queryClient}>
      <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
    </QueryClientProvider>
  );
}

export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext);
  if (!ctx) {
    throw new Error('useAuth must be used inside AuthProvider');
  }
  return ctx;
}
