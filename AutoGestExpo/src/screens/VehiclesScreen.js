import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  ActivityIndicator,
} from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import ApiService from '../services/api';

const VehiclesScreen = ({ navigation }) => {
  const [vehicles, setVehicles] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadVehicles();
  }, []);

  const loadVehicles = async () => {
    try {
      const response = await ApiService.getVehicles();
      setVehicles(response.data || []);
    } catch (error) {
      console.error('Error al cargar vehículos:', error);
    } finally {
      setLoading(false);
    }
  };

  const renderVehicle = ({ item }) => (
    <TouchableOpacity style={styles.vehicleCard}>
      <Text style={styles.vehicleMake}>{item.make} {item.model}</Text>
      <Text style={styles.vehicleYear}>Año: {item.year}</Text>
      <Text style={styles.vehiclePlate}>Placa: {item.license_plate}</Text>
      <Text style={styles.vehicleColor}>Color: {item.color}</Text>
    </TouchableOpacity>
  );

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Mis Vehículos</Text>
      </View>

      {loading ? (
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#3498db" />
          <Text style={styles.loadingText}>Cargando vehículos...</Text>
        </View>
      ) : (
        <FlatList
          data={vehicles}
          renderItem={renderVehicle}
          keyExtractor={(item) => item.id.toString()}
          contentContainerStyle={styles.listContainer}
          ListEmptyComponent={
            <View style={styles.emptyContainer}>
              <Text style={styles.emptyText}>No tienes vehículos registrados</Text>
            </View>
          }
        />
      )}
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  header: {
    backgroundColor: '#3498db',
    padding: 20,
    paddingTop: 40,
  },
  headerTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#ffffff',
  },
  listContainer: {
    padding: 20,
  },
  vehicleCard: {
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
  vehicleMake: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#2c3e50',
    marginBottom: 5,
  },
  vehicleYear: {
    fontSize: 14,
    color: '#7f8c8d',
    marginBottom: 3,
  },
  vehiclePlate: {
    fontSize: 14,
    color: '#7f8c8d',
    marginBottom: 3,
  },
  vehicleColor: {
    fontSize: 14,
    color: '#7f8c8d',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 10,
    color: '#7f8c8d',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
  },
  emptyText: {
    fontSize: 16,
    color: '#7f8c8d',
    textAlign: 'center',
  },
});

export default VehiclesScreen;