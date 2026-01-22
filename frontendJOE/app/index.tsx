import React, { useEffect, useState } from 'react';
import { 
  SafeAreaView, 
  ScrollView, 
  StyleSheet, 
  View, 
  Text, 
  StatusBar, 
  ActivityIndicator 
} from 'react-native';
import boxApi from '../src/api/boxApi';


// Importación de tipos
import { Fighter, FightMatch } from '../src/types/boxeo';
 
// Importación de componentes profesionales
import { Header } from '../src/components/Header';
import { CategoryTabs } from '../src/components/CategoryTabs';
import { HeroEvent } from '../src/components/HeroEvent';
import { RecentFighters } from '../src/components/RecentFighters'; // <-- Agregado
import { ScheduledFights } from '../src/components/ScheduledFights';
import { VipCard } from '../src/components/VipCard';
import { BottomNav } from '../src/components/BottomNav';

// Definimos la estructura de la respuesta para que TypeScript no se queje
interface AppData {
  evento: { titulo: string };
  peleadores_destacados: Fighter[];
  peleas_pactadas: FightMatch[];
}

export default function App() {
  const [data, setData] = useState<AppData | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    boxApi.get('/eventos')
      .then(res => {
        setData(res.data);
        setLoading(false);
      })
      .catch(err => {
        console.error("Error cargando API:", err);
        setLoading(false);
      });
  }, []);

  // Pantalla de carga profesional
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
      {/* Forzamos que la barra de estado del celular sea clara sobre el fondo negro */}
      <StatusBar barStyle="light-content" backgroundColor="#000" />
      
      {/* 1. Header Fijo Arriba */}
      <Header title={data?.evento?.titulo} />
      
      {/* 2. Contenido Deslizable */}
      <ScrollView 
        showsVerticalScrollIndicator={false} 
        contentContainerStyle={styles.scrollBody}
      >
        <CategoryTabs />
        
        {/* Pasamos la data a cada sección */}
        {data && (
          <>
            <HeroEvent data={data} />
            <RecentFighters data={data} /> 
            <ScheduledFights data={data} />
          </>
        )}
        
        <VipCard />
      </ScrollView>

      {/* 3. Navegación Fija Abajo */}
      <BottomNav />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#000',
  },
  scrollBody: {
    // 120 es el espacio justo para que el BottomNav flotante no tape el botón de la VipCard
    paddingBottom: 130, 
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
    letterSpacing: 2
  }
});