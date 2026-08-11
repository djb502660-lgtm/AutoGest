// Configuración de la API
export const API_CONFIG = {
  // URL base de la API - cambiar según el entorno
  BASE_URL: 'http://192.168.1.8/backend/api', // IP permanente de Laragon
  // BASE_URL: 'http://localhost/backend/api', // Para desarrollo local solo
  // BASE_URL: 'https://autogest-taller-management-api.onrender.com/api', // Para producción
  
  // Endpoints
  ENDPOINTS: {
    LOGIN: '/login',
    REGISTER: '/register',
    USER: '/user',
    LOGOUT: '/logout',
    VEHICLES: '/vehicles',
    ORDERS: '/orders',
    MAINTENANCES: '/maintenances',
    USERS: '/users',
  },
  
  // Configuración de timeout
  TIMEOUT: 10000, // 10 segundos
};

export default API_CONFIG;