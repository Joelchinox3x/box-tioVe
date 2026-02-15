import React, { useState, useEffect } from 'react';
import { View, ScrollView, StyleSheet, Text, TouchableOpacity, ActivityIndicator, SafeAreaView, StatusBar, Dimensions, Platform } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { ScreenHeader } from '../../components/common/ScreenHeader';
import { COLORS, SPACING, BORDER_RADIUS, SHADOWS, TYPOGRAPHY } from '../../constants/theme';
import { Ionicons } from '@expo/vector-icons';
import { AdminService } from '../../services/AdminService';
import ApprovalFighters from './ApprovalFighters';
import ClubsManagement from './ClubsManagement';
import AssignOwners from './AssignOwners';
import PaymentManagement from './PaymentManagement';
import AdminBannerScreen from './AdminBannerScreen';
import AdminBrandingScreen from './AdminBrandingScreen';
import AdminSettingsScreen from './AdminSettingsScreen';
import AdminAnunciosScreen from './AdminAnunciosScreen';
import { SettingsService } from '../../services/SettingsService';

type AdminSection = 'dashboard' | 'fighters' | 'clubs' | 'owners' | 'payments' | 'banners' | 'branding' | 'settings' | 'anuncios';

interface Estadisticas {
  peleadores_pendientes: number;
  peleadores_aprobados: number;
  clubs_activos: number;
  usuarios_activos: number;
  banners_activos?: number;
}

