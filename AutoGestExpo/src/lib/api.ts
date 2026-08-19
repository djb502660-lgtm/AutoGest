import axios, { isAxiosError, type AxiosError, type InternalAxiosRequestConfig } from 'axios';
import { Platform } from 'react-native';
import { clearSession, getToken } from './session';
import { apiHost, apiOrigin, apiUrl } from './theme';

type RetryConfig = InternalAxiosRequestConfig & { __retryCount?: number };

function isFormDataBody(data: unknown): boolean {
  return typeof FormData !== 'undefined' && data instanceof FormData;
}

let unauthorizedHandler: (() => void) | null = null;

export function onUnauthorized(handler: (() => void) | null): void {
  unauthorizedHandler = handler;
}

function requestPath(config?: InternalAxiosRequestConfig): string {
  return `${config?.url ?? ''}`;
}

export const api = axios.create({
  baseURL: apiUrl,
  timeout: Platform.OS === 'web' ? 45000 : 20000,
  headers: {
    Accept: 'application/json',
  },
  transitional: {
    clarifyTimeoutError: true,
  },
  ...(Platform.OS === 'web' ? {} : { adapter: 'fetch' as const }),
});

if (__DEV__) {
  console.log(`[AutoGest API] ${Platform.OS} → ${apiUrl}`);
}

api.interceptors.request.use(async (config) => {
  const token = await getToken();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  if (config.data && !isFormDataBody(config.data) && !config.headers['Content-Type']) {
    config.headers['Content-Type'] = 'application/json';
  }
  return config;
});

api.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    const config = error.config as RetryConfig | undefined;
    const method = (config?.method ?? 'get').toLowerCase();
    const path = requestPath(config);
    const networkFail = !error.response;
    const retries = config?.__retryCount ?? 0;

    if (config && networkFail && method === 'get' && retries < 1) {
      config.__retryCount = retries + 1;
      await new Promise((resolve) => setTimeout(resolve, 800));
      return api.request(config);
    }

    if (error.response?.status === 401 && !path.includes('/login')) {
      await clearSession();
      unauthorizedHandler?.();
    }

    if (__DEV__) {
      console.warn('[AutoGest API] request failed', {
        url: `${config?.baseURL ?? apiUrl}${path}`,
        method,
        code: error.code,
        message: error.message,
        status: error.response?.status ?? null,
        platform: Platform.OS,
      });
    }

    return Promise.reject(error);
  },
);

export type Role = 'admin' | 'asesor' | 'mecanico' | 'cliente';

export type AuthUser = {
  id: number;
  name: string;
  email: string;
  role: Role;
  phone?: string | null;
};

export type Vehicle = {
  id: number;
  plate: string;
  brand: string;
  model: string;
  year?: number;
  mileage?: number;
  status?: string;
  status_label?: string;
  client?: { id: number; name: string } | null;
};

export type Order = {
  id: number;
  order_number: string;
  description: string;
  status: string;
  status_label?: string;
  priority?: string;
  progress?: number;
  diagnosis?: string | null;
  recommendations?: string | null;
  vehicle?: Vehicle | null;
  client?: { id: number; name: string; phone?: string } | null;
  mechanic?: { id: number; name: string } | null;
  photos?: Photo[];
  comments?: { id: number; comment: string; user?: { name?: string }; created_at?: string }[];
};

export type Photo = {
  id: number;
  url: string;
  description?: string | null;
  type?: string;
  type_label?: string;
  user?: string | null;
  created_at?: string;
};

export type Appointment = {
  id: number;
  status: string;
  status_label?: string;
  service_type?: string;
  description?: string;
  requested_date?: string;
  requested_time?: string;
  advisor_notes?: string | null;
  client?: { id: number; name: string; phone?: string } | null;
  vehicle?: Vehicle | null;
  order_number?: string | null;
};

export type StaffUser = {
  id: number;
  name: string;
  email: string;
  role: Role | string;
  phone?: string | null;
  status?: string;
  vehicles_count?: number;
  assigned_orders_count?: number;
  last_login_at?: string | null;
  created_at?: string;
  vehicles?: { id: number; plate: string; brand: string; model: string; year?: number }[];
};

export type DashboardPayload = {
  role?: string;
  message?: string;
  stats?: Record<string, number | string>;
  recent_orders?: Order[];
  pending_appointments?: Appointment[];
  appointments?: Appointment[];
};

