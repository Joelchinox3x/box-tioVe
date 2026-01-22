import React, { useState, useEffect } from 'react';
import { 
  SafeAreaView, 
  ScrollView, 
  StyleSheet, 
  View, 
  Text,
  TouchableOpacity,
  Image,
  Alert 
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

interface UserProfile {
  id: number;
  nombre: string;
  email: string;
  tipo_usuario: 'espectador' | 'peleador' | 'admin';
  foto?: string;
}

interface FighterProfile extends UserProfile {
  apodo: string;
  club: string;
  record: string;
  promociones: number;
  victorias: number;
  derrotas: number;
  empates: number;
}

export default function ProfileScreen({ navigation }: any) {
  const [isLoggedIn, setIsLoggedIn] = useState(false);
  const [userProfile, setUserProfile] = useState<UserProfile | FighterProfile | null>(null);

  useEffect(() => {
    checkLoginStatus();
  }, []);

  const checkLoginStatus = async () => {
    // Verificar si hay sesión activa
    // const token = await AsyncStorage.getItem('userToken');
    // if (token) {
    //   loadUserProfile();
    // }
    
    // DEMO: Usuario peleador
    setIsLoggedIn(true);
    setUserProfile({
      id: 1,
      nombre: 'Carlos Mendoza',
      email: 'carlos@example.com',
      tipo_usuario: 'peleador',
      apodo: 'El Rayo',
      club: 'Boxeo Los Campeones',
      record: '15-3-2',
      promociones: 42,
      victorias: 15,
      derrotas: 3,
      empates: 2,
      foto: 'https://via.placeholder.com/150'
    } as FighterProfile);
  };

  const handleLogout = () => {
    Alert.alert(
      'Cerrar Sesión',
      '¿Estás seguro que deseas salir?',
      [
        { text: 'Cancelar', style: 'cancel' },
        { 
          text: 'Salir', 
          onPress: () => {
            setIsLoggedIn(false);
            setUserProfile(null);
          },
          style: 'destructive'
        }
      ]
    );
  };

  // Pantalla de Login/Registro
  if (!isLoggedIn) {
    return (
      <SafeAreaView style={styles.container}>
        <View style={styles.loginContainer}>
          <Ionicons name="person-circle" size={100} color="#FFD700" />
          <Text style={styles.loginTitle}>Bienvenido a BoxEvent</Text>
          <Text style={styles.loginSubtitle}>
            Inicia sesión para acceder a tu perfil
          </Text>

          <TouchableOpacity 
            style={styles.loginButton}
            onPress={() => {/* Navegar a Login */}}
          >
            <Text style={styles.loginButtonText}>INICIAR SESIÓN</Text>
          </TouchableOpacity>

          <TouchableOpacity 
            style={styles.registerButton}
            onPress={() => {/* Navegar a Registro */}}
          >
            <Text style={styles.registerButtonText}>CREAR CUENTA</Text>
          </TouchableOpacity>

          <TouchableOpacity 
            style={styles.guestButton}
            onPress={() => navigation.navigate('Home')}
          >
            <Text style={styles.guestButtonText}>Continuar como invitado</Text>
          </TouchableOpacity>
        </View>
      </SafeAreaView>
    );
  }

  // Perfil de Peleador
  if (userProfile?.tipo_usuario === 'peleador') {
    const fighter = userProfile as FighterProfile;
    return (
      <SafeAreaView style={styles.container}>
        <ScrollView showsVerticalScrollIndicator={false}>
          {/* Header del perfil */}
          <View style={styles.profileHeader}>
            <Image 
              source={{ uri: fighter.foto }} 
              style={styles.profilePhoto}
            />
            <Text style={styles.profileName}>{fighter.nombre}</Text>
            <Text style={styles.profileNickname}>"{fighter.apodo}"</Text>
            <Text style={styles.profileClub}>{fighter.club}</Text>
          </View>

          {/* Stats del peleador */}
          <View style={styles.statsContainer}>
            <StatCard 
              icon="trophy" 
              label="Victorias" 
              value={fighter.victorias.toString()} 
              color="#4CAF50"
            />
            <StatCard 
              icon="close-circle" 
              label="Derrotas" 
              value={fighter.derrotas.toString()} 
              color="#FF3B30"
            />
            <StatCard 
              icon="remove-circle" 
              label="Empates" 
              value={fighter.empates.toString()} 
              color="#888"
            />
            <StatCard 
              icon="flame" 
              label="Promociones" 
              value={fighter.promociones.toString()} 
              color="#FFD700"
            />
          </View>

          {/* Récord */}
          <View style={styles.recordCard}>
            <Text style={styles.recordLabel}>Récord Oficial</Text>
            <Text style={styles.recordValue}>{fighter.record}</Text>
            <Text style={styles.recordSubtext}>
              Victorias - Derrotas - Empates
            </Text>
          </View>

          {/* Opciones del menú */}
          <View style={styles.menuSection}>
            <MenuItem 
              icon="person" 
              label="Editar Perfil" 
              onPress={() => {/* Editar perfil */}}
            />
            <MenuItem 
              icon="calendar" 
              label="Mis Peleas" 
              onPress={() => {/* Ver peleas */}}
            />
            <MenuItem 
              icon="share-social" 
              label="Compartir Perfil" 
              onPress={() => {/* Compartir */}}
            />
            <MenuItem 
              icon="stats-chart" 
              label="Estadísticas" 
              onPress={() => {/* Ver stats */}}
            />
            <MenuItem 
              icon="settings" 
              label="Configuración" 
              onPress={() => {/* Configuración */}}
            />
          </View>

          {/* Botón de cerrar sesión */}
          <TouchableOpacity 
            style={styles.logoutButton}
            onPress={handleLogout}
          >
            <Ionicons name="log-out" size={20} color="#FF3B30" />
            <Text style={styles.logoutText}>Cerrar Sesión</Text>
          </TouchableOpacity>
        </ScrollView>
      </SafeAreaView>
    );
  }

  // Perfil de Espectador
  return (
    <SafeAreaView style={styles.container}>
      <ScrollView showsVerticalScrollIndicator={false}>
        <View style={styles.profileHeader}>
          <Image 
            source={{ uri: userProfile?.foto || 'https://via.placeholder.com/150' }} 
            style={styles.profilePhoto}
          />
          <Text style={styles.profileName}>{userProfile?.nombre}</Text>
          <Text style={styles.profileEmail}>{userProfile?.email}</Text>
        </View>

        {/* Opciones del menú */}
        <View style={styles.menuSection}>
          <MenuItem 
            icon="ticket" 
            label="Mis Entradas" 
            onPress={() => {/* Ver entradas */}}
            badge="2"
          />
          <MenuItem 
            icon="heart" 
            label="Peleadores Favoritos" 
            onPress={() => {/* Ver favoritos */}}
          />
          <MenuItem 
            icon="notifications" 
            label="Notificaciones" 
            onPress={() => {/* Configurar notifs */}}
          />
          <MenuItem 
            icon="settings" 
            label="Configuración" 
            onPress={() => {/* Configuración */}}
          />
        </View>

        <TouchableOpacity 
          style={styles.logoutButton}
          onPress={handleLogout}
        >
          <Ionicons name="log-out" size={20} color="#FF3B30" />
          <Text style={styles.logoutText}>Cerrar Sesión</Text>
        </TouchableOpacity>
      </ScrollView>
    </SafeAreaView>
  );
}

// Componentes auxiliares
const StatCard = ({ 
  icon, 
  label, 
  value, 
  color 
}: { 
  icon: any; 
  label: string; 
  value: string; 
  color: string;
}) => (
  <View style={styles.statCard}>
    <Ionicons name={icon} size={32} color={color} />
    <Text style={styles.statValue}>{value}</Text>
    <Text style={styles.statLabel}>{label}</Text>
  </View>
);

const MenuItem = ({ 
  icon, 
  label, 
  onPress, 
  badge 
}: { 
  icon: any; 
  label: string; 
  onPress: () => void;
  badge?: string;
}) => (
  <TouchableOpacity style={styles.menuItem} onPress={onPress}>
    <View style={styles.menuItemLeft}>
      <Ionicons name={icon} size={24} color="#FFD700" />
      <Text style={styles.menuItemText}>{label}</Text>
    </View>
    <View style={styles.menuItemRight}>
      {badge && (
        <View style={styles.badge}>
          <Text style={styles.badgeText}>{badge}</Text>
        </View>
      )}
      <Ionicons name="chevron-forward" size={20} color="#666" />
    </View>
  </TouchableOpacity>
);

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#000',
  },
  loginContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 30,
  },
  loginTitle: {
    color: '#FFD700',
    fontSize: 24,
    fontWeight: 'bold',
    marginTop: 20,
    textAlign: 'center',
  },
  loginSubtitle: {
    color: '#888',
    fontSize: 14,
    marginTop: 10,
    marginBottom: 40,
    textAlign: 'center',
  },
  loginButton: {
    width: '100%',
    backgroundColor: '#FFD700',
    paddingVertical: 15,
    borderRadius: 10,
    alignItems: 'center',
    marginBottom: 15,
  },
  loginButtonText: {
    color: '#000',
    fontSize: 16,
    fontWeight: 'bold',
  },
  registerButton: {
    width: '100%',
    backgroundColor: '#1a1a1a',
    paddingVertical: 15,
    borderRadius: 10,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#FFD700',
    marginBottom: 15,
  },
  registerButtonText: {
    color: '#FFD700',
    fontSize: 16,
    fontWeight: 'bold',
  },
  guestButton: {
    marginTop: 20,
  },
  guestButtonText: {
    color: '#666',
    fontSize: 14,
    textDecorationLine: 'underline',
  },
  profileHeader: {
    alignItems: 'center',
    padding: 30,
    backgroundColor: '#1a1a1a',
    borderBottomWidth: 1,
    borderBottomColor: '#333',
  },
  profilePhoto: {
    width: 100,
    height: 100,
    borderRadius: 50,
    borderWidth: 3,
    borderColor: '#FFD700',
    marginBottom: 15,
  },
  profileName: {
    color: '#fff',
    fontSize: 22,
    fontWeight: 'bold',
  },
  profileNickname: {
    color: '#FFD700',
    fontSize: 18,
    fontStyle: 'italic',
    marginTop: 4,
  },
  profileClub: {
    color: '#888',
    fontSize: 14,
    marginTop: 8,
  },
  profileEmail: {
    color: '#888',
    fontSize: 14,
    marginTop: 4,
  },
  statsContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    padding: 15,
    gap: 10,
  },
  statCard: {
    flex: 1,
    minWidth: '45%',
    backgroundColor: '#1a1a1a',
    padding: 15,
    borderRadius: 10,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#333',
  },
  statValue: {
    color: '#fff',
    fontSize: 28,
    fontWeight: 'bold',
    marginVertical: 5,
  },
  statLabel: {
    color: '#888',
    fontSize: 12,
  },
  recordCard: {
    backgroundColor: '#1a1a1a',
    marginHorizontal: 15,
    marginBottom: 20,
    padding: 20,
    borderRadius: 10,
    alignItems: 'center',
    borderWidth: 2,
    borderColor: '#FFD700',
  },
  recordLabel: {
    color: '#888',
    fontSize: 12,
    marginBottom: 8,
  },
  recordValue: {
    color: '#FFD700',
    fontSize: 36,
    fontWeight: 'bold',
  },
  recordSubtext: {
    color: '#666',
    fontSize: 11,
    marginTop: 4,
  },
  menuSection: {
    padding: 15,
  },
  menuItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    backgroundColor: '#1a1a1a',
    padding: 15,
    borderRadius: 10,
    marginBottom: 10,
    borderWidth: 1,
    borderColor: '#333',
  },
  menuItemLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 15,
  },
  menuItemText: {
    color: '#fff',
    fontSize: 16,
  },
  menuItemRight: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  badge: {
    backgroundColor: '#FF3B30',
    borderRadius: 10,
    paddingHorizontal: 8,
    paddingVertical: 2,
  },
  badgeText: {
    color: '#fff',
    fontSize: 12,
    fontWeight: 'bold',
  },
  logoutButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginHorizontal: 15,
    marginVertical: 20,
    paddingVertical: 15,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: '#FF3B30',
    gap: 10,
  },
  logoutText: {
    color: '#FF3B30',
    fontSize: 16,
    fontWeight: '600',
  },
});