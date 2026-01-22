import React, { useEffect, useState } from 'react';
import { 
  SafeAreaView, 
  ScrollView, 
  StyleSheet, 
  View, 
  Text, 
  StatusBar, 
  ActivityIndicator,
  TouchableOpacity 
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import boxApi from '../api/boxApi';

// Componentes
import { Header } from '../components/Header';
import { CategoryTabs } from '../components/CategoryTabs';
import { HeroEvent } from '../components/HeroEvent';
import { RecentFighters } from '../components/RecentFighters';
import { ScheduledFights } from '../components/ScheduledFights';
import { VipCard } from '../components/VipCard';
import { CountdownTimer } from '../components/CountdownTimer';

// Tipos
interface AppData {
  evento: { 
    titulo: string;
    fecha_evento: string;
    lugar: string;
  };
  peleadores_destacados: any[];
  peleas_pactadas: any[];
}

export default function HomeScreen({ navigation }: any) {
  const [data, setData] = useState<AppData | null>(null);
  const [loading, setLoading] = useState(true);
  const [isAdmin, setIsAdmin] = useState(false);

  useEffect(() => {
    loadData();
    checkAdminStatus();
  }, []);

  const loadData = async () => {
    try {
      const res = await boxApi.get('/eventos');
      setData(res.data);
      setLoading(false);
    } catch (err) {
      console.error("Error cargando API:", err);
      setLoading(false);
    }
  };

  const checkAdminStatus = async () => {
    // Verificar si el usuario es admin
    // Aquí implementarías la lógica de verificación
    const adminEmail = 'admin@boxevent.com'; // Ejemplo
    // setIsAdmin(true); // Descomentar si es admin
  };

  if (loading) {
    return (
      <View style={styles.loading}>
        <ActivityIndicator size="large" color="#FFD700" />
        <Text style={styles.loadingText}>CARGANDO NOCHE CORPORATIVA...</Text>
      </View>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor="#000" />
      
      {/* Header con botón Admin */}
      <View style={styles.headerWrapper}>
        <Header title={data?.evento?.titulo} />
        
        {/* Botón Admin (solo visible si isAdmin) */}
        {isAdmin && (
          <TouchableOpacity 
            style={styles.adminButton}
            onPress={() => navigation.navigate('Admin')}
          >
            <Ionicons name="settings" size={24} color="#FFD700" />
          </TouchableOpacity>
        )}
      </View>
      
      <ScrollView 
        showsVerticalScrollIndicator={false} 
        contentContainerStyle={styles.scrollBody}
      >
        {/* Countdown Timer */}
        {data?.evento?.fecha_evento && (
          <CountdownTimer eventDate={data.evento.fecha_evento} />
        )}

        {/* Botón Destacado: Comprar Entradas */}
        <TouchableOpacity 
          style={styles.buyTicketsButton}
          onPress={() => navigation.navigate('Register')}
        >
          <Ionicons name="ticket" size={24} color="#000" />
          <Text style={styles.buyTicketsText}>🎟️ ADQUIRIR ENTRADAS</Text>
        </TouchableOpacity>

        <CategoryTabs />
        
        {data && (
          <>
            <HeroEvent data={data} />
            <RecentFighters data={data} navigation={navigation} />
            <ScheduledFights data={data} />
          </>
        )}
        
        <VipCard />

        {/* Botón: Reglas del Torneo */}
        <TouchableOpacity 
          style={styles.rulesButton}
          onPress={() => {/* Abrir modal de reglas */}}
        >
          <Ionicons name="book" size={20} color="#FFD700" />
          <Text style={styles.rulesText}>Reglas del Torneo</Text>
        </TouchableOpacity>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#000',
  },
  headerWrapper: {
    position: 'relative',
  },
  adminButton: {
    position: 'absolute',
    top: 15,
    right: 15,
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: '#1a1a1a',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#333',
  },
  scrollBody: {
    paddingBottom: 100,
  },
  loading: {
    flex: 1,
    backgroundColor: '#000',
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    color: '#FFD700',
    fontWeight: 'bold',
    marginTop: 15,
    fontSize: 12,
    letterSpacing: 2,
  },
  buyTicketsButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#FFD700',
    marginHorizontal: 15,
    marginVertical: 15,
    paddingVertical: 18,
    borderRadius: 12,
    gap: 10,
    shadowColor: '#FFD700',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.4,
    shadowRadius: 8,
    elevation: 6,
  },
  buyTicketsText: {
    color: '#000',
    fontSize: 16,
    fontWeight: 'bold',
    letterSpacing: 1,
  },
  rulesButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginHorizontal: 15,
    marginVertical: 20,
    paddingVertical: 12,
    borderWidth: 1,
    borderColor: '#FFD700',
    borderRadius: 8,
    gap: 8,
  },
  rulesText: {
    color: '#FFD700',
    fontSize: 14,
    fontWeight: '600',
  },
});