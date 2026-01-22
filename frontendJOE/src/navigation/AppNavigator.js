import React from 'react';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { NavigationContainer } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { View, StyleSheet } from 'react-native';

// Importar pantallas
import HomeScreen from '../screens/HomeScreen';
import EventScreen from '../screens/EventScreen';
import RegisterScreen from '../screens/RegisterScreen';
import FightersScreen from '../screens/FightersScreen';
import ProfileScreen from '../screens/ProfileScreen';

const Tab = createBottomTabNavigator();

export default function AppNavigator() {
  return (
    <NavigationContainer>
      <Tab.Navigator
        screenOptions={{
          headerShown: false,
          tabBarStyle: styles.tabBar,
          tabBarActiveTintColor: '#FFD700',
          tabBarInactiveTintColor: '#888',
          tabBarLabelStyle: styles.tabLabel,
        }}
      >
        {/* 1. INICIO */}
        <Tab.Screen
          name="Home"
          component={HomeScreen}
          options={{
            tabBarLabel: 'Inicio',
            tabBarIcon: ({ color, focused }) => (
              <Ionicons 
                name={focused ? 'home' : 'home-outline'} 
                size={24} 
                color={color} 
              />
            ),
          }}
        />

        {/* 2. EVENTO */}
        <Tab.Screen
          name="Event"
          component={EventScreen}
          options={{
            tabBarLabel: 'Evento',
            tabBarIcon: ({ color, focused }) => (
              <Ionicons 
                name={focused ? 'calendar' : 'calendar-outline'} 
                size={24} 
                color={color} 
              />
            ),
          }}
        />

        {/* 3. INSCRIBIRSE - CENTRAL DESTACADO */}
        <Tab.Screen
          name="Register"
          component={RegisterScreen}
          options={{
            tabBarLabel: 'Inscribirse',
            tabBarIcon: ({ color, focused }) => (
              <View style={styles.centralButton}>
                <Ionicons name="add-circle" size={40} color="#000" />
              </View>
            ),
            tabBarButton: (props) => (
              <View style={styles.centralWrapper}>
                {/* @ts-ignore */}
                <props.children {...props} />
              </View>
            ),
          }}
        />

        {/* 4. PELEADORES */}
        <Tab.Screen
          name="Fighters"
          component={FightersScreen}
          options={{
            tabBarLabel: 'Peleadores',
            tabBarIcon: ({ color, focused }) => (
              <Ionicons 
                name={focused ? 'people' : 'people-outline'} 
                size={24} 
                color={color} 
              />
            ),
          }}
        />

        {/* 5. MI CUENTA */}
        <Tab.Screen
          name="Profile"
          component={ProfileScreen}
          options={{
            tabBarLabel: 'Cuenta',
            tabBarIcon: ({ color, focused }) => (
              <Ionicons 
                name={focused ? 'person' : 'person-outline'} 
                size={24} 
                color={color} 
              />
            ),
          }}
        />
      </Tab.Navigator>
    </NavigationContainer>
  );
}

const styles = StyleSheet.create({
  tabBar: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: '#111',
    borderTopWidth: 1,
    borderTopColor: '#333',
    height: 70,
    paddingBottom: 5,
    elevation: 0,
  },
  tabLabel: {
    fontSize: 10,
    fontWeight: '600',
    marginTop: 4,
  },
  centralWrapper: {
    top: -20,
    justifyContent: 'center',
    alignItems: 'center',
  },
  centralButton: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: '#FFD700',
    justifyContent: 'center',
    alignItems: 'center',
    shadowColor: '#FFD700',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.6,
    shadowRadius: 8,
    elevation: 8,
  },
});