import React from 'react';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { NavigationContainer } from '@react-navigation/native';
import { CustomBottomNav } from '../components/navigation/CustomBottomNav';

// Pantallas
import HomeScreen from '../screens/HomeScreen';
import EventScreen from '../screens/EventScreen';
import FightersScreen from '../screens/FightersScreen';
import FighterDetailScreen from '../screens/FighterDetailScreen';
import RegisterScreen from '../screens/RegisterScreen';
import ProfileScreen from '../screens/ProfileScreen';
import FighterFormScreen from '../screens/FighterForm';
import RegisterUserScreen from '../screens/RegisterUserScreen';
import LoginScreen from '../screens/LoginScreen';
import BuyTicketsScreen from '../screens/BuyTicketsScreenNEW';
import AdminBoletosScreen from '../screens/admin/AdminBoletosScreen';
import { AdminPanel } from '../screens/admin';

const Tab = createBottomTabNavigator();

export default function AppNavigator() {
  return (
    <NavigationContainer>
      <Tab.Navigator
        screenOptions={{
          headerShown: false,
        }}
        tabBar={(props) => <CustomBottomNav {...props} />}
      >
        {/* Tabs visibles en el navbar */}
        <Tab.Screen name="Home" component={HomeScreen} />
        <Tab.Screen name="Event" component={EventScreen} />
        <Tab.Screen name="Register" component={RegisterScreen} />
        <Tab.Screen name="Fighters" component={FightersScreen} />
        <Tab.Screen name="FighterDetail" component={FighterDetailScreen} />
        <Tab.Screen name="Tickets" component={ProfileScreen} />

        {/* Pantallas ocultas (sin tab) */}
        <Tab.Screen
          name="Profile"
          component={ProfileScreen}
          options={{ tabBarButton: () => null }}
        />
        <Tab.Screen
          name="FighterForm"
          component={FighterFormScreen}
          options={{ tabBarButton: () => null }}
        />
        <Tab.Screen
          name="RegisterUser"
          component={RegisterUserScreen}
          options={{ tabBarButton: () => null }}
        />
        <Tab.Screen
          name="Login"
          component={LoginScreen}
          options={{ tabBarButton: () => null }}
        />
        <Tab.Screen
          name="AdminPanel"
          component={AdminPanel}
          options={{ tabBarButton: () => null }}
        />
        <Tab.Screen
          name="BuyTickets"
          component={BuyTicketsScreen}
          options={{ tabBarButton: () => null }}
        />
        <Tab.Screen
          name="AdminBoletos"
          component={AdminBoletosScreen}
          options={{ tabBarButton: () => null }}
        />
      </Tab.Navigator>
    </NavigationContainer>
  );
}
