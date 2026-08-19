import Constants from 'expo-constants';

export const colors = {
  bg: '#0F52A3',
  page: '#EEF4FB',
  card: '#ffffff',
  muted: '#5B6B80',
  text: '#0F1F33',
  textSecondary: '#334155',
  primary: '#0284C7',
  primaryDark: '#0369A1',
  primarySoft: '#E0F2FE',
  accent: '#0F52A3',
  warning: '#D97706',
  warningSoft: '#FEF3C7',
  danger: '#DC2626',
  dangerSoft: '#FEE2E2',
  success: '#059669',
  successSoft: '#D1FAE5',
  border: '#D7E3F0',
  soft: '#F8FBFF',
};

export const radius = {
  sm: 10,
  md: 14,
  lg: 20,
  xl: 28,
  pill: 999,
};

export const shadow = {
  card: {
    shadowColor: '#0F1F33',
    shadowOpacity: 0.08,
    shadowRadius: 16,
    shadowOffset: { width: 0, height: 6 },
    elevation: 3,
  },
};

export function statusTone(status?: string): 'info' | 'success' | 'warning' | 'danger' | 'neutral' {
  const value = (status ?? '').toLowerCase();
  if (['completada', 'entregada', 'completado', 'activo', 'confirmada', 'convertida'].includes(value)) {
    return 'success';
  }
  if (['en_proceso', 'pendiente', 'recibida'].includes(value)) {
    return 'warning';
  }
  if (['cancelada', 'cancelado', 'rechazada'].includes(value)) {
    return 'danger';
  }
  return 'info';
}

export const RENDER_API_URL = 'https://autogest-jlm7.onrender.com/api';

function firstHttpUrl(...values: Array<string | undefined>): string {
  for (const value of values) {
    const trimmed = value?.trim();
    if (trimmed && /^https?:\/\//i.test(trimmed)) {
      return trimmed.replace(/\/+$/, '');
    }
  }
  return RENDER_API_URL;
}

export const apiUrl = __DEV__
  ? firstHttpUrl(process.env.EXPO_PUBLIC_API_URL, RENDER_API_URL)
  : firstHttpUrl(
      Constants.expoConfig?.extra?.apiUrl as string | undefined,
      process.env.EXPO_PUBLIC_API_URL,
      RENDER_API_URL,
    );

export const apiOrigin = apiUrl.replace(/\/api(\/v1)?\/?$/, '');

export const apiHost = (() => {
  try {
    return new URL(apiUrl).host;
  } catch {
    return apiUrl;
  }
})();
