import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Platform } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, TYPOGRAPHY } from '../../constants/theme';

interface CustomBottomNavProps {
  navigation: any;
  state: any;
}

export const CustomBottomNav: React.FC<CustomBottomNavProps> = ({ navigation, state }) => {
  const routes = [
    { key: 'Home', name: 'Inicio', icon: 'home' },
    { key: 'Event', name: 'Evento', icon: 'calendar' },
    { key: 'Register', name: '', icon: 'calendar', isCenter: true },
    { key: 'Fighters', name: 'Peleadores', icon: 'people' },
    { key: 'Tickets', name: 'Entradas', icon: 'ticket' },
  ];

  const currentRoute = state.routes[state.index].name;

  const handlePress = (routeKey: string) => {
    navigation.navigate(routeKey);
  };

  return (
    <View style={styles.container}>
      {/* Barra principal */}
      <View style={styles.navbar}>
        {routes.map((route, index) => {
          const isFocused = currentRoute === route.key;

          if (route.isCenter) {
            // Botón central flotante
            return (
              <View key={route.key} style={styles.centerWrapper}>
                <TouchableOpacity
                  onPress={() => handlePress(route.key)}
                  activeOpacity={0.8}
                  style={styles.centerButtonContainer}
                >
                  <LinearGradient
                    colors={[COLORS.primary, '#FFA500']}
                    style={styles.centerButton}
                    start={{ x: 0, y: 0 }}
                    end={{ x: 1, y: 1 }}
                  >
                    <Ionicons
                      name="calendar"
                      size={36}
                      color={COLORS.text.inverse}
                    />
                  </LinearGradient>
                </TouchableOpacity>
              </View>
            );
          }

          // Botones normales
          return (
            <TouchableOpacity
              key={route.key}
              onPress={() => handlePress(route.key)}
              style={styles.tabButton}
              activeOpacity={0.7}
            >
              <Ionicons
                name={isFocused ? route.icon as any : `${route.icon}-outline` as any}
                size={26}
                color={isFocused ? COLORS.primary : COLORS.text.tertiary}
              />
              <Text
                style={[
                  styles.tabLabel,
                  isFocused && styles.tabLabelActive,
                ]}
              >
                {route.name}
              </Text>
            </TouchableOpacity>
          );
        })}
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    backgroundColor: COLORS.background,
  },
  navbar: {
    flexDirection: 'row',
    height: Platform.OS === 'ios' ? 85 : 70,
    paddingBottom: Platform.OS === 'ios' ? 20 : 10,
    paddingTop: 8,
    backgroundColor: COLORS.background,
    borderTopWidth: 1,
    borderTopColor: COLORS.border.primary,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: -3 },
    shadowOpacity: 0.3,
    shadowRadius: 6,
    elevation: 8,
  },
  tabButton: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingTop: 4,
  },
  tabLabel: {
    fontSize: 11,
    fontWeight: '600',
    color: COLORS.text.tertiary,
    marginTop: 4,
  },
  tabLabelActive: {
    color: COLORS.primary,
  },
  centerWrapper: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  centerButtonContainer: {
    position: 'absolute',
    top: -35,
    justifyContent: 'center',
    alignItems: 'center',
  },
  centerButton: {
    width: 70,
    height: 70,
    borderRadius: 35,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 5,
    borderColor: COLORS.background,
    shadowColor: COLORS.primary,
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.5,
    shadowRadius: 10,
    elevation: 12,
  },
});
