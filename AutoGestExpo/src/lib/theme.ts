export const colors = {
  bg: '#0f172a',
  page: '#f0f4f9',
  card: '#ffffff',
  muted: '#64748b',
  text: '#0f172a',
  textSecondary: '#334155',
  primary: '#0284c7',
  primarySoft: '#e0f2fe',
  accent: '#0d9488',
  warning: '#f59e0b',
  warningSoft: '#fef3c7',
  danger: '#ef4444',
  dangerSoft: '#fee2e2',
  success: '#10b981',
  successSoft: '#d1fae5',
  border: '#e2e8f0',
  soft: '#f8fafc',
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

export const apiUrl =
  process.env.EXPO_PUBLIC_API_URL ?? 'https://autogest-jlm7.onrender.com/api';

export const apiOrigin = apiUrl.replace(/\/api(\/v1)?\/?$/, '');
