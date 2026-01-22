import React from 'react';
import { View, TouchableOpacity, StyleSheet, Text } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

interface BottomNavProps {
  activeTab: string;
  onTabPress: (tab: string) => void;
}

export const BottomNav: React.FC<BottomNavProps> = ({ activeTab, onTabPress }) => {
  return (
    <View style={styles.container}>
      {/* 1. Inicio */}
      <TouchableOpacity 
        style={styles.tab}
        onPress={() => onTabPress('home')}
      >
        <Ionicons 
          name={activeTab === 'home' ? 'home' : 'home-outline'} 
          size={24} 
          color={activeTab === 'home' ? '#FFD700' : '#888'} 
        />
        <Text style={[styles.label, activeTab === 'home' && styles.activeLabel]}>
          Inicio
        </Text>
      </TouchableOpacity>

      {/* 2. Evento */}
      <TouchableOpacity 
        style={styles.tab}
        onPress={() => onTabPress('event')}
      >
        <Ionicons 
          name={activeTab === 'event' ? 'calendar' : 'calendar-outline'} 
          size={24} 
          color={activeTab === 'event' ? '#FFD700' : '#888'} 
        />
        <Text style={[styles.label, activeTab === 'event' && styles.activeLabel]}>
          Evento
        </Text>
      </TouchableOpacity>

      {/* 3. INSCRIBIRSE - Central Destacado */}
      <TouchableOpacity 
        style={styles.centralTab}
        onPress={() => onTabPress('register')}
      >
        <View style={styles.centralIcon}>
          <Ionicons name="add-circle" size={40} color="#000" />
        </View>
        <Text style={styles.centralLabel}>Inscribirse</Text>
      </TouchableOpacity>

      {/* 4. Peleadores */}
      <TouchableOpacity 
        style={styles.tab}
        onPress={() => onTabPress('fighters')}
      >
        <Ionicons 
          name={activeTab === 'fighters' ? 'people' : 'people-outline'} 
          size={24} 
          color={activeTab === 'fighters' ? '#FFD700' : '#888'} 
        />
        <Text style={[styles.label, activeTab === 'fighters' && styles.activeLabel]}>
          Peleadores
        </Text>
      </TouchableOpacity>

      {/* 5. Mi Cuenta */}
      <TouchableOpacity 
        style={styles.tab}
        onPress={() => onTabPress('profile')}
      >
        <Ionicons 
          name={activeTab === 'profile' ? 'person' : 'person-outline'} 
          size={24} 
          color={activeTab === 'profile' ? '#FFD700' : '#888'} 
        />
        <Text style={[styles.label, activeTab === 'profile' && styles.activeLabel]}>
          Cuenta
        </Text>
      </TouchableOpacity>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    flexDirection: 'row',
    backgroundColor: '#111',
    borderTopWidth: 1,
    borderTopColor: '#333',
    height: 70,
    paddingBottom: 5,
    paddingHorizontal: 5,
    alignItems: 'center',
    justifyContent: 'space-around',
  },
  tab: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
  },
  label: {
    color: '#888',
    fontSize: 10,
    marginTop: 4,
    fontWeight: '600',
  },
  activeLabel: {
    color: '#FFD700',
  },
  centralTab: {
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: -25, // Levanta el botón central
  },
  centralIcon: {
    width: 60,
    height: 60,
    borderRadius: 30,
    backgroundColor: '#FFD700',
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: '#FFD700',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.6,
    shadowRadius: 8,
    elevation: 8,
  },
  centralLabel: {
    color: '#FFD700',
    fontSize: 10,
    marginTop: 4,
    fontWeight: 'bold',
  },
});