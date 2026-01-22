import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  SafeAreaView,
  StatusBar,
  ScrollView,
  TouchableOpacity,
  Alert,
  ActivityIndicator,
  Image,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation, useFocusEffect } from '@react-navigation/native';
import * as Haptics from 'expo-haptics';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS } from '../constants/theme';

const API_BASE_URL = 'https://boxtiove.com';

interface Usuario {
  id: number;
  nombre: string;
  apellidos?: string;
  email: string;
  telefono?: string;
  tipo_id: number;
  tipo_nombre: string;
  club_id?: number;
  estado: string;
  fecha_registro: string;
  foto_perfil?: string;
  peleador?: {
    id: number;
    apodo: string;
    genero: string;
    categoria: string;
    peso: number;
    altura: number;
    victorias: number;
    derrotas: number;
    empates: number;
  };
}

export default function ProfileScreen() {
  const navigation = useNavigation();
  const [user, setUser] = useState<Usuario | null>(null);
  const [loading, setLoading] = useState(true);

  const loadUserData = async () => {
    try {
      const userData = await AsyncStorage.getItem('user');
      if (userData) {
        setUser(JSON.parse(userData));
      }
    } catch (error) {
      console.error('Error cargando datos del usuario:', error);
    } finally {
      setLoading(false);
    }
  };

  // Cargar datos cuando la pantalla recibe foco
  useFocusEffect(
    React.useCallback(() => {
      loadUserData();
    }, [])
  );

    const handleLogout = async () => {
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);
    Alert.alert(
      'Cerrar Sesión',
      '¿Estás seguro que deseas salir de tu cuenta?',
      [
        {
          text: 'Cancelar',
          style: 'cancel',
        },
        {
          text: 'Salir',
          style: 'destructive',
          onPress: async () => {
            try {
              await AsyncStorage.removeItem('user');
              await AsyncStorage.removeItem('token');
              setUser(null);
              Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
              Alert.alert('Sesión Cerrada', 'Has cerrado sesión exitosamente');
            } catch (error) {
              console.error('Error al cerrar sesión:', error);
              Alert.alert('Error', 'No se pudo cerrar la sesión');
            }
          },
        },
      ]
    );
  };
  if (loading) {
    return (
      <SafeAreaView style={styles.container}>
        <StatusBar barStyle="light-content" backgroundColor={COLORS.background} />
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color={COLORS.primary} />
          <Text style={styles.loadingText}>Cargando perfil...</Text>
        </View>
      </SafeAreaView>
    );
  }

  // Si no hay usuario autenticado
  if (!user) {
    return (
      <SafeAreaView style={styles.container}>
        <StatusBar barStyle="light-content" backgroundColor={COLORS.background} />
        <View style={styles.emptyContainer}>
          <Ionicons name="person-circle-outline" size={120} color={COLORS.text.tertiary} />
          <Text style={styles.emptyTitle}>No has iniciado sesión</Text>
          <Text style={styles.emptySubtitle}>
            Inicia sesión o crea una cuenta para acceder a tu perfil
          </Text>

          <TouchableOpacity
            style={styles.primaryButton}
            onPress={() => {
              Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
              navigation.navigate('Login' as never);
            }}
          >
            <Ionicons name="log-in" size={24} color={COLORS.text.inverse} />
            <Text style={styles.primaryButtonText}>INICIAR SESIÓN</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={styles.secondaryButton}
            onPress={() => {
              Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
              navigation.navigate('RegisterUser' as never);
            }}
          >
            <Ionicons name="person-add" size={24} color={COLORS.primary} />
            <Text style={styles.secondaryButtonText}>CREAR CUENTA</Text>
          </TouchableOpacity>
        </View>
      </SafeAreaView>
    );
  }

  // Usuario autenticado
  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor={COLORS.background} />

      <ScrollView
        style={styles.scrollView}
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
      >
        {/* Header con avatar */}
        <View style={styles.header}>
          <View style={styles.avatarContainer}>
            {user.foto_perfil ? (
              <Image
                source={{ uri: `${API_BASE_URL}/${user.foto_perfil}` }}
                style={styles.avatarImage}
              />
            ) : (
              <Ionicons
                name={user.tipo_id === 2 ? 'fitness' : 'person'}
                size={60}
                color={COLORS.text.inverse}
              />
            )}
          </View>
          <Text style={styles.userName}>
            {user.nombre} {user.apellidos || ''}
          </Text>
          <View style={styles.userTypeBadge}>
            <Text style={styles.userTypeText}>
              {user.tipo_nombre.toUpperCase()}
            </Text>
          </View>
        </View>

        {/* Información del usuario */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>📋 INFORMACIÓN PERSONAL</Text>

          <View style={styles.infoCard}>
            <View style={styles.infoRow}>
              <Ionicons name="mail" size={20} color={COLORS.primary} />
              <View style={styles.infoContent}>
                <Text style={styles.infoLabel}>Email</Text>
                <Text style={styles.infoValue}>{user.email}</Text>
              </View>
            </View>

            {user.telefono && (
              <View style={styles.infoRow}>
                <Ionicons name="call" size={20} color={COLORS.primary} />
                <View style={styles.infoContent}>
                  <Text style={styles.infoLabel}>Teléfono</Text>
                  <Text style={styles.infoValue}>{user.telefono}</Text>
                </View>
              </View>
            )}

            <View style={styles.infoRow}>
              <Ionicons name="calendar" size={20} color={COLORS.primary} />
              <View style={styles.infoContent}>
                <Text style={styles.infoLabel}>Miembro desde</Text>
                <Text style={styles.infoValue}>
                  {new Date(user.fecha_registro).toLocaleDateString('es-ES', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                  })}
                </Text>
              </View>
            </View>

            <View style={styles.infoRow}>
              <Ionicons name="checkmark-circle" size={20} color={COLORS.success} />
              <View style={styles.infoContent}>
                <Text style={styles.infoLabel}>Estado</Text>
                <Text style={[styles.infoValue, { color: COLORS.success }]}>
                  {user.estado.toUpperCase()}
                </Text>
              </View>
            </View>
          </View>
        </View>

        {/* Si es peleador, mostrar estadísticas */}
        {user.tipo_id === 2 && user.peleador && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>🥊 PERFIL DE PELEADOR</Text>

            <View style={styles.fighterCard}>
              <View style={styles.fighterHeader}>
                <Text style={styles.fighterNickname}>"{user.peleador.apodo}"</Text>
                <View style={styles.genderBadge}>
                  <Text style={styles.genderText}>
                    {user.peleador.genero === 'M' ? '♂ MASCULINO' : '♀ FEMENINO'}
                  </Text>
                </View>
              </View>

              <View style={styles.fighterStats}>
                <View style={styles.statBox}>
                  <Text style={styles.statLabel}>Categoría</Text>
                  <Text style={styles.statValue}>{user.peleador.categoria}</Text>
                </View>

                <View style={styles.statBox}>
                  <Text style={styles.statLabel}>Peso</Text>
                  <Text style={styles.statValue}>{user.peleador.peso} kg</Text>
                </View>

                <View style={styles.statBox}>
                  <Text style={styles.statLabel}>Altura</Text>
                  <Text style={styles.statValue}>{user.peleador.altura} cm</Text>
                </View>
              </View>

              <View style={styles.recordContainer}>
                <Text style={styles.recordTitle}>RÉCORD PROFESIONAL</Text>
                <View style={styles.recordStats}>
                  <View style={[styles.recordItem, { backgroundColor: COLORS.success + '20' }]}>
                    <Text style={styles.recordNumber}>{user.peleador.victorias}</Text>
                    <Text style={[styles.recordLabel, { color: COLORS.success }]}>Victorias</Text>
                  </View>

                  <View style={[styles.recordItem, { backgroundColor: COLORS.error + '20' }]}>
                    <Text style={styles.recordNumber}>{user.peleador.derrotas}</Text>
                    <Text style={[styles.recordLabel, { color: COLORS.error }]}>Derrotas</Text>
                  </View>

                  <View style={[styles.recordItem, { backgroundColor: COLORS.warning + '20' }]}>
                    <Text style={styles.recordNumber}>{user.peleador.empates}</Text>
                    <Text style={[styles.recordLabel, { color: COLORS.warning }]}>Empates</Text>
                  </View>
                </View>
              </View>
            </View>
          </View>
        )}

        {/* Botón de Panel Admin (solo para administradores) */}
        {user.tipo_id === 1 && (
          <TouchableOpacity
            style={styles.adminButton}
            onPress={() => {
              Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);
              navigation.navigate('AdminPanel' as never);
            }}
          >
            <Ionicons name="shield-checkmark" size={24} color="#fff" />
            <Text style={styles.adminButtonText}>PANEL DE ADMINISTRACIÓN</Text>
          </TouchableOpacity>
        )}

        {/* Botón de cerrar sesión */}
        <TouchableOpacity style={styles.logoutButton} onPress={handleLogout}>
          <Ionicons name="log-out" size={24} color={COLORS.error} />
          <Text style={styles.logoutButtonText}>CERRAR SESIÓN</Text>
        </TouchableOpacity>

        <View style={styles.bottomSpace} />
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    paddingBottom: SPACING.xxl * 2,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    gap: SPACING.md,
  },
  loadingText: {
    fontSize: TYPOGRAPHY.fontSize.md,
    color: COLORS.text.secondary,
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: SPACING.xl,
    gap: SPACING.md,
  },
  emptyTitle: {
    fontSize: TYPOGRAPHY.fontSize.xl,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
    marginTop: SPACING.md,
  },
  emptySubtitle: {
    fontSize: TYPOGRAPHY.fontSize.md,
    color: COLORS.text.secondary,
    textAlign: 'center',
    marginBottom: SPACING.lg,
  },
  primaryButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.primary,
    paddingVertical: SPACING.md,
    paddingHorizontal: SPACING.xl,
    borderRadius: BORDER_RADIUS.md,
    gap: SPACING.sm,
    width: '100%',
    marginBottom: SPACING.md,
  },
  primaryButtonText: {
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.inverse,
  },
  secondaryButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.surface,
    paddingVertical: SPACING.md,
    paddingHorizontal: SPACING.xl,
    borderRadius: BORDER_RADIUS.md,
    borderWidth: 2,
    borderColor: COLORS.primary,
    gap: SPACING.sm,
    width: '100%',
  },
  secondaryButtonText: {
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.primary,
  },
  header: {
    padding: SPACING.xl,
    alignItems: 'center',
    gap: SPACING.md,
  },
  avatarContainer: {
    width: 120,
    height: 120,
    borderRadius: 60,
    backgroundColor: COLORS.primary,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 4,
    borderColor: COLORS.primary + '40',
    overflow: 'hidden',
  },
  avatarImage: {
    width: '100%',
    height: '100%',
    resizeMode: 'cover',
  },
  userName: {
    fontSize: TYPOGRAPHY.fontSize.xxl,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
    textAlign: 'center',
  },
  userTypeBadge: {
    backgroundColor: COLORS.primary + '20',
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.xs,
    borderRadius: BORDER_RADIUS.sm,
    borderWidth: 1,
    borderColor: COLORS.primary,
  },
  userTypeText: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.primary,
    letterSpacing: 1,
  },
  section: {
    paddingHorizontal: SPACING.lg,
    marginBottom: SPACING.xl,
  },
  sectionTitle: {
    fontSize: TYPOGRAPHY.fontSize.lg,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
    marginBottom: SPACING.md,
  },
  infoCard: {
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.md,
    padding: SPACING.lg,
    gap: SPACING.md,
  },
  infoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.md,
    paddingVertical: SPACING.sm,
  },
  infoContent: {
    flex: 1,
  },
  infoLabel: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.text.tertiary,
    marginBottom: 2,
  },
  infoValue: {
    fontSize: TYPOGRAPHY.fontSize.md,
    color: COLORS.text.primary,
    fontWeight: TYPOGRAPHY.fontWeight.semiBold,
  },
  fighterCard: {
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.md,
    padding: SPACING.lg,
    gap: SPACING.lg,
    borderWidth: 2,
    borderColor: COLORS.primary + '30',
  },
  fighterHeader: {
    alignItems: 'center',
    gap: SPACING.sm,
  },
  fighterNickname: {
    fontSize: TYPOGRAPHY.fontSize.xl,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.primary,
    fontStyle: 'italic',
  },
  genderBadge: {
    backgroundColor: COLORS.primary + '15',
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.xs,
    borderRadius: BORDER_RADIUS.sm,
  },
  genderText: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
    letterSpacing: 0.5,
  },
  fighterStats: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    paddingVertical: SPACING.md,
    borderTopWidth: 1,
    borderBottomWidth: 1,
    borderColor: COLORS.border.primary,
  },
  statBox: {
    alignItems: 'center',
    gap: SPACING.xs,
  },
  statLabel: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    color: COLORS.text.tertiary,
    textTransform: 'uppercase',
  },
  statValue: {
    fontSize: TYPOGRAPHY.fontSize.lg,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
  },
  recordContainer: {
    gap: SPACING.md,
  },
  recordTitle: {
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
    textAlign: 'center',
    letterSpacing: 0.5,
  },
  recordStats: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    gap: SPACING.sm,
  },
  recordItem: {
    flex: 1,
    padding: SPACING.md,
    borderRadius: BORDER_RADIUS.sm,
    alignItems: 'center',
    gap: SPACING.xs,
  },
  recordNumber: {
    fontSize: TYPOGRAPHY.fontSize.xxl,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
  },
  recordLabel: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    fontWeight: TYPOGRAPHY.fontWeight.semiBold,
    textTransform: 'uppercase',
  },
  adminButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#e74c3c',
    paddingVertical: SPACING.md,
    paddingHorizontal: SPACING.xl,
    borderRadius: BORDER_RADIUS.md,
    gap: SPACING.sm,
    marginHorizontal: SPACING.lg,
    marginTop: SPACING.xl,
    shadowColor: '#e74c3c',
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 8,
  },
  adminButtonText: {
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: '#fff',
    letterSpacing: 0.5,
  },
  logoutButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.surface,
    paddingVertical: SPACING.md,
    paddingHorizontal: SPACING.xl,
    borderRadius: BORDER_RADIUS.md,
    borderWidth: 2,
    borderColor: COLORS.error,
    gap: SPACING.sm,
    marginHorizontal: SPACING.lg,
    marginTop: SPACING.lg,
  },
  logoutButtonText: {
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.error,
  },
  bottomSpace: {
    height: SPACING.xl,
  },
});
