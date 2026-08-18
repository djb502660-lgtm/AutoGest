import axios, { isAxiosError } from 'axios';
import { apiOrigin, apiUrl } from './theme';
import { getToken } from './session';

export const api = axios.create({
  baseURL: apiUrl,
  timeout: 20000,
  headers: {
    Accept: 'application/json',
  },
});

api.interceptors.request.use(async (config) => {
  const token = await getToken();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

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

export type DashboardPayload = {
  role?: string;
  stats?: Record<string, number | string>;
  recent_orders?: Order[];
  pending_appointments?: Appointment[];
  appointments?: Appointment[];
};

export const modernStatuses = [
  { value: 'recibida', label: 'Recibida' },
  { value: 'en_proceso', label: 'En proceso' },
  { value: 'completada', label: 'Completada' },
  { value: 'entregada', label: 'Entregada' },
] as const;

export const legacyStatuses = [
  { value: 'pendiente', label: 'Pendiente' },
  { value: 'en_proceso', label: 'En proceso' },
  { value: 'completado', label: 'Completado' },
  { value: 'cancelado', label: 'Cancelado' },
] as const;

let mobileV1Available: boolean | null = null;

export async function hasMobileV1(): Promise<boolean> {
  if (mobileV1Available !== null) {
    return mobileV1Available;
  }

  try {
    await api.get('/dashboard');
    mobileV1Available = true;
  } catch (error) {
    mobileV1Available = isAxiosError(error) && error.response?.status === 404 ? false : false;
  }

  return mobileV1Available;
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

export async function updateOrderStatus(id: string | number, status: string, progress?: number): Promise<void> {
  const v1 = await hasMobileV1();

  if (v1) {
    await api.put(`/orders/${id}/status`, { status, progress: progress ?? 0 });
    return;
  }

  const legacyMap: Record<string, string> = {
    recibida: 'pendiente',
    completada: 'completado',
    entregada: 'completado',
    cancelada: 'cancelado',
  };

  await api.put(`/orders/${id}/status`, {
    status: legacyMap[status] ?? status,
  });
}

export async function fetchDashboard(): Promise<DashboardPayload> {
  try {
    return (await api.get('/dashboard')).data as DashboardPayload;
  } catch (error) {
    if (!isAxiosError(error) || error.response?.status !== 404) {
      throw error;
    }

    const [{ data: vehiclesData }, { data: ordersData }] = await Promise.all([
      api.get('/vehicles'),
      api.get('/orders'),
    ]);

    const vehicles: Vehicle[] = (vehiclesData.vehicles ?? []).map((item: Vehicle) => item);
    const orders: Order[] = (ordersData.orders ?? []).map((item: Record<string, unknown>) => normalizeOrder(item));
    const closed = new Set(['completada', 'entregada', 'cancelada', 'completado', 'cancelado']);

    return {
      stats: {
        vehicles: vehicles.length,
        open_orders: orders.filter((order) => !closed.has(order.status)).length,
      },
      recent_orders: orders.slice(0, 5),
      appointments: [],
    };
  }
}
