import React, { useState, useEffect } from 'react';
import { View, ScrollView, StyleSheet, Text, TouchableOpacity, ActivityIndicator, SafeAreaView, StatusBar } from 'react-native';
import { AdminService } from '../../services/AdminService';
import ApprovalFighters from './ApprovalFighters';
import ClubsManagement from './ClubsManagement';
import AssignOwners from './AssignOwners';
import PaymentManagement from './PaymentManagement';
import AdminBannerScreen from './AdminBannerScreen';

type AdminSection = 'dashboard' | 'fighters' | 'clubs' | 'owners' | 'payments' | 'banners';

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

  useEffect(() => {
    loadEstadisticas();
  }, []);

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
      <Text style={styles.sectionTitle}>Panel de Administración</Text>

      {loading ? (
        <ActivityIndicator size="large" color="#e74c3c" />
      ) : (
        <View style={styles.statsGrid}>
          <View style={[styles.statCard, { backgroundColor: '#e74c3c' }]}>
            <Text style={styles.statNumber}>{estadisticas?.peleadores_pendientes || 0}</Text>
            <Text style={styles.statLabel}>Peleadores Pendientes</Text>
          </View>

          <View style={[styles.statCard, { backgroundColor: '#27ae60' }]}>
            <Text style={styles.statNumber}>{estadisticas?.peleadores_aprobados || 0}</Text>
            <Text style={styles.statLabel}>Peleadores Aprobados</Text>
          </View>

          <View style={[styles.statCard, { backgroundColor: '#3498db' }]}>
            <Text style={styles.statNumber}>{estadisticas?.clubs_activos || 0}</Text>
            <Text style={styles.statLabel}>Clubs Activos</Text>
          </View>

          <View style={[styles.statCard, { backgroundColor: '#9b59b6' }]}>
            <Text style={styles.statNumber}>{estadisticas?.usuarios_activos || 0}</Text>
            <Text style={styles.statLabel}>Usuarios Activos</Text>
          </View>
        </View>
      )}

      <View style={styles.actionsGrid}>
        <TouchableOpacity
          style={[styles.actionCard, { backgroundColor: '#e74c3c' }]}
          onPress={() => setCurrentSection('fighters')}
        >
          <Text style={styles.actionIcon}>👊</Text>
          <Text style={styles.actionTitle}>Aprobar Peleadores</Text>
          <Text style={styles.actionDesc}>Gestionar solicitudes pendientes</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.actionCard, { backgroundColor: '#3498db' }]}
          onPress={() => setCurrentSection('clubs')}
        >
          <Text style={styles.actionIcon}>🏛️</Text>
          <Text style={styles.actionTitle}>Gestionar Clubs</Text>
          <Text style={styles.actionDesc}>Crear y administrar clubs</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.actionCard, { backgroundColor: '#f39c12' }]}
          onPress={() => setCurrentSection('owners')}
        >
          <Text style={styles.actionIcon}>👤</Text>
          <Text style={styles.actionTitle}>Asignar Dueños</Text>
          <Text style={styles.actionDesc}>Buscar y asignar dueños a clubs</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.actionCard, { backgroundColor: '#27ae60' }]}
          onPress={() => setCurrentSection('payments')}
        >
          <Text style={styles.actionIcon}>💰</Text>
          <Text style={styles.actionTitle}>Gestionar Pagos</Text>
          <Text style={styles.actionDesc}>Inscripciones y confirmación de pagos</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.actionCard, { backgroundColor: '#8e44ad' }]}
          onPress={() => setCurrentSection('banners')}
        >
          <Text style={styles.actionIcon}>🖼️</Text>
          <Text style={styles.actionTitle}>Banners Home</Text>
          <Text style={styles.actionDesc}>Gestionar "¿Quieres Pelear?"</Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.actionCard, { backgroundColor: '#e67e22' }]}
          onPress={() => navigation.navigate('AdminBoletos')}
        >
          <Text style={styles.actionIcon}>🎫</Text>
          <Text style={styles.actionTitle}>Gestionar Boletos</Text>
          <Text style={styles.actionDesc}>Ventas, pagos y validación de entradas</Text>
        </TouchableOpacity>
      </View>
    </View>
  );

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor="#1a1a1a" />

      {/* Navegación superior */}
      <View style={styles.navbar}>
        <TouchableOpacity
          style={[styles.navItem, currentSection === 'dashboard' && styles.navItemActive]}
          onPress={() => setCurrentSection('dashboard')}
        >
          <Text style={[styles.navText, currentSection === 'dashboard' && styles.navTextActive]}>
            Dashboard
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.navItem, currentSection === 'fighters' && styles.navItemActive]}
          onPress={() => setCurrentSection('fighters')}
        >
          <Text style={[styles.navText, currentSection === 'fighters' && styles.navTextActive]}>
            Peleadores
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.navItem, currentSection === 'clubs' && styles.navItemActive]}
          onPress={() => setCurrentSection('clubs')}
        >
          <Text style={[styles.navText, currentSection === 'clubs' && styles.navTextActive]}>
            Clubs
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.navItem, currentSection === 'owners' && styles.navItemActive]}
          onPress={() => setCurrentSection('owners')}
        >
          <Text style={[styles.navText, currentSection === 'owners' && styles.navTextActive]}>
            Dueños
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.navItem, currentSection === 'payments' && styles.navItemActive]}
          onPress={() => setCurrentSection('payments')}
        >
          <Text style={[styles.navText, currentSection === 'payments' && styles.navTextActive]}>
            Pagos
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.navItem, currentSection === 'banners' && styles.navItemActive]}
          onPress={() => setCurrentSection('banners')}
        >
          <Text style={[styles.navText, currentSection === 'banners' && styles.navTextActive]}>
            Banners
          </Text>
        </TouchableOpacity>
      </View>

      {/* Contenido - Render directo sin wrapper condicional */}
      {currentSection === 'dashboard' && (
        <ScrollView style={styles.content}>
          {renderDashboard()}
        </ScrollView>
      )}

      {currentSection === 'fighters' && <ApprovalFighters />}

      {currentSection === 'clubs' && <ClubsManagement />}

      {currentSection === 'owners' && <AssignOwners />}

      {currentSection === 'payments' && <PaymentManagement />}

      {currentSection === 'banners' && <AdminBannerScreen />}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#1a1a1a',
  },
  navbar: {
    flexDirection: 'row',
    backgroundColor: '#2c2c2c',
    borderBottomWidth: 2,
    borderBottomColor: '#e74c3c',
  },
  navItem: {
    flex: 1,
    paddingVertical: 15,
    alignItems: 'center',
  },
  navItemActive: {
    backgroundColor: '#e74c3c',
  },
  navText: {
    color: '#999',
    fontSize: 14,
    fontWeight: '600',
  },
  navTextActive: {
    color: '#fff',
  },
  content: {
    flex: 1,
  },
  dashboard: {
    padding: 20,
  },
  sectionTitle: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#fff',
    marginBottom: 20,
  },
  statsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginBottom: 30,
    gap: 15,
  },
  statCard: {
    flex: 1,
    minWidth: '45%',
    padding: 20,
    borderRadius: 15,
    alignItems: 'center',
  },
  statNumber: {
    fontSize: 36,
    fontWeight: 'bold',
    color: '#fff',
    marginBottom: 5,
  },
  statLabel: {
    fontSize: 14,
    color: '#fff',
    textAlign: 'center',
  },
  actionsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 15,
  },
  actionCard: {
    flex: 1,
    minWidth: '45%',
    padding: 20,
    borderRadius: 15,
    alignItems: 'center',
  },
  actionIcon: {
    fontSize: 40,
    marginBottom: 10,
  },
  actionTitle: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#fff',
    marginBottom: 5,
    textAlign: 'center',
  },
  actionDesc: {
    fontSize: 12,
    color: '#fff',
    textAlign: 'center',
    opacity: 0.9,
  },
  placeholder: {
    fontSize: 18,
    color: '#fff',
    textAlign: 'center',
    marginTop: 50,
  },
});
