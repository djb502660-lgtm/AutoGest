import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ScrollView,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import ApiService from '../services/api';
import AuthService from '../services/auth';

interface HomeScreenProps {
  navigation: any;
}

const HomeScreen: React.FC<HomeScreenProps> = ({ navigation }) => {
  const [userInfo, setUserInfo] = React.useState<any>(null);

  React.useEffect(() => {
    loadUserInfo();
  }, []);

  const loadUserInfo = async () => {
    try {
      const user = await AuthService.getUser();
      setUserInfo(user);
    } catch (error) {
      console.error('Error al cargar usuario:', error);
    }
  };

  const handleLogout = async () => {
    try {
      await ApiService.logout();
      navigation.reset({
        index: 0,
        routes: [{ name: 'Login' }],
      });
    } catch (error) {
      console.error('Error al cerrar sesión:', error);
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView style={styles.scrollView}>
        <View style={styles.header}>
          <Text style={styles.welcomeText}>Bienvenido</Text>
          {userInfo && (
            <Text style={styles.userName}>{userInfo.name}</Text>
          )}
        </View>

        <View style={styles.menuContainer}>
          <TouchableOpacity
            style={styles.menuItem}
            onPress={() => navigation.navigate('Vehicles')}
          >
            <View style={styles.menuItemContent}>
              <View style={styles.iconCircle}>
                <Text style={styles.iconText}>🚗</Text>
              </View>
              <Text style={styles.menuItemText}>Mis Vehículos</Text>
            </View>
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.menuItem}
            onPress={() => navigation.navigate('Orders')}
          >
            <View style={styles.menuItemContent}>
              <View style={styles.iconCircle}>
                <Text style={styles.iconText}>🔧</Text>
              </View>
              <Text style={styles.menuItemText}>Órdenes de Servicio</Text>
            </View>
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.menuItem}
            onPress={() => navigation.navigate('Maintenances')}
          >
            <View style={styles.menuItemContent}>
              <View style={styles.iconCircle}>
                <Text style={styles.iconText}>🔩</Text>
              </View>
              <Text style={styles.menuItemText}>Mantenimientos</Text>
            </View>
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.menuItem}
            onPress={() => navigation.navigate('Profile')}
          >
            <View style={styles.menuItemContent}>
              <View style={styles.iconCircle}>
                <Text style={styles.iconText}>👤</Text>
              </View>
              <Text style={styles.menuItemText}>Mi Perfil</Text>
            </View>
          </TouchableOpacity>
        </View>

        <TouchableOpacity style={styles.logoutButton} onPress={handleLogout}>
          <Text style={styles.logoutButtonText}>Cerrar Sesión</Text>
        </TouchableOpacity>
      </ScrollView>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  scrollView: {
    flex: 1,
  },
  header: {
    backgroundColor: '#3498db',
    padding: 20,
    paddingTop: 40,
    borderBottomLeftRadius: 20,
    borderBottomRightRadius: 20,
    marginBottom: 20,
  },
  welcomeText: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#ffffff',
    marginBottom: 5,
  },
  userName: {
    fontSize: 18,
    color: '#ffffff',
  },
  menuContainer: {
    paddingHorizontal: 20,
  },
  menuItem: {
    backgroundColor: '#ffffff',
    padding: 20,
    borderRadius: 12,
    marginBottom: 15,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  menuItemContent: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  iconCircle: {
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: '#e8f4f8',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 15,
  },
  iconText: {
    fontSize: 24,
  },
  menuItemText: {
    fontSize: 18,
    fontWeight: '600',
    color: '#2c3e50',
  },
  logoutButton: {
    backgroundColor: '#e74c3c',
    padding: 15,
    borderRadius: 12,
    margin: 20,
    alignItems: 'center',
  },
  logoutButtonText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: 'bold',
  },
});

export default HomeScreen;