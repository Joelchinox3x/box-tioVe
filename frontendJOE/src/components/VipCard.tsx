import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Image } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ticket } from 'lucide-react-native';

// Definimos el componente como Functional Component (FC)
export const VipCard: React.FC = () => {
  return (
    <View style={styles.section}>
      {/* Contenedor principal con efecto de resplandor dorado */}
      <View style={styles.cardContainer}>
        
        {/* Imagen de fondo (Estadio) */}
        <Image 
          source={{ uri: 'https://picsum.photos/seed/stadium_lights/600/400' }} 
          style={styles.backgroundImage}
          resizeMode="cover"
        />

        {/* Gradiente doble: uno para oscurecer y otro para dar el tono dorado profundo */}
        <LinearGradient 
          colors={['rgba(61, 43, 0, 0.4)', 'rgba(0,0,0,0.85)', '#000000']} 
          style={styles.overlayGradient}
        />

        {/* Contenido Central */}
        <View style={styles.content}>
          {/* Icono del Ticket con caja estilizada */}
          <View style={styles.ticketIconBox}>
            <Ticket size={24} color="#FFD700" strokeWidth={2.5} />
          </View>
          
          <Text style={styles.title}>Entradas VIP</Text>
          
          <Text style={styles.description}>
            Acceso exclusivo al ringside, catering premium y afterparty con los peleadores.
          </Text>
          
          <TouchableOpacity 
            activeOpacity={0.8} 
            style={styles.button}
            onPress={() => console.log('Compra VIP iniciada')}
          >
            <Text style={styles.buttonText}>Comprar Ahora</Text>
          </TouchableOpacity>
        </View>

      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  section: { 
    marginTop: 10, 
    paddingBottom: 40, 
    paddingHorizontal: 20 
  },
  cardContainer: {
    width: '100%',
    borderRadius: 28, // Más redondeado para un look moderno
    overflow: 'hidden',
    backgroundColor: '#1a1400', 
    borderWidth: 1.5,
    borderColor: 'rgba(255, 215, 0, 0.25)',
    // Sombras para iOS
    shadowColor: '#FFD700',
    shadowOffset: { width: 0, height: 10 },
    shadowOpacity: 0.15,
    shadowRadius: 20,
    // Elevación para Android
    elevation: 8,
  },
  backgroundImage: {
    ...StyleSheet.absoluteFillObject,
    opacity: 0.25, // Opacidad baja para que el texto sea legible
  },
  overlayGradient: {
    ...StyleSheet.absoluteFillObject,
  },
  content: {
    paddingVertical: 35,
    paddingHorizontal: 24,
    alignItems: 'center',
    zIndex: 10,
  },
  ticketIconBox: {
    width: 52,
    height: 52,
    backgroundColor: 'rgba(0,0,0,0.8)',
    borderRadius: 15,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 16,
    borderWidth: 1,
    borderColor: 'rgba(255, 215, 0, 0.4)',
    // Brillo del icono
    shadowColor: '#FFD700',
    shadowOffset: { width: 0, height: 0 },
    shadowOpacity: 0.5,
    shadowRadius: 10,
    elevation: 5,
  },
  title: {
    fontSize: 26,
    fontWeight: '900',
    color: '#FFD700',
    marginBottom: 8,
    letterSpacing: 0.5,
    textTransform: 'uppercase',
  },
  description: {
    fontSize: 14,
    color: '#a1a1aa',
    textAlign: 'center',
    maxWidth: 240,
    lineHeight: 20,
    marginBottom: 26,
    fontWeight: '500',
  },
  button: {
    backgroundColor: '#FFD700', // Botón dorado para máxima acción
    paddingVertical: 14,
    paddingHorizontal: 36,
    borderRadius: 14, // Forma cuadrada-redondeada moderna
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 5,
  },
  buttonText: {
    color: 'black',
    fontSize: 15,
    fontWeight: '800',
    textTransform: 'uppercase',
  },
});