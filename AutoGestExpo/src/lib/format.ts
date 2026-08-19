export function displayFirstName(name?: string | null): string {
  const first = (name ?? '').trim().split(/\s+/)[0] ?? '';
  if (!first) {
    return '';
  }
  return first.charAt(0).toUpperCase() + first.slice(1).toLowerCase();
}

export function roleLabel(role?: string | null): string {
  if (role === 'asesor') {
    return 'Asesor';
  }
  if (role === 'mecanico') {
    return 'Mecánico';
  }
  if (role === 'admin') {
    return 'Admin';
  }
  if (role === 'cliente') {
    return 'Cliente';
  }
  return 'Usuario';
}