export const mechanicStatuses = [
  { value: 'recibida', label: 'En espera' },
  { value: 'en_proceso', label: 'En proceso' },
  { value: 'completada', label: 'Trabajo terminado' },
] as const;

export const advisorStatuses = [
  { value: 'recibida', label: 'Recibida' },
  { value: 'en_proceso', label: 'En proceso' },
  { value: 'completada', label: 'Completada' },
  { value: 'entregada', label: 'Entregada' },
] as const;

export const modernStatuses = advisorStatuses;

export function statusesForRole(role: string | null) {
  return role === 'asesor' || role === 'admin' ? advisorStatuses : mechanicStatuses;
}

export type OrderStatusPayload = {
  status: string;
  progress?: number;
  diagnosis?: string | null;
  recommendations?: string | null;
};

export type PhotoType = 'reception' | 'before' | 'after' | 'evidence';

export function apiErrorMessage(error: unknown, fallback = 'Ocurrió un error'): string {
  if (isAxiosError(error)) {
    if (error.code === 'ECONNABORTED' || error.code === 'ETIMEDOUT' || error.code === 'ERR_CANCELED') {
      return `El servidor (${apiHost}) tardó demasiado. Espera unos segundos e intenta de nuevo.`;
    }
    if (!error.response) {
      const detail = error.code ? `${error.message} [${error.code}]` : error.message;
      return `Sin conexión con ${apiHost}. ${detail}`;
    }
    const data = error.response?.data as { message?: string; errors?: Record<string, string[]> } | undefined;
    if (data?.message) {
      return data.message;
    }
    const first = data?.errors ? Object.values(data.errors).flat()[0] : undefined;
    if (typeof first === 'string') {
      return first;
    }
  }
  if (error instanceof Error && error.message) {
    return error.message;
  }
  return fallback;
}

export function photoRequirements(photos: Photo[] = []) {
  const hasInitial = photos.some((photo) => photo.type === 'reception' || photo.type === 'before');
  const hasFinal = photos.some((photo) => photo.type === 'after');
  return { hasInitial, hasFinal, ready: hasInitial && hasFinal };
}

function statusLabel(status: string): string {
  const map: Record<string, string> = {
    pendiente: 'Pendiente',
    en_proceso: 'En proceso',
    completado: 'Completado',
    completada: 'Completada',
    cancelado: 'Cancelado',
    cancelada: 'Cancelada',
    recibida: 'Recibida',
    entregada: 'Entregada',
  };
  return map[status] ?? status;
}

export function normalizePhoto(raw: Record<string, unknown>): Photo {
  const path = raw.photo_path as string | undefined;
  const url =
    (raw.url as string | undefined) ??
    (path ? `${apiOrigin}/storage/${path.replace(/^\//, '')}` : '');

  return {
    id: raw.id as number,
    url,
    description: (raw.description as string | null | undefined) ?? null,
    type: (raw.type as string | undefined) ?? (raw.photo_type as string | undefined),
    type_label: (raw.type_label as string | undefined) ?? (raw.photo_type as string | undefined),
    user: (raw.user as string | null | undefined) ?? null,
    created_at: raw.created_at as string | undefined,
  };
}

export function normalizeOrder(raw: Record<string, unknown>): Order {
  const photos = Array.isArray(raw.photos) ? raw.photos.map((item) => normalizePhoto(item as Record<string, unknown>)) : [];

  return {
    id: raw.id as number,
    order_number: raw.order_number as string,
    description: raw.description as string,
    status: raw.status as string,
    status_label: (raw.status_label as string | undefined) ?? statusLabel(raw.status as string),
    priority: raw.priority as string | undefined,
    progress: raw.progress as number | undefined,
    diagnosis: (raw.diagnosis as string | null | undefined) ?? null,
    recommendations: (raw.recommendations as string | null | undefined) ?? null,
    vehicle: raw.vehicle as Vehicle | null | undefined,
    client: raw.client as Order['client'],
    mechanic: raw.mechanic as Order['mechanic'],
    photos,
    comments: raw.comments as Order['comments'],
  };
}

export async function fetchOrder(id: string | number): Promise<Order> {
  const { data } = await api.get(`/orders/${id}`);
  return normalizeOrder(data.order as Record<string, unknown>);
}

