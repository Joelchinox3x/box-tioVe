import React from 'react';
import { View, Text, StyleSheet, Image, TouchableOpacity, Platform, StatusBar } from 'react-native';
import { Bell, Video } from 'lucide-react-native';

// Definimos la interfaz para las propiedades del componente
interface HeaderProps {
  title?: string;
}

// Usamos React.FC (Functional Component) con nuestra interfaz HeaderProps
export const Header: React.FC<HeaderProps> = ({ title }) => {
  return (
    <View style={styles.headerContainer}>
      {/* Event Info (Lado Izquierdo) */}
      <View style={styles.eventInfo}>
        <View style={styles.videoIconBox}>
          {/* fill="black" para que el icono se vea sólido como en el diseño pro */}
          <Video size={26} color="black" fill="black" />
        </View>
        <View style={styles.textColumn}>
          <Text style={styles.liveLabel}>LIVE EVENT</Text>
          <Text style={styles.titleText} numberOfLines={2}>
            {title || "Noche Corporativa"}
          </Text>
        </View>
      </View>

      {/* User Actions (Lado Derecho) */}
      <View style={styles.actions}>
        <TouchableOpacity style={styles.notifButton} activeOpacity={0.7}>
          <Bell size={24} color="#8e8e93" />
          <View style={styles.redDot} />
        </TouchableOpacity>
        
        <TouchableOpacity style={styles.avatarWrapper} activeOpacity={0.8}>
          <Image 
            source={{ uri: 'https://picsum.photos/seed/user_avatar/100/100' }} 
            style={styles.avatarImg}
          />
        </TouchableOpacity>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  headerContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    // Cálculo pro para el padding superior según el dispositivo
    paddingTop: Platform.OS === 'ios' ? 50 : (StatusBar.currentHeight ? StatusBar.currentHeight + 10 : 40),
    paddingBottom: 16,
    backgroundColor: 'rgba(0, 0, 0, 0.95)', 
    borderBottomWidth: 1,
    borderBottomColor: 'rgba(255, 255, 255, 0.08)',
    zIndex: 40,
  },
  eventInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  videoIconBox: {
    width: 34,
    height: 34,
    backgroundColor: '#FFD700',
    borderRadius: 8,
    alignItems: 'center',
    justifyContent: 'center',
    // Sombra pro para que el cuadro brille un poco
    shadowColor: '#FFD700',
    shadowOffset: { width: 0, height: 0 },
    shadowOpacity: 0.4,
    shadowRadius: 8,
    elevation: 6,
  },
  textColumn: {
    flexDirection: 'column',
    maxWidth: '60%', // Evita que el título empuje los iconos de la derecha
  },
  liveLabel: {
    fontSize: 9,
    fontWeight: '800',
    color: '#8e8e93',
    letterSpacing: 1.2,
    textTransform: 'uppercase',
    marginBottom: -2,
  },
  titleText: {
    fontSize: 19,
    fontWeight: 'bold',
    color: 'white',
    letterSpacing: -0.5,
  },
  actions: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 18,
  },
  notifButton: {
    position: 'relative',
  },
  redDot: {
    position: 'absolute',
    top: -2,
    right: -2,
    width: 9,
    height: 9,
    backgroundColor: '#ff3b30', 
    borderRadius: 4.5,
    borderWidth: 2,
    borderColor: '#000',
  },
  avatarWrapper: {
    width: 38,
    height: 38,
    borderRadius: 19,
    backgroundColor: '#2c2c2e',
    overflow: 'hidden',
    borderWidth: 1.5,
    borderColor: '#3a3a3c',
  },
  avatarImg: {
    width: '100%',
    height: '100%',
    opacity: 0.9,
  },
});