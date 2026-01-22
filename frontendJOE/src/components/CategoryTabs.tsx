import React, { useState } from 'react';
import { ScrollView, Text, TouchableOpacity, StyleSheet, View } from 'react-native';
import { TabItem } from '../types/boxeo'; // Importamos el tipo pro

// Definimos los datos iniciales siguiendo la interfaz
const INITIAL_TABS: TabItem[] = [
  { id: '1', label: 'Todos' },
  { id: '2', label: 'Peso Pesado' },
  { id: '3', label: 'Sector Tech' },
  { id: '4', label: 'Exhibición' },
  { id: '5', label: 'Amateur' },
];
      
export const CategoryTabs: React.FC = () => {
  // Tipamos el estado para que solo acepte strings (el ID del tab)
  const [activeId, setActiveId] = useState<string>('1');

  return (
    <View style={styles.container}>
      <ScrollView 
        horizontal 
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
        // Mejora el rendimiento del scroll en listas horizontales
        decelerationRate="fast"
      >
        {INITIAL_TABS.map((tab) => {
          const isActive = tab.id === activeId;
          return (
            <TouchableOpacity
              key={tab.id}
              onPress={() => setActiveId(tab.id)}
              activeOpacity={0.8}
              style={[
                styles.tab,
                isActive ? styles.tabActive : styles.tabInactive
              ]}
            >
              <Text style={[
                styles.tabText,
                isActive ? styles.tabTextActive : styles.tabTextInactive
              ]}>
                {tab.label}
              </Text>
            </TouchableOpacity>
          );
        })}
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    width: '100%',
    paddingVertical: 12, // Ajustado para que no ocupe demasiado espacio vertical
  },
  scrollContent: {
    paddingHorizontal: 20,
    // Nota: 'gap' solo funciona en versiones recientes de React Native. 
    // Si tu versión es antigua, usa marginHorizontal en 'tab'.
    gap: 10, 
  },
  tab: {
    paddingHorizontal: 22,
    paddingVertical: 10,
    borderRadius: 99,
    borderWidth: 1,
    justifyContent: 'center',
    alignItems: 'center',
    // Sutil transición visual
  },
  tabActive: {
    backgroundColor: '#FFFFFF',
    borderColor: '#FFFFFF',
    // Resplandor blanco para efecto "Glow"
    shadowColor: '#FFFFFF',
    shadowOffset: { width: 0, height: 0 },
    shadowOpacity: 0.4,
    shadowRadius: 8,
    elevation: 4,
    transform: [{ scale: 1.02 }],
  },
  tabInactive: {
    backgroundColor: '#1c1c1e', // Fondo oscuro tipo tarjeta
    borderColor: 'rgba(255, 255, 255, 0.1)',
  },
  tabText: {
    fontSize: 13,
    fontWeight: '700', // Un poco más grueso para legibilidad
  },
  tabTextActive: {
    color: '#000000',
  },
  tabTextInactive: {
    color: '#a1a1aa', // Un gris un poco más claro para mejor contraste
  },
});