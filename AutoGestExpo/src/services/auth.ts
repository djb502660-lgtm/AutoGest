import AsyncStorage from '@react-native-async-storage/async-storage';

const TOKEN_KEY = '@autogest_token';
const USER_KEY = '@autogest_user';

// Servicio de autenticación
class AuthService {
  // Guardar token
  async saveToken(token: string): Promise<void> {
    try {
      await AsyncStorage.setItem(TOKEN_KEY, token);
    } catch (error) {
      console.error('Error al guardar token:', error);
    }
  }

  // Obtener token
  async getToken(): Promise<string | null> {
    try {
      const token = await AsyncStorage.getItem(TOKEN_KEY);
      return token;
    } catch (error) {
      console.error('Error al obtener token:', error);
      return null;
    }
  }

  // Guardar información del usuario
  async saveUser(user: any): Promise<void> {
    try {
      await AsyncStorage.setItem(USER_KEY, JSON.stringify(user));
    } catch (error) {
      console.error('Error al guardar usuario:', error);
    }
  }

  // Obtener información del usuario
  async getUser(): Promise<any | null> {
    try {
      const userJson = await AsyncStorage.getItem(USER_KEY);
      return userJson ? JSON.parse(userJson) : null;
    } catch (error) {
      console.error('Error al obtener usuario:', error);
      return null;
    }
  }

  // Eliminar token (logout)
  async removeToken(): Promise<void> {
    try {
      await AsyncStorage.removeItem(TOKEN_KEY);
    } catch (error) {
      console.error('Error al eliminar token:', error);
    }
  }

  // Eliminar usuario
  async removeUser(): Promise<void> {
    try {
      await AsyncStorage.removeItem(USER_KEY);
    } catch (error) {
      console.error('Error al eliminar usuario:', error);
    }
  }

  // Logout completo
  async logout(): Promise<void> {
    await this.removeToken();
    await this.removeUser();
  }

  // Verificar si está autenticado
  async isAuthenticated(): Promise<boolean> {
    const token = await this.getToken();
    return !!token;
  }
}

export default new AuthService();