export default function AdminPanel({ navigation }: any) {
  const [currentSection, setCurrentSection] = useState<AdminSection>('dashboard');
  const [estadisticas, setEstadisticas] = useState<Estadisticas | null>(null);
  const [loading, setLoading] = useState(true);
  const [navMode, setNavMode] = useState<'normal' | 'hidden' | 'auto_hide'>('normal');
  const [navHiddenByAutoHide, setNavHiddenByAutoHide] = useState(false);

  useEffect(() => {
    loadEstadisticas();
    loadNavMode();
  }, []);

  const loadNavMode = async () => {
    const res = await SettingsService.getSetting('admin_nav_mode');
    if (res.success && res.value) {
      setNavMode(res.value as any);
    }
  };

  // Para auto_hide: al navegar desde dashboard, ocultar tabs
  const navigateToSection = (section: AdminSection) => {
    if (navMode === 'auto_hide' && section !== 'dashboard' && section !== 'settings') {
      setNavHiddenByAutoHide(true);
    }
    setCurrentSection(section);
  };

  // Determinar si los tabs se deben mostrar
  const shouldShowTabs = () => {
    if (navMode === 'hidden') return false;
    if (navMode === 'auto_hide' && navHiddenByAutoHide) return false;
    return true;
  };

  const loadEstadisticas = async () => {
    try {
      const data = await AdminService.getEstadisticas();
      setEstadisticas(data.estadisticas);
    } catch (error) {
      console.error('Error cargando estadísticas:', error);
    } finally {
      setLoading(false);
    }
  };

  const renderDashboard = () => (
    <View style={styles.dashboard}>

      {loading ? (
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color={COLORS.primary} />
          <Text style={styles.loadingText}>Cargando datos...</Text>
        </View>
      ) : (
        <>
          <View style={styles.statsRow}>
            <TouchableOpacity style={styles.statCardWrapper} onPress={() => navigateToSection('fighters')}>
              <LinearGradient colors={['#FF416C', '#FF4B2B']} style={styles.statCard} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }}>
                <Text style={styles.statNumber}>{estadisticas?.peleadores_pendientes || 0}</Text>
                <Text style={styles.statLabel} numberOfLines={1} adjustsFontSizeToFit>Pendiente</Text>
              </LinearGradient>
            </TouchableOpacity>

            <TouchableOpacity style={styles.statCardWrapper} onPress={() => navigateToSection('fighters')}>
              <LinearGradient colors={['#11998e', '#38ef7d']} style={styles.statCard} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }}>
                <Text style={styles.statNumber}>{estadisticas?.peleadores_aprobados || 0}</Text>
                <Text style={styles.statLabel} numberOfLines={1} adjustsFontSizeToFit>Listo</Text>
              </LinearGradient>
            </TouchableOpacity>

            <TouchableOpacity style={styles.statCardWrapper} onPress={() => navigateToSection('clubs')}>
              <LinearGradient colors={['#2193b0', '#6dd5ed']} style={styles.statCard} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }}>
                <Text style={styles.statNumber}>{estadisticas?.clubs_activos || 0}</Text>
                <Text style={styles.statLabel} numberOfLines={1} adjustsFontSizeToFit>Clubs</Text>
              </LinearGradient>
            </TouchableOpacity>

            <TouchableOpacity style={styles.statCardWrapper}>
              <LinearGradient colors={['#bdc3c7', '#2c3e50']} style={styles.statCard} start={{ x: 0, y: 0 }} end={{ x: 1, y: 1 }}>
                <Text style={styles.statNumber}>{estadisticas?.usuarios_activos || 0}</Text>
                <Text style={styles.statLabel} numberOfLines={1} adjustsFontSizeToFit>Usuarios</Text>
              </LinearGradient>
            </TouchableOpacity>
          </View>
        </>
      )}

      <Text style={styles.sectionHeader}>GESTIÓN RÁPIDA</Text>

      <View style={styles.actionsGrid}>
        <TouchableOpacity style={styles.actionCard} onPress={() => navigateToSection('fighters')}>
          <LinearGradient colors={[COLORS.surface, COLORS.surface]} style={styles.actionGradient}>
            <View style={[styles.actionIconCircle, { backgroundColor: 'rgba(231, 76, 60, 0.1)' }]}>
              <Ionicons name="fitness" size={24} color="#e74c3c" />
            </View>
            <Text style={styles.actionTitle}>Peleadores</Text>
            <Text style={styles.actionDesc}>Aprobar y gestionar</Text>
          </LinearGradient>
        </TouchableOpacity>

        <TouchableOpacity style={styles.actionCard} onPress={() => navigateToSection('clubs')}>
          <LinearGradient colors={[COLORS.surface, COLORS.surface]} style={styles.actionGradient}>
            <View style={[styles.actionIconCircle, { backgroundColor: 'rgba(52, 152, 219, 0.1)' }]}>
              <Ionicons name="business" size={24} color="#3498db" />
            </View>
            <Text style={styles.actionTitle}>Clubs</Text>
            <Text style={styles.actionDesc}>Administrar gimnasios</Text>
          </LinearGradient>
        </TouchableOpacity>

        <TouchableOpacity style={styles.actionCard} onPress={() => navigateToSection('owners')}>
          <LinearGradient colors={[COLORS.surface, COLORS.surface]} style={styles.actionGradient}>
            <View style={[styles.actionIconCircle, { backgroundColor: 'rgba(241, 196, 15, 0.1)' }]}>
              <Ionicons name="key" size={24} color="#f1c40f" />
            </View>
            <Text style={styles.actionTitle}>Dueños</Text>
            <Text style={styles.actionDesc}>Asignar permisos</Text>
          </LinearGradient>
        </TouchableOpacity>

        <TouchableOpacity style={styles.actionCard} onPress={() => navigateToSection('payments')}>
          <LinearGradient colors={[COLORS.surface, COLORS.surface]} style={styles.actionGradient}>
            <View style={[styles.actionIconCircle, { backgroundColor: 'rgba(46, 204, 113, 0.1)' }]}>
              <Ionicons name="cash" size={24} color="#2ecc71" />
            </View>
            <Text style={styles.actionTitle}>Pagos</Text>
            <Text style={styles.actionDesc}>Inscripciones</Text>
          </LinearGradient>
        </TouchableOpacity>

        <TouchableOpacity style={styles.actionCard} onPress={() => navigateToSection('banners')}>
          <LinearGradient colors={[COLORS.surface, COLORS.surface]} style={styles.actionGradient}>
            <View style={[styles.actionIconCircle, { backgroundColor: 'rgba(155, 89, 182, 0.1)' }]}>
              <Ionicons name="images" size={24} color="#9b59b6" />
            </View>
            <Text style={styles.actionTitle}>Banners</Text>
            <Text style={styles.actionDesc}>Publicidad Home</Text>
          </LinearGradient>
        </TouchableOpacity>

        <TouchableOpacity style={styles.actionCard} onPress={() => navigateToSection('anuncios')}>
          <LinearGradient colors={[COLORS.surface, COLORS.surface]} style={styles.actionGradient}>
            <View style={[styles.actionIconCircle, { backgroundColor: 'rgba(59, 130, 246, 0.1)' }]}>
              <Ionicons name="megaphone" size={24} color="#3B82F6" />
            </View>
            <Text style={styles.actionTitle}>Anuncios</Text>
            <Text style={styles.actionDesc}>Avisos y Noticias</Text>
          </LinearGradient>
        </TouchableOpacity>

        <TouchableOpacity style={styles.actionCard} onPress={() => navigateToSection('branding')}>
          <LinearGradient colors={[COLORS.surface, COLORS.surface]} style={styles.actionGradient}>
            <View style={[styles.actionIconCircle, { backgroundColor: 'rgba(52, 73, 94, 0.1)' }]}>
              <Ionicons name="brush" size={24} color="#34495e" />
            </View>
            <Text style={styles.actionTitle}>Branding</Text>
            <Text style={styles.actionDesc}>Identidad Visual</Text>
          </LinearGradient>
        </TouchableOpacity>

        <TouchableOpacity style={styles.actionCard} onPress={() => navigation.navigate('AdminBoletos')}>
          <LinearGradient colors={[COLORS.surface, COLORS.surface]} style={styles.actionGradient}>
            <View style={[styles.actionIconCircle, { backgroundColor: 'rgba(230, 126, 34, 0.1)' }]}>
              <Ionicons name="ticket" size={24} color="#e67e22" />
            </View>
            <Text style={styles.actionTitle}>Boletos</Text>
            <Text style={styles.actionDesc}>Venta de entradas</Text>
          </LinearGradient>
        </TouchableOpacity>

        <TouchableOpacity style={styles.actionCard} onPress={() => navigateToSection('settings')}>
          <LinearGradient colors={[COLORS.surface, COLORS.surface]} style={styles.actionGradient}>
            <View style={[styles.actionIconCircle, { backgroundColor: 'rgba(149, 165, 166, 0.1)' }]}>
              <Ionicons name="settings" size={24} color="#95a5a6" />
            </View>
            <Text style={styles.actionTitle}>Config</Text>
            <Text style={styles.actionDesc}>Sistema</Text>
          </LinearGradient>
        </TouchableOpacity>
      </View>
    </View>
  );

  const sectionHeaders: Record<AdminSection, { title: string; subtitle: string }> = {
    dashboard: { title: 'PANEL DE CONTROL', subtitle: 'ADMINISTRACIÓN GLOBAL' },
    fighters:  { title: 'PELEADORES', subtitle: 'GESTIÓN DE PELEADORES' },
    clubs:     { title: 'CLUBS', subtitle: 'ADMINISTRAR GIMNASIOS' },
    owners:    { title: 'DUEÑOS', subtitle: 'ASIGNAR PERMISOS' },
    payments:  { title: 'GESTION DE PAGOS', subtitle: 'INSCRIPCIONES Y COBROS' },
    banners:   { title: 'BANNERS', subtitle: 'PUBLICIDAD HOME' },
    branding:  { title: 'BRANDING', subtitle: 'IDENTIDAD VISUAL' },
    anuncios:  { title: 'ANUNCIOS', subtitle: 'GESTIONAR AVISOS' },
    settings:  { title: 'CONFIGURACIÓN', subtitle: 'AJUSTES DEL SISTEMA' },
  };

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor="#000" />
      <ScreenHeader
        title={sectionHeaders[currentSection].title}
        subtitle={sectionHeaders[currentSection].subtitle}
        showBackButton={true}
        onBackPress={() => {
          if (currentSection !== 'dashboard') {
            setCurrentSection('dashboard');
            setNavHiddenByAutoHide(false);
            // Recargar navMode por si cambió en settings
            if (currentSection === 'settings') loadNavMode();
          } else {
            navigation.navigate('Profile' as never);
          }
        }}
      />

      {/* Navegación tipo Tabs/Pills */}
      {shouldShowTabs() && (
        <View style={styles.navContainer}>
          <ScrollView
            horizontal
            showsHorizontalScrollIndicator={Platform.OS === 'web'}
            contentContainerStyle={[styles.navContent, { paddingBottom: Platform.OS === 'web' ? 15 : 0 }]}
          >
            {(['fighters', 'clubs', 'owners', 'payments', 'anuncios', 'banners', 'branding'] as AdminSection[]).map((sec) => {
              const pillLabels: Record<string, string> = {
                fighters: 'PELEADORES', clubs: 'CLUBS', owners: 'DUEÑOS',
                payments: 'PAGOS', anuncios: 'ANUNCIOS', banners: 'BANNERS', branding: 'BRANDING',
              };
              return (
              <TouchableOpacity
                key={sec}
                style={[styles.pill, currentSection === sec && styles.pillActive]}
                onPress={() => navigateToSection(sec)}
              >
                <Text style={[styles.pillText, currentSection === sec && styles.pillTextActive]}>
                  {pillLabels[sec] || sec.toUpperCase()}
                </Text>
              </TouchableOpacity>
              );
            })}
          </ScrollView>
        </View>
      )}

      {/* Contenido */}
      {currentSection === 'dashboard' && (
        <ScrollView style={styles.content} contentContainerStyle={{ paddingBottom: 100 }}>
          {renderDashboard()}
        </ScrollView>
      )}

      {currentSection === 'fighters' && <ApprovalFighters />}

      {currentSection === 'clubs' && <ClubsManagement />}

      {currentSection === 'owners' && <AssignOwners />}

      {currentSection === 'payments' && <PaymentManagement />}

      {currentSection === 'anuncios' && <AdminAnunciosScreen />}

      {currentSection === 'banners' && <AdminBannerScreen />}

      {currentSection === 'branding' && <AdminBrandingScreen />}

      {currentSection === 'settings' && <AdminSettingsScreen />}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  navContainer: {
    paddingVertical: SPACING.md,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border.primary,
    flexDirection: 'row', // Align fixed button and list
    alignItems: 'center',
    paddingLeft: SPACING.lg
  },
  navContent: {
    paddingHorizontal: SPACING.sm, // Reduced since paddingLeft is on parent
    gap: SPACING.sm,
  },
  fixedPill: {
    marginRight: SPACING.sm,
    width: 40,
    height: 40,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 0, // Reset default pill padding
    paddingVertical: 0
  },
  pill: {
    paddingHorizontal: SPACING.lg,
    paddingVertical: SPACING.sm,
    borderRadius: BORDER_RADIUS.full,
    borderWidth: 1,
    borderColor: COLORS.text.tertiary,
    backgroundColor: 'transparent',
  },
  pillActive: {
    backgroundColor: COLORS.primary,
    borderColor: COLORS.primary,
  },
  pillText: {
    color: COLORS.text.secondary,
    fontWeight: 'bold',
    fontSize: 12,
  },
  pillTextActive: {
    color: COLORS.text.inverse,
  },
  content: {
    flex: 1,
  },
  dashboard: {
    padding: SPACING.lg,
  },
  loadingContainer: {
    padding: 20,
    alignItems: 'center',
    gap: 10
  },
  loadingText: {
    color: '#AAA'
  },
  statsRow: {
    flexDirection: 'row',
    gap: SPACING.md,
    marginBottom: SPACING.md,
  },
  statCardWrapper: {
    flex: 1,
  },
  statCard: {
    flex: 1,
    padding: SPACING.sm, // Reduced padding
    borderRadius: BORDER_RADIUS.lg,
    alignItems: 'center',
    justifyContent: 'center',
    height: 80, // Reduced height
    ...SHADOWS.md
  },
  // Removed statIconContainer styles as we removed the icons
  statNumber: {
    fontSize: 24, // Slightly smaller
    fontWeight: '900',
    color: '#FFF',
    marginBottom: 0,
    textShadowColor: 'rgba(0,0,0,0.3)',
    textShadowOffset: { width: 0, height: 1 },
    textShadowRadius: 3
  },
  statLabel: {
    fontSize: 12,
    color: 'rgba(255,255,255,0.9)',
    fontWeight: 'bold',
    textTransform: 'uppercase'
  },
  sectionHeader: {
    fontSize: 18,
    fontWeight: 'bold',
    color: COLORS.text.primary,
    marginTop: SPACING.lg,
    marginBottom: SPACING.md,
    letterSpacing: 1
  },
  actionsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: SPACING.md,
  },
  actionCard: {
    width: '30%', // 3 columns
    height: 150,

    borderRadius: BORDER_RADIUS.lg,
    overflow: 'hidden',

    ...SHADOWS.sm,
  },
  actionGradient: {
    flex: 1,
    padding: SPACING.md,
    alignItems: 'center',
    justifyContent: 'center',
    gap: SPACING.xs
  },
  actionIconCircle: {
    width: 50,
    height: 50,
    borderRadius: 25,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: SPACING.xs
  },
  actionTitle: {
    fontSize: 14,
    fontWeight: 'bold',
    color: COLORS.text.primary,
    textAlign: 'center'
  },
  actionDesc: {
    fontSize: 10,
    color: COLORS.text.tertiary,
    textAlign: 'center'
  }
});