const toApiStatus: Record<string, string> = {
  pendiente: 'recibida',
  completado: 'completada',
  cancelado: 'cancelada',
};

export async function updateOrderStatus(id: string | number, payload: OrderStatusPayload): Promise<void> {
  await api.put(`/orders/${id}/status`, {
    status: toApiStatus[payload.status] ?? payload.status,
    progress: payload.progress,
    diagnosis: payload.diagnosis?.trim() || undefined,
    recommendations: payload.recommendations?.trim() || undefined,
  });
}

function jpegFileName(original?: string | null, ext = 'jpg'): string {
  const stamp = Date.now();
  const base = (original ?? 'evidence')
    .replace(/\.[^.]+$/, '')
    .replace(/[^\w.-]+/g, '_')
    .slice(0, 40) || 'evidence';
  return `${base}-${stamp}.${ext}`;
}

function extensionForMime(mime: string): string {
  if (mime.includes('png')) {
    return 'png';
  }
  if (mime.includes('webp')) {
    return 'webp';
  }
  if (mime.includes('gif')) {
    return 'gif';
  }
  return 'jpg';
}

async function photoPart(asset: { uri: string; fileName?: string | null; mimeType?: string | null }) {
  if (Platform.OS === 'web') {
    const blob = await (await fetch(asset.uri)).blob();
    const type = blob.type?.startsWith('image/') ? blob.type : 'image/jpeg';
    const ext = extensionForMime(type);
    return new File([blob], jpegFileName(asset.fileName, ext), { type });
  }

  return {
    uri: asset.uri,
    name: jpegFileName(asset.fileName),
    type: 'image/jpeg',
  };
}

export async function uploadOrderPhoto(
  id: string | number,
  asset: { uri: string; fileName?: string | null; mimeType?: string | null },
  type: PhotoType,
  description?: string,
): Promise<void> {
  const token = await getToken();
  const form = new FormData();
  form.append('type', type);
  if (description?.trim()) {
    form.append('description', description.trim());
  }
  form.append('photo', (await photoPart(asset)) as unknown as Blob);

  const response = await fetch(`${apiUrl}/orders/${id}/photos`, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
    body: form,
  });

  if (!response.ok) {
    const data = (await response.json().catch(() => ({}))) as { message?: string; errors?: Record<string, string[]> };
    const first = data.errors ? Object.values(data.errors).flat()[0] : undefined;
    throw new Error(data.message || first || 'No se pudo subir la foto');
  }
}

function hasDashboardStats(data: DashboardPayload): boolean {
  return Boolean(data.stats && Object.keys(data.stats).length);
}

async function composeOperationsDashboard(role = 'admin'): Promise<DashboardPayload> {
  const [{ data: vehiclesData }, { data: ordersData }, appointmentsRes, usersRes] = await Promise.all([
    api.get('/vehicles'),
    api.get('/orders'),
    api.get('/appointments?status=pendiente').catch(() => ({ data: { appointments: [] } })),
    api.get('/users').catch(() => ({ data: { users: [] } })),
  ]);

  const vehicles: Vehicle[] = vehiclesData.vehicles ?? [];
  const orders: Order[] = (ordersData.orders ?? []).map((item: Record<string, unknown>) => normalizeOrder(item));
  const appointments: Appointment[] = appointmentsRes.data.appointments ?? [];
  const users: StaffUser[] = usersRes.data.users ?? [];
  const closed = new Set(['completada', 'entregada', 'cancelada', 'completado', 'cancelado']);

  return {
    role,
    stats: {
      vehicles: vehicles.length,
      open_orders: orders.filter((order) => !closed.has(order.status)).length,
      pending_appointments: appointments.length,
      users: users.length,
    },
    recent_orders: orders.slice(0, 8),
    pending_appointments: appointments.slice(0, 8),
  };
}

export async function fetchDashboard(): Promise<DashboardPayload> {
  try {
    const data = (await api.get('/dashboard')).data as DashboardPayload;
    if (hasDashboardStats(data)) {
      return data;
    }
    if (data.role === 'admin' || data.message) {
      return composeOperationsDashboard(data.role ?? 'admin');
    }
    return data;
  } catch (error) {
    if (!isAxiosError(error) || error.response?.status !== 404) {
      throw error;
    }

    return composeOperationsDashboard();
  }
}
