import React, { useEffect, useState } from 'react';
import {
  View,
  StyleSheet,
  ScrollView,
  ActivityIndicator,
  SafeAreaView,
  StatusBar,
  TouchableOpacity,
  Text,
} from 'react-native';
import { COLORS, SPACING } from '../constants/theme';
import api from '../services/api';
import type { EventData } from '../types';
import {
  Header,
  CategoryTabs,
  FightCard,
  FighterCarousel,
  ScheduledFights,
  GeneralTicketBanner,
  BannerCarousel,
  type Category,
} from '../components/home';
import { Ionicons } from '@expo/vector-icons';

interface HomeScreenProps {
  navigation: any;
}

export default function HomeScreen({ navigation }: HomeScreenProps) {
  const [loading, setLoading] = useState(true);
  const [eventData, setEventData] = useState<EventData | null>(null);
  const [selectedCategory, setSelectedCategory] = useState('todos');

  // Categorías disponibles
  const categories: Category[] = [
    { id: 'todos', label: 'Todos' },
    { id: 'peso_pesado', label: 'Peso Pesado' },
    { id: 'sector_tech', label: 'Sector Tech' },
  ];

  useEffect(() => {
    loadEventData();
  }, []);

  const loadEventData = async () => {
    try {
      const response = await api.get('/eventos');
      setEventData(response.data);
    } catch (error: any) {
      console.error('❌ Error al cargar eventos:', error);

      if (error.code === 'ECONNABORTED') {
        console.warn('⏱️ Timeout: El servidor tardó demasiado en responder');
      } else if (error.response) {
        console.error('📛 Error del servidor:', error.response.status, error.response.data);
      } else if (error.request) {
        console.error('🌐 Error de red: No se pudo conectar con el servidor');
      }

      setEventData({
        evento: null,
        peleadores_destacados: [],
        peleas_pactadas: [],
      });
    } finally {
      setLoading(false);
    }
  };

  const handleNotificationPress = () => {
    console.log('Notificaciones presionadas');
    // TODO: Implementar navegación a notificaciones
  };

  const handleProfilePress = () => {
    navigation.navigate('Profile');
  };

  const handleFightCardPress = () => {
    console.log('Fight card presionada');
    // TODO: Implementar navegación a detalles de pelea
  };

  const handleFighterPress = (fighter: any) => {
    console.log('Peleador presionado:', fighter);
    // TODO: Implementar navegación a perfil de peleador
  };

  const handleTicketPress = () => {
    // Navegar a compra de boletos con el ID del evento actual
    const eventoId = eventData?.evento?.id || 1;
    navigation.navigate('BuyTickets', { eventoId });
  };

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color={COLORS.primary} />
      </View>
    );
  }

  // Filtrar peleas destacadas (primeras 3 peleas pactadas o crear ejemplos)
  const featuredFights = eventData?.peleas_pactadas?.slice(0, 3) || [];

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor={COLORS.background} />

      {/* Header Premium Box TioVE */}
      <Header
        eventTitle="EL JAB DORADO"
        isLive={true}
        onNotificationPress={handleNotificationPress}
        onProfilePress={handleProfilePress}
      />

      {/* Tabs de categorías 
      <CategoryTabs
        categories={categories}
        selectedCategory={selectedCategory}
        onSelectCategory={setSelectedCategory}
      /> */}

      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
      >
        {/* Sección: Evento Estelar */}
        {featuredFights.length > 0 && (
          <View style={styles.section}>
            <View style={styles.sectionHeader}>
              <Text style={styles.sectionTitle}>Evento Estelar</Text>
              <TouchableOpacity
                onPress={() => navigation.navigate('Fighters')}
                style={styles.seeAllButton}
              >
                <Text style={styles.seeAllText}>Ver todo</Text>
                <Ionicons name="arrow-forward" size={16} color={COLORS.primary} />
              </TouchableOpacity>
            </View>

            <ScrollView
              horizontal
              showsHorizontalScrollIndicator={false}
              contentContainerStyle={styles.carouselContent}
            >
              {featuredFights.map((fight: any, index: number) => (
                <FightCard
                  key={fight.id || index}
                  fighter1={{
                    nombre: fight.peleador1?.nombre,
                    apodo: fight.peleador1?.apodo,
                    empresa: fight.peleador1?.empresa,
                    club_nombre: fight.peleador1?.club_nombre,
                    foto_perfil: fight.peleador1?.foto_perfil,
                  }}
                  fighter2={{
                    nombre: fight.peleador2?.nombre,
                    apodo: fight.peleador2?.apodo,
                    empresa: fight.peleador2?.empresa,
                    club_nombre: fight.peleador2?.club_nombre,
                    foto_perfil: fight.peleador2?.foto_perfil,
                  }}
                  featured={index === 0}
                  onPress={handleFightCardPress}
                />
              ))}
            </ScrollView>
          </View>
        )}

        {/* Sección: Últimos Inscritos */}
        {eventData?.peleadores_destacados && eventData.peleadores_destacados.length > 0 && (
          <FighterCarousel
            fighters={eventData.peleadores_destacados}
            title="Últimos Inscritos"
            subtitle="Luchadores ejecutivos destacados de esta temporada"
            onFighterPress={handleFighterPress}
          />
        )}

        {/* Sección: Peleas Pactadas */}
        {eventData?.peleas_pactadas && (
          <ScheduledFights
            fights={eventData.peleas_pactadas}
            title="Peleas Pactadas"
            subtitle="Próximos combates confirmados"
            onFightPress={handleFightCardPress}
          />
        )}

        {/* Slider Dinámico de Banners */}
        <BannerCarousel onPress={() => navigation.navigate('FighterForm')} />

        {/* Banner de Entradas El Jab Dorado */}
        <GeneralTicketBanner
          onPress={handleTicketPress}
          title="EL JAB DORADO"
          subtitle="Sábado 22 de Febrero - Santa Clara"
          buttonText="COMPRAR S/. 10"
        />
        <View style={styles.actionsSection}>
          <TouchableOpacity
            style={styles.actionButton}
            onPress={() => navigation.navigate('Fighters')}
          >
            <Ionicons name="people" size={24} color={COLORS.primary} />
            <Text style={styles.actionButtonText}>VER TODOS LOS PELEADORES</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.actionButtonSecondary}
            onPress={() => navigation.navigate('RegisterUser')}
          >
            <Ionicons name="person-add" size={20} color={COLORS.text.secondary} />
            <Text style={styles.actionButtonSecondaryText}>CREAR CUENTA</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.loginButton}
            onPress={() => navigation.navigate('Login')}
          >
            <Ionicons name="log-in" size={18} color={COLORS.primary} />
            <Text style={styles.loginButtonText}>¿Ya tienes cuenta? INICIA SESIÓN</Text>
          </TouchableOpacity>
        </View>

        {/* Espaciado inferior para el bottom nav */}
        <View style={styles.bottomSpacer} />
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  loadingContainer: {
    flex: 1,
    backgroundColor: COLORS.background,
    justifyContent: 'center',
    alignItems: 'center',
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    paddingBottom: SPACING.xl,
  },
  section: {
    paddingVertical: SPACING.lg,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: SPACING.lg,
    marginBottom: SPACING.md,
  },
  sectionTitle: {
    fontSize: 24,
    fontWeight: '700',
    color: COLORS.text.primary,
  },
  seeAllButton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  seeAllText: {
    fontSize: 14,
    fontWeight: '600',
    color: COLORS.primary,
  },
  carouselContent: {
    paddingHorizontal: SPACING.lg,
  },
  actionsSection: {
    paddingHorizontal: SPACING.lg,
    paddingVertical: SPACING.xl,
    gap: SPACING.md,
  },
  actionButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.surface,
    paddingVertical: SPACING.lg,
    borderRadius: 12,
    borderWidth: 2,
    borderColor: COLORS.primary,
    gap: SPACING.sm,
  },
  actionButtonText: {
    color: COLORS.primary,
    fontSize: 16,
    fontWeight: '700',
    letterSpacing: 1,
  },
  actionButtonSecondary: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.background,
    paddingVertical: SPACING.md,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: COLORS.border.light,
    gap: SPACING.sm,
    marginTop: SPACING.sm,
  },
  actionButtonSecondaryText: {
    color: COLORS.text.secondary,
    fontSize: 14,
    fontWeight: '600',
    letterSpacing: 0.5,
  },
  loginButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: 'transparent',
    paddingVertical: SPACING.sm,
    gap: SPACING.xs,
  },
  loginButtonText: {
    color: COLORS.primary,
    fontSize: 12,
    fontWeight: '600',
  },
  bottomSpacer: {
    height: 150,
  },
});
