import React from 'react';
import { View, Text, StyleSheet, Image, TouchableOpacity, ScrollView } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Fighter } from '../types/boxeo'; // Importamos tu interfaz

interface RecentProps {
  data: {
    peleadores_destacados: Fighter[];
  };
}

export const RecentFighters: React.FC<RecentProps> = ({ data }) => {
  // Usamos los peleadores que vienen de tu base de datos XAMPP
  const fighters = data?.peleadores_destacados || [];

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.title}>Últimos Inscritos</Text>
        <Text style={styles.subtitle}>Luchadores ejecutivos destacados de esta temporada</Text>
      </View>

      <ScrollView 
        horizontal 
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
      >
        {fighters.map((fighter) => (
          <TouchableOpacity 
            key={fighter.id} 
            activeOpacity={0.8} 
            style={styles.fighterWrapper}
          >
            <View style={styles.circle}>
              <Image 
                source={{ uri: fighter.foto_url }} 
                style={styles.img} 
              />
              {/* El degradado sobre la imagen para dar profundidad */}
              <LinearGradient
                colors={['transparent', 'rgba(0,0,0,0.5)']}
                style={styles.gradient}
              />
            </View>
            <Text style={styles.nameLabel} numberOfLines={1}>
              {fighter.apodo || fighter.nombre.split(' ')[0]}
            </Text>
          </TouchableOpacity>
        ))}
      </ScrollView>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    marginVertical: 20,
  },
  header: {
    paddingHorizontal: 20,
    marginBottom: 12,
  },
  title: {
    color: 'white',
    fontSize: 20,
    fontWeight: 'bold',
  },
  subtitle: {
    color: '#636366',
    fontSize: 12,
    marginTop: 2,
  },
  scrollContent: {
    paddingHorizontal: 15, // Un poco menos para que el primer círculo esté cerca del borde
  },
  fighterWrapper: {
    alignItems: 'center',
    marginHorizontal: 8,
    width: 75,
  },
  circle: {
    width: 70,
    height: 70,
    borderRadius: 35,
    backgroundColor: '#1c1c1e',
    overflow: 'hidden',
    borderWidth: 2,
    borderColor: 'rgba(255, 255, 255, 0.1)',
    position: 'relative',
  },
  img: {
    width: '100%',
    height: '100%',
    resizeMode: 'cover',
  },
  gradient: {
    ...StyleSheet.absoluteFillObject,
  },
  nameLabel: {
    color: '#8e8e93',
    fontSize: 10,
    marginTop: 6,
    fontWeight: '600',
    textAlign: 'center',
  },
});