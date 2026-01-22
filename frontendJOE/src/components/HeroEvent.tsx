import React from 'react';
import { View, Text, Image, StyleSheet, TouchableOpacity } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Fighter } from '../types/boxeo';

// 1. Definimos la forma de los datos que recibe el componente
interface HeroProps {
  data: {
    peleadores_destacados: Fighter[];
    evento?: {
      titulo: string;
    };
  };
}

export const HeroEvent: React.FC<HeroProps> = ({ data }) => {
  // Extraemos los dos primeros peleadores para el evento estelar
  const p1 = data.peleadores_destacados?.[0];
  const p2 = data.peleadores_destacados?.[1];

  // Si no hay peleadores, no renderizamos nada para evitar errores
  if (!p1 || !p2) return null;

  return (
    <View style={styles.container}>
      <View style={styles.sectionHeader}>
        <Text style={styles.title}>Evento Estelar</Text>
        <TouchableOpacity>
          <Text style={styles.more}>Ver todo</Text>
        </TouchableOpacity>
      </View>

      <TouchableOpacity activeOpacity={0.9} style={styles.card}>
        <View style={styles.fighterContainer}>
          
          {/* Lado Izquierdo - Peleador 1 */}
          <View style={styles.side}>
            <Image 
              source={{ uri: p1.foto_perfil || 'https://via.placeholder.com/150' }} 
              style={styles.img} 
            />
            <LinearGradient 
              colors={['transparent', 'rgba(0,0,0,0.9)']} 
              style={styles.grad} 
            />
            <View style={styles.infoLeft}>
              <View style={styles.badge}>
                <Text style={styles.badgeTxt}>VS</Text>
              </View>
              <Text style={styles.name} numberOfLines={1}>{p1.nombre}</Text>
              <Text style={styles.sub} numberOfLines={1}>{p1.club || 'Corporate'}</Text>
            </View>
          </View>

          {/* Marcador VS Flotante Central */}
          <View style={styles.vsCircle}>
            <Text style={styles.vsText}>VS</Text>
          </View>

          {/* Lado Derecho - Peleador 2 */}
          <View style={styles.side}>
            <Image 
              source={{ uri: p2.foto_perfil || 'https://via.placeholder.com/150' }} 
              style={styles.img} 
            />
            <LinearGradient 
              colors={['transparent', 'rgba(0,0,0,0.9)']} 
              style={styles.grad} 
            />
            <View style={styles.infoRight}>
              <Text style={styles.name} numberOfLines={1}>{p2.nombre}</Text>
              <Text style={styles.sub} numberOfLines={1}>{p2.club || 'Corporate'}</Text>
            </View>
          </View>

        </View>
      </TouchableOpacity>
    </View>
  );
};

const styles = StyleSheet.create({
  container: { 
    paddingHorizontal: 20, 
    paddingVertical: 10 
  },
  sectionHeader: { 
    flexDirection: 'row', 
    justifyContent: 'space-between', 
    alignItems: 'center', 
    marginBottom: 15 
  },
  title: { 
    color: 'white', 
    fontSize: 22, 
    fontWeight: 'bold',
    letterSpacing: -0.5
  },
  more: { 
    color: '#FFD700', // Dorado para resaltar el link
    fontSize: 14,
    fontWeight: '600'
  },
  card: { 
    height: 240, 
    borderRadius: 25, 
    overflow: 'hidden', 
    backgroundColor: '#111', 
    borderWidth: 1, 
    borderColor: '#222',
    elevation: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 10,
  },
  fighterContainer: { 
    flex: 1, 
    flexDirection: 'row' 
  },
  side: { 
    flex: 1,
    position: 'relative'
  },
  img: { 
    width: '100%', 
    height: '100%', 
    resizeMode: 'cover' 
  },
  grad: { 
    ...StyleSheet.absoluteFillObject 
  },
  vsCircle: { 
    position: 'absolute', 
    top: '40%', 
    left: '42%', // Ajustado para centrar mejor
    width: 50, 
    height: 50, 
    zIndex: 10, 
    justifyContent: 'center', 
    alignItems: 'center' 
  },
  vsText: { 
    color: 'rgba(255,255,255,0.15)', 
    fontSize: 48, 
    fontWeight: '900', 
    fontStyle: 'italic' 
  },
  infoLeft: { 
    position: 'absolute', 
    bottom: 18, 
    left: 15,
    right: 5
  },
  infoRight: { 
    position: 'absolute', 
    bottom: 18, 
    right: 15, 
    left: 5,
    alignItems: 'flex-end' 
  },
  badge: { 
    backgroundColor: '#FFD700', 
    paddingHorizontal: 6, 
    borderRadius: 4, 
    marginBottom: 4,
    alignSelf: 'flex-start'
  },
  badgeTxt: { 
    fontSize: 10, 
    fontWeight: '900',
    color: 'black'
  },
  name: { 
    color: 'white', 
    fontSize: 17, 
    fontWeight: 'bold',
    textShadowColor: 'rgba(0, 0, 0, 0.75)',
    textShadowOffset: { width: -1, height: 1 },
    textShadowRadius: 10
  },
  sub: { 
    color: '#a1a1aa', 
    fontSize: 11,
    fontWeight: '500'
  }
});