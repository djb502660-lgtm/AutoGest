/**
 * AutoGest Mobile App
 * @format
 */

import React, { useState } from 'react';
import { StatusBar, StyleSheet, useColorScheme, View } from 'react-native';
import {
  SafeAreaProvider,
} from 'react-native-safe-area-context';
import LoginScreen from './src/screens/LoginScreen';
import RegisterScreen from './src/screens/RegisterScreen';
import HomeScreen from './src/screens/HomeScreen';
import VehiclesScreen from './src/screens/VehiclesScreen';
import OrdersScreen from './src/screens/OrdersScreen';

type ScreenType = 'Login' | 'Register' | 'Home' | 'Vehicles' | 'Orders';

function App() {
  const isDarkMode = useColorScheme() === 'dark';
  const [currentScreen, setCurrentScreen] = useState<ScreenType>('Login');

  const navigation = {
    navigate: (screen: ScreenType) => setCurrentScreen(screen),
    reset: (options: any) => {
      setCurrentScreen(options.routes[0].name as ScreenType);
    },
  };

  const renderScreen = () => {
    switch (currentScreen) {
      case 'Login':
        return <LoginScreen navigation={navigation} />;
      case 'Register':
        return <RegisterScreen navigation={navigation} />;
      case 'Home':
        return <HomeScreen navigation={navigation} />;
      case 'Vehicles':
        return <VehiclesScreen navigation={navigation} />;
      case 'Orders':
        return <OrdersScreen navigation={navigation} />;
      default:
        return <LoginScreen navigation={navigation} />;
    }
  };

  return (
    <SafeAreaProvider>
      <StatusBar barStyle={isDarkMode ? 'light-content' : 'dark-content'} />
      <View style={styles.container}>
        {renderScreen()}
      </View>
    </SafeAreaProvider>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
});

export default App;