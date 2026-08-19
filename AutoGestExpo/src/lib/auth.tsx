import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { createContext, useContext, useEffect, useMemo, useState, type ReactNode } from 'react';
import { isAxiosError } from 'axios';
import { api, onUnauthorized, type AuthUser, type Role } from './api';
import { clearSession, getStoredUser, getToken, saveSession } from './session';

type AuthContextValue = {
  user: AuthUser | null;
  role: Role | null;
  ready: boolean;
  loggingIn: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
};

const AuthContext = createContext<AuthContextValue | undefined>(undefined);
const queryClient = new QueryClient();

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [ready, setReady] = useState(false);
  const [loggingIn, setLoggingIn] = useState(false);

  useEffect(() => {
    onUnauthorized(() => {
      queryClient.clear();
      setUser(null);
    });
    return () => onUnauthorized(null);
  }, []);

  useEffect(() => {
    (async () => {
      try {
        const token = await getToken();
        const stored = await getStoredUser();
        if (token && stored) {
          setUser(stored);
          setReady(true);
          try {
            const { data } = await api.get('/user');
            setUser(data.user ?? stored);
          } catch (error) {
            const status = isAxiosError(error) ? error.response?.status : undefined;
            if (status === 401 || status === 403) {
              await clearSession();
              setUser(null);
            }
          }
          return;
        }
        await clearSession();
        setUser(null);
      } catch {
        await clearSession();
        setUser(null);
      } finally {
        setReady(true);
      }
    })();
  }, []);

  const value = useMemo<AuthContextValue>(
    () => ({
      user,
      role: user?.role ?? null,
      ready,
      loggingIn,
      login: async (email: string, password: string) => {
        setLoggingIn(true);
        try {
          await clearSession();
          queryClient.clear();
          setUser(null);
          const { data } = await api.post('/login', { email: email.trim().toLowerCase(), password });
          await saveSession(data.token, data.user);
          queryClient.clear();
          setUser(data.user);
        } finally {
          setLoggingIn(false);
        }
      },
      logout: async () => {
        try {
          await api.post('/logout');
        } catch {
          // Local session must drop even if Render is asleep.
        } finally {
          await clearSession();
          queryClient.clear();
          setUser(null);
        }
      },
    }),
    [loggingIn, ready, user],
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
