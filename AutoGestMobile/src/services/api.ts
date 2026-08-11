import axios from 'axios';
import API_CONFIG from '../config/api';
import AuthService from './auth';

// Servicio de API
class ApiService {
  private baseURL: string;

  constructor() {
    this.baseURL = API_CONFIG.BASE_URL;
  }

  // Obtener token de autenticación
  private async getToken(): Promise<string | null> {
    return await AuthService.getToken();
  }

  // Configurar headers de autenticación
  private async getAuthHeaders() {
    const token = await this.getToken();
    if (token) {
      return {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      };
    }
    return {
      'Content-Type': 'application/json',
    };
  }

  // Login
  async login(email: string, password: string) {
    try {
      const response = await axios.post(
        `${this.baseURL}${API_CONFIG.ENDPOINTS.LOGIN}`,
        { email, password },
        { headers: await this.getAuthHeaders(), timeout: API_CONFIG.TIMEOUT }
      );
      
      // Guardar token y usuario
      if (response.data.token) {
        await AuthService.saveToken(response.data.token);
      }
      if (response.data.user) {
        await AuthService.saveUser(response.data.user);
      }
      
      return response.data;
    } catch (error) {
      this.handleError(error);
      throw error;
    }
  }

  // Registro
  async register(userData: {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
  }) {
    try {
      const response = await axios.post(
        `${this.baseURL}${API_CONFIG.ENDPOINTS.REGISTER}`,
        userData,
        { headers: await this.getAuthHeaders(), timeout: API_CONFIG.TIMEOUT }
      );
      return response.data;
    } catch (error) {
      this.handleError(error);
      throw error;
    }
  }

  // Obtener información del usuario
  async getUser() {
    try {
      const response = await axios.get(
        `${this.baseURL}${API_CONFIG.ENDPOINTS.USER}`,
        { headers: await this.getAuthHeaders(), timeout: API_CONFIG.TIMEOUT }
      );
      return response.data;
    } catch (error) {
      this.handleError(error);
      throw error;
    }
  }

  // Logout
  async logout() {
    try {
      const response = await axios.post(
        `${this.baseURL}${API_CONFIG.ENDPOINTS.LOGOUT}`,
        {},
        { headers: await this.getAuthHeaders(), timeout: API_CONFIG.TIMEOUT }
      );
      // Limpiar almacenamiento local
      await AuthService.logout();
      return response.data;
    } catch (error) {
      this.handleError(error);
      throw error;
    }
  }

  // Obtener vehículos
  async getVehicles() {
    try {
      const response = await axios.get(
        `${this.baseURL}${API_CONFIG.ENDPOINTS.VEHICLES}`,
        { headers: await this.getAuthHeaders(), timeout: API_CONFIG.TIMEOUT }
      );
      return response.data;
    } catch (error) {
      this.handleError(error);
      throw error;
    }
  }

  // Obtener órdenes de servicio
  async getOrders() {
    try {
      const response = await axios.get(
        `${this.baseURL}${API_CONFIG.ENDPOINTS.ORDERS}`,
        { headers: await this.getAuthHeaders(), timeout: API_CONFIG.TIMEOUT }
      );
      return response.data;
    } catch (error) {
      this.handleError(error);
      throw error;
    }
  }

  // Manejo de errores
  private handleError(error: any) {
    if (error.response) {
      // El servidor respondió con un error
      console.error('Error de respuesta:', error.response.data);
    } else if (error.request) {
      // La solicitud se hizo pero no hubo respuesta
      console.error('Error de red:', error.request);
    } else {
      // Error al configurar la solicitud
      console.error('Error de configuración:', error.message);
    }
  }
}

export default new ApiService();