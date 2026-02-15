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
  Platform,
  RefreshControl,
  Linking,
  Modal,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation, useFocusEffect } from '@react-navigation/native';
import * as Haptics from 'expo-haptics';
import * as ImagePicker from 'expo-image-picker';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS, SHADOWS } from '../constants/theme';
import { createShadow } from '../utils/shadows';
import { ScreenHeader } from '../components/common/ScreenHeader';
import { clubService, Club } from '../services/clubService';
import { fighterService } from '../services/fighterService';
import api from '../services/api';
import { ConfirmModal } from '../components/ConfirmModal';
import { ChangePasswordModal } from '../components/common/ChangePasswordModal';
import { AdminService } from '../services/AdminService';
import { Config } from '../config/config';
import { getCategoria } from '../utils/categories';

interface MetodoPagoInfo {
  id: number;
  codigo: string;
  nombre: string;
  requiere_comprobante: number;
  activo: number;
  qr_imagen_url: string | null;
  telefono_receptor: string | null;
  nombre_receptor: string | null;
}

const API_BASE_URL = Config.BASE_URL;

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
    edad?: number;
    victorias: number;
    derrotas: number;
    empates: number;
  };
  es_primer_login?: number;
}

export default function ProfileScreen() {
  const navigation = useNavigation();
  const [user, setUser] = useState<Usuario | null>(null);
  const [club, setClub] = useState<Club | null>(null);
  const [loading, setLoading] = useState(true);
  const [loadingExtra, setLoadingExtra] = useState(false);
  const [refreshing, setRefreshing] = useState(false);
  const [logoutModalVisible, setLogoutModalVisible] = useState(false);
  const [passwordModalVisible, setPasswordModalVisible] = useState(false);
  const [messageModal, setMessageModal] = useState<{ visible: boolean; type: 'success' | 'error'; message: string }>({ visible: false, type: 'success', message: '' });

  // Estado de inscripción al evento
  const [eventoData, setEventoData] = useState<any>(null);
  const [inscripcionData, setInscripcionData] = useState<any>(null);
  const [estadoPeleador, setEstadoPeleador] = useState<string>('pendiente');
  const [loadingInscripcion, setLoadingInscripcion] = useState(false);
  const [inscribiendose, setInscribiendose] = useState(false);
  const [managerContacto, setManagerContacto] = useState<{ id: number; nombre_visible: string; telefono_whatsapp: string; mensaje_base: string; rol: string } | null>(null);
  const [managerCobros, setManagerCobros] = useState<{ id: number; nombre_visible: string; telefono_whatsapp: string; mensaje_base: string; rol: string } | null>(null);
  const [managerGeneral, setManagerGeneral] = useState<{ id: number; nombre_visible: string; telefono_whatsapp: string; mensaje_base: string; rol: string } | null>(null);
  const [metodoPagoSeleccionado, setMetodoPagoSeleccionado] = useState<string>('');
  const [comprobante, setComprobante] = useState<{ uri: string; name: string; type: string } | null>(null);
  const [metodosPagoDisponibles, setMetodosPagoDisponibles] = useState<MetodoPagoInfo[]>([]);

  const getImageUrl = (path: string | null | undefined): string | null => {
    if (!path) return null;
    if (path.startsWith('http')) return path;
    return `${Config.BASE_URL}/${path}`;
  };

  const requiresComprobante = (metodo: string) => {
    const found = metodosPagoDisponibles.find(m => m.codigo === metodo);
    return found ? found.requiere_comprobante === 1 : ['yape', 'plin', 'transferencia', 'deposito'].includes(metodo);
  };

  const loadMetodosPago = async () => {
    try {
      const result = await AdminService.getMetodosPago({ activo: 1 });
      if (result.success && result.metodos) {
        setMetodosPagoDisponibles(result.metodos);
      }
    } catch (error) {
      console.log('Error cargando métodos de pago:', error);
    }
  };

  const pickComprobante = async () => {
    try {
      const permissionResult = await ImagePicker.requestMediaLibraryPermissionsAsync();
      if (!permissionResult.granted) {
        Alert.alert('Permiso requerido', 'Se requiere permiso para acceder a tu galería.');
        return;
      }

      const result = await ImagePicker.launchImageLibraryAsync({
        mediaTypes: ['images'],
        allowsEditing: false,
        quality: 0.8,
      });

      if (!result.canceled && result.assets && result.assets.length > 0) {
        const asset = result.assets[0];
        const uri = asset.uri;
        const uriParts = uri.split('.');
        const fileType = uriParts[uriParts.length - 1] || 'jpg';
        const name = `comprobante_${Date.now()}.${fileType}`;
        const type = `image/${fileType === 'jpg' ? 'jpeg' : fileType}`;

        setComprobante({ uri, name, type });
        Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
      }
    } catch (err) {
      console.error('Error al seleccionar comprobante:', err);
      Alert.alert('Error', 'No se pudo seleccionar el comprobante.');
    }
  };

  const loadUserData = async () => {
    try {
      const userData = await AsyncStorage.getItem('user');
      if (userData) {
        const parsedUser = JSON.parse(userData);
        setUser(parsedUser); // Show cached data first for speed

        // 🚀 FETCH FRESH DATA
        try {
          await fetchFreshData(parsedUser.id);
        } catch (fetchErr) {
          console.log('⚠️ Could not fetch fresh data, using cache only');
        }

        // Cargar datos extra si tiene club (usando el ID posiblemente actualizado)
        if (parsedUser.club_id) {
          loadClubData(parsedUser.club_id);
        }
        // Cargar inscripción si es peleador (con datos cacheados)
        if (parsedUser.tipo_id === 2 && parsedUser.peleador?.id) {
          loadInscripcionEvento(parsedUser.peleador.id);
        }
      }
    } catch (error) {
      console.error('Error cargando datos del usuario:', error);
    } finally {
      setLoading(false);
    }
  };

  const fetchFreshData = async (userId: number) => {
    try {
      const response = await api.get(`/usuarios/${userId}`);

      if (response.data && response.data.success) {
        const freshUser = response.data.usuario;
        console.log('✅ Datos actualizados recibidos:', freshUser);
        setUser(freshUser);

        // Update Cache
        await AsyncStorage.setItem('user', JSON.stringify(freshUser));

        if (freshUser.club_id) {
          loadClubData(freshUser.club_id);
        }

        // Cargar inscripción si es peleador
        if (freshUser.tipo_id === 2 && freshUser.peleador?.id) {
          loadInscripcionEvento(freshUser.peleador.id);
        }

        // 🛡️ SECURITY PROMPT: Check if first login
        if (freshUser.es_primer_login === 1) {
          setTimeout(() => setPasswordModalVisible(true), 1000); // Wait a bit for UX
        }
      }
    } catch (error) {
      console.error('Error fetching fresh data:', error);
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
    if (user?.id) {
      await fetchFreshData(user.id);
    }
    setRefreshing(false);
  };

  const loadClubData = async (clubId: number) => {
    try {
      setLoadingExtra(true);
      const clubData = await clubService.getById(clubId);
      setClub(clubData);
    } catch (error) {
      console.error('Error cargando datos del club:', error);
    } finally {
      setLoadingExtra(false);
    }
  };

  const loadInscripcionEvento = async (peleadorId: number) => {
    try {
      setLoadingInscripcion(true);
      const result = await fighterService.getInscripcionEvento(peleadorId);
      if (result.success) {
        setEstadoPeleador(result.estado_peleador || 'pendiente');
        setEventoData(result.evento);
        setInscripcionData(result.inscripcion);

        if (result.evento && !result.inscripcion) {
          // Hay evento pero no se ha inscrito → mostrar boton + manager
          loadManagerByRol('manager_peleadores', setManagerContacto);
        } else if (result.inscripcion && result.inscripcion.estado_pago === 'inscrito') {
          // Estado INSCRITO: se inscribió pero no ha pagado → cargar métodos de pago
          loadMetodosPago();
          loadManagerByRol('manager_cobros', setManagerCobros);
        } else if (result.inscripcion && result.inscripcion.estado_pago === 'pendiente') {
          // Estado PENDIENTE: pago enviado, esperando confirmación del admin
          loadManagerByRol('manager_cobros', setManagerCobros);
          loadManagerByRol('manager_general', setManagerGeneral);
        } else if (result.inscripcion && result.inscripcion.estado_pago === 'pagado') {
          // Estado PAGADO: pago confirmado
          loadManagerByRol('manager_general', setManagerGeneral);
        }

        if (result.estado_peleador === 'rechazado') {
          loadManagerByRol('manager_peleadores', setManagerContacto);
        }
      }
    } catch (error) {
      console.log('Error cargando inscripción:', error);
    } finally {
      setLoadingInscripcion(false);
    }
  };

  const loadManagerByRol = async (rol: 'manager_peleadores' | 'manager_cobros' | 'manager_general', setter: (m: any) => void) => {
    try {
      const result = await fighterService.getManagerContacto(rol);
      if (result.success && result.manager) {
        setter(result.manager);
      }
    } catch (error) {
      console.log(`Error cargando manager ${rol}:`, error);
    }
  };

  const handleInscribirse = async () => {
    if (!metodoPagoSeleccionado) {
      setMessageModal({ visible: true, type: 'error', message: 'Selecciona un método de pago antes de inscribirte.' });
      return;
    }
    if (requiresComprobante(metodoPagoSeleccionado) && !comprobante) {
      setMessageModal({ visible: true, type: 'error', message: 'Sube el voucher de tu pago antes de continuar.' });
      return;
    }
    if (!user?.peleador?.id || !eventoData?.id) return;

    try {
      setInscribiendose(true);
      const result = await fighterService.inscribirEvento(
        user.peleador.id,
        eventoData.id,
        metodoPagoSeleccionado,
        comprobante || undefined
      );

      // Mostrar modal de éxito
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
      setMessageModal({
        visible: true,
        type: 'success',
        message: '✅ Tu pago fue enviado correctamente. Un administrador lo verificará pronto.'
      });

      // Esperar un momento antes de recargar para que el usuario vea el modal
      setTimeout(() => {
        loadInscripcionEvento(user.peleador.id);
        setComprobante(null);
        setMetodoPagoSeleccionado('');
      }, 500);

    } catch (error: any) {
      const errorMsg = error?.response?.data?.message || 'No se pudo completar la inscripción';
      setMessageModal({ visible: true, type: 'error', message: errorMsg });
    } finally {
      setInscribiendose(false);
    }
  };

  const handleCrearInscripcion = async () => {
    if (!user?.peleador?.id || !eventoData?.id) return;
    try {
      setInscribiendose(true);
      const result = await fighterService.crearInscripcion(user.peleador.id, eventoData.id);

      // Mostrar modal de confirmación
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
      setMessageModal({
        visible: true,
        type: 'success',
        message: '✅ Te inscribiste correctamente. Ahora selecciona tu método de pago para continuar.'
      });

      // Recargar para mostrar la pantalla de pago
      setTimeout(() => {
        loadInscripcionEvento(user.peleador.id);
      }, 500);

    } catch (error: any) {
      const errorMsg = error?.response?.data?.message || 'No se pudo crear la inscripción';
      setMessageModal({ visible: true, type: 'error', message: errorMsg });
    } finally {
      setInscribiendose(false);
    }
  };

  const comprobanteRequired = metodoPagoSeleccionado ? requiresComprobante(metodoPagoSeleccionado) : false;

  // Cargar datos cuando la pantalla recibe foco
  useFocusEffect(
    React.useCallback(() => {
      loadUserData();
    }, [])
  );

  const handleLogout = () => {
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);
    setLogoutModalVisible(true);
  };

  const confirmLogout = async () => {
    try {
      await AsyncStorage.removeItem('user');
      await AsyncStorage.removeItem('token');
      setUser(null);
      setLogoutModalVisible(false);
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
    } catch (error) {
      console.error('Error al cerrar sesión:', error);
      Alert.alert('Error', 'No se pudo cerrar la sesión');
    }
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
      <StatusBar barStyle="light-content" backgroundColor="#000" />
      <ScreenHeader
        title="MI PERFIL"
        showBackButton={true}
        onBackPress={() => navigation.navigate('Home' as never)}
      />

      <ScrollView
        style={styles.scrollView}
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            tintColor={COLORS.primary} // iOS
            colors={[COLORS.primary]} // Android
          />
        }
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
                    {(user.peleador.genero?.toLowerCase().startsWith('m')) ? '♂ MASCULINO' : '♀ FEMENINO'}
                  </Text>
                </View>
              </View>

              <View style={styles.fighterStats}>
                <View style={styles.statBox}>
                  <Text style={styles.statLabel}>Categoría</Text>
                  <Text style={styles.statValue}>{user.peleador.categoria || getCategoria(user.peleador.peso)}</Text>
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

        {/* Sección: Inscripción al Evento (Solo para peleadores) */}
        {user.tipo_id === 2 && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>📅 MI INSCRIPCIÓN AL EVENTO</Text>

            {loadingInscripcion ? (
              <View style={styles.enrollmentCard}>
                <ActivityIndicator size="small" color={COLORS.primary} />
                <Text style={[styles.enrollmentTitle, { textAlign: 'center', marginTop: SPACING.sm }]}>
                  Cargando...
                </Text>
              </View>
            ) : !eventoData ? (
              /* No hay evento activo */
              <View style={styles.enrollmentCard}>
                <View style={styles.enrollmentHeader}>
                  <Ionicons name="calendar-outline" size={24} color={COLORS.text.tertiary} />
                  <Text style={[styles.enrollmentTitle, { color: COLORS.text.tertiary }]}>
                    No hay eventos próximos
                  </Text>
                </View>
                <Text style={{ color: COLORS.text.secondary, fontSize: TYPOGRAPHY.fontSize.sm }}>
                  Cuando se programe un evento, aquí podrás inscribirte.
                </Text>
              </View>
            ) : estadoPeleador === 'rechazado' ? (
              /* ESTADO: Peleador RECHAZADO */
              <View style={[styles.enrollmentCard, { borderColor: COLORS.error + '50' }]}>
                <View style={styles.enrollmentHeader}>
                  <Ionicons name="close-circle" size={24} color={COLORS.error} />
                  <Text style={styles.enrollmentTitle}>{eventoData.nombre}</Text>
                </View>
                <View style={[styles.statusChip, { backgroundColor: COLORS.error }]}>
                  <Text style={styles.statusChipText}>RECHAZADO</Text>
                </View>
                <View style={styles.lockedMessage}>
                  <Ionicons name="information-circle" size={18} color={COLORS.error} />
                  <Text style={styles.lockedMessageText}>
                    Tu perfil fue rechazado. Contacta al administrador para más información.
                  </Text>
                </View>
                {managerContacto && (
                  <TouchableOpacity
                    style={styles.whatsappButton}
                    onPress={() => {
                      const phone = managerContacto.telefono_whatsapp.replace(/\D/g, '');
                      const phoneWithCode = phone.startsWith('51') ? phone : `51${phone}`;
                      const nombre = `${user.nombre} ${user.apellidos || ''}`.trim();
                      const apodo = user.peleador?.apodo ? ` "${user.peleador.apodo}"` : '';
                      const edad = user.peleador?.edad ? `, ${user.peleador.edad} años` : '';
                      const peso = user.peleador?.peso ? `, ${user.peleador.peso}kg` : '';
                      const msg = `Hola, soy ${nombre}${apodo}${edad}${peso}. Mi perfil fue rechazado y quiero más información. Gracias.`;
                      Linking.openURL(`https://wa.me/${phoneWithCode}?text=${encodeURIComponent(msg)}`);
                    }}
                  >
                    <Ionicons name="logo-whatsapp" size={20} color="#fff" />
                    <Text style={styles.whatsappButtonText}>
                      Contactar a {managerContacto.nombre_visible}
                    </Text>
                  </TouchableOpacity>
                )}
              </View>
            ) : eventoData && !inscripcionData ? (
              /* ESTADO: Hay evento pero NO se ha inscrito aún */
              <View style={[styles.enrollmentCard, { borderColor: COLORS.primary + '50' }]}>
                <View style={styles.enrollmentHeader}>
                  <Ionicons name="trophy" size={24} color={COLORS.primary} />
                  <Text style={styles.enrollmentTitle}>{eventoData.nombre}</Text>
                </View>

                <View style={styles.eventoInfoRow}>
                  <Ionicons name="calendar" size={16} color={COLORS.text.secondary} />
                  <Text style={styles.eventoInfoText}>
                    {new Date(eventoData.fecha).toLocaleDateString('es-PE', { day: 'numeric', month: 'long', year: 'numeric' })}
                  </Text>
                </View>
                {eventoData.direccion && (
                  <View style={styles.eventoInfoRow}>
                    <Ionicons name="location" size={16} color={COLORS.text.secondary} />
                    <Text style={styles.eventoInfoText}>{eventoData.direccion}</Text>
                  </View>
                )}

                <View style={styles.precioContainer}>
                  <Text style={styles.precioLabel}>Precio de inscripción:</Text>
                  <Text style={styles.precioValue}>S/ {Number(eventoData.precio_inscripcion_peleador || 0).toFixed(2)}</Text>
                </View>

                <TouchableOpacity
                  style={styles.inscribirseBtn}
                  onPress={handleCrearInscripcion}
                  disabled={inscribiendose}
                >
                  {inscribiendose ? (
                    <ActivityIndicator size="small" color={COLORS.text.inverse} />
                  ) : (
                    <>
                      <Ionicons name="hand-right" size={20} color={COLORS.text.inverse} />
                      <Text style={styles.inscribirseBtnText}>INSCRIBIRME AL EVENTO</Text>
                    </>
                  )}
                </TouchableOpacity>

                {managerContacto && (
                  <TouchableOpacity
                    style={[styles.whatsappButton, { marginTop: SPACING.sm }]}
                    onPress={() => {
                      const phone = managerContacto.telefono_whatsapp.replace(/\D/g, '');
                      const phoneWithCode = phone.startsWith('51') ? phone : `51${phone}`;
                      const nombre = `${user.nombre} ${user.apellidos || ''}`.trim();
                      const apodo = user.peleador?.apodo ? ` "${user.peleador.apodo}"` : '';
                      const edad = user.peleador?.edad ? `, ${user.peleador.edad} años` : '';
                      const peso = user.peleador?.peso ? `, ${user.peleador.peso}kg` : '';
                      const evento = eventoData?.nombre || 'el evento';
                      const msg = `Hola, soy ${nombre}${apodo}${edad}${peso}. Quiero inscribirme en ${evento}. Quedo atento!`;
                      Linking.openURL(`https://wa.me/${phoneWithCode}?text=${encodeURIComponent(msg)}`);
                    }}
                  >
                    <Ionicons name="logo-whatsapp" size={20} color="#fff" />
                    <Text style={styles.whatsappButtonText}>
                      Dudas? Contactar a {managerContacto.nombre_visible}
                    </Text>
                  </TouchableOpacity>
                )}
              </View>
            ) : inscripcionData && inscripcionData.estado_pago === 'inscrito' ? (
              /* ESTADO: INSCRITO - se inscribió pero aún no ha pagado */
              <View style={[styles.enrollmentCard, { borderColor: COLORS.primary + '50' }]}>
                <View style={styles.enrollmentHeader}>
                  <Ionicons name="trophy" size={24} color={COLORS.primary} />
                  <Text style={styles.enrollmentTitle}>{eventoData.nombre}</Text>
                </View>

                <View style={styles.eventoInfoRow}>
                  <Ionicons name="calendar" size={16} color={COLORS.text.secondary} />
                  <Text style={styles.eventoInfoText}>
                    {new Date(eventoData.fecha).toLocaleDateString('es-PE', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'UTC' })}
                    {eventoData.hora ? ` • ${eventoData.hora.substring(0, 5)}` : ''}
                  </Text>
                </View>

                {eventoData.direccion && (
                  <View style={styles.eventoInfoRow}>
                    <Ionicons name="location" size={16} color={COLORS.text.secondary} />
                    <Text style={styles.eventoInfoText}>{eventoData.direccion}</Text>
                  </View>
                )}

                <View style={styles.precioContainer}>
                  <Text style={styles.precioLabel}>Precio de inscripción:</Text>
                  <Text style={styles.precioValue}>S/ {Number(eventoData.precio_inscripcion_peleador || 0).toFixed(2)}</Text>
                </View>

                {/* Selector de método de pago con datos reales de BD */}
                <Text style={styles.metodoPagoLabel}>Selecciona método de pago:</Text>
                <View style={styles.metodoPagoGrid}>
                  {metodosPagoDisponibles.map((metodo) => {
                    const iconMap: { [key: string]: string } = {
                      yape: 'phone-portrait',
                      plin: 'phone-portrait',
                      transferencia: 'card',
                      efectivo: 'cash',
                      deposito: 'business',
                    };
                    return (
                      <TouchableOpacity
                        key={metodo.codigo}
                        style={[
                          styles.metodoPagoOption,
                          metodoPagoSeleccionado === metodo.codigo && styles.metodoPagoOptionSelected,
                        ]}
                        onPress={() => {
                          setMetodoPagoSeleccionado(metodo.codigo);
                          setComprobante(null);
                        }}
                      >
                        <Ionicons
                          name={(iconMap[metodo.codigo] || 'wallet') as any}
                          size={18}
                          color={metodoPagoSeleccionado === metodo.codigo ? COLORS.text.inverse : COLORS.primary}
                        />
                        <Text
                          style={[
                            styles.metodoPagoText,
                            metodoPagoSeleccionado === metodo.codigo && styles.metodoPagoTextSelected,
                          ]}
                        >
                          {metodo.nombre}
                        </Text>
                      </TouchableOpacity>
                    );
                  })}
                </View>

                {/* Card de detalle del método seleccionado */}
                {metodoPagoSeleccionado !== '' && (() => {
                  const metodoInfo = metodosPagoDisponibles.find(m => m.codigo === metodoPagoSeleccionado);
                  if (!metodoInfo) return null;
                  const hasDetails = metodoInfo.nombre_receptor || metodoInfo.telefono_receptor || metodoInfo.qr_imagen_url;
                  if (!hasDetails) return null;
                  return (
                    <View style={styles.paymentInfoCard}>
                      <Text style={styles.paymentInfoTitle}>{metodoInfo.nombre}</Text>
                      {metodoInfo.nombre_receptor && (
                        <View style={styles.paymentDetailRow}>
                          <Ionicons name="person-outline" size={16} color={COLORS.text.secondary} />
                          <Text style={styles.paymentInfoText}>{metodoInfo.nombre_receptor}</Text>
                        </View>
                      )}
                      {metodoInfo.telefono_receptor && (
                        <View style={styles.paymentDetailRow}>
                          <Ionicons name="call-outline" size={16} color={COLORS.text.secondary} />
                          <Text style={styles.paymentInfoText}>{metodoInfo.telefono_receptor}</Text>
                        </View>
                      )}
                      {metodoInfo.qr_imagen_url && (
                        <View>
                          <Image
                            source={{ uri: getImageUrl(metodoInfo.qr_imagen_url) || '' }}
                            style={styles.paymentQr}
                            resizeMode="contain"
                          />
                          <TouchableOpacity
                            style={styles.downloadQrButton}
                            onPress={() => {
                              const url = getImageUrl(metodoInfo.qr_imagen_url);
                              if (url) Linking.openURL(url);
                            }}
                          >
                            <Ionicons name="download-outline" size={16} color={COLORS.primary} />
                            <Text style={styles.downloadQrText}>Descargar QR</Text>
                          </TouchableOpacity>
                        </View>
                      )}
                    </View>
                  );
                })()}

                {metodoPagoSeleccionado !== '' && metodoPagoSeleccionado !== 'efectivo' && (
                  <View style={styles.comprobanteSection}>
                    <Text style={styles.comprobanteLabel}>
                      Sube tu voucher de pago{comprobanteRequired ? ' *' : ' (opcional)'}
                    </Text>
                    {comprobante ? (
                      <View style={styles.comprobantePreviewRow}>
                        <Image source={{ uri: comprobante.uri }} style={styles.comprobantePreview} />
                        <View style={styles.comprobanteIconActions}>
                          <TouchableOpacity style={styles.comprobanteIconBtn} onPress={pickComprobante}>
                            <Ionicons name="camera-reverse-outline" size={20} color={COLORS.primary} />
                          </TouchableOpacity>
                          <TouchableOpacity
                            style={[styles.comprobanteIconBtn, { backgroundColor: COLORS.error + '15' }]}
                            onPress={() => setComprobante(null)}
                          >
                            <Ionicons name="trash" size={20} color={COLORS.error} />
                          </TouchableOpacity>
                        </View>
                      </View>
                    ) : (
                      <TouchableOpacity style={styles.comprobanteButton} onPress={pickComprobante}>
                        <Ionicons name="cloud-upload-outline" size={18} color={COLORS.text.inverse} />
                        <Text style={styles.comprobanteButtonText}>Subir voucher</Text>
                      </TouchableOpacity>
                    )}
                  </View>
                )}

                <TouchableOpacity
                  style={[
                    styles.inscribirseBtn,
                    (!metodoPagoSeleccionado || (comprobanteRequired && !comprobante)) && { opacity: 0.5 },
                  ]}
                  onPress={handleInscribirse}
                  disabled={inscribiendose || !metodoPagoSeleccionado || (comprobanteRequired && !comprobante)}
                >
                  {inscribiendose ? (
                    <ActivityIndicator size="small" color={COLORS.text.inverse} />
                  ) : (
                    <>
                      <Ionicons name="checkmark-circle" size={20} color={COLORS.text.inverse} />
                      <Text style={styles.inscribirseBtnText}>ENVIAR PAGO</Text>
                    </>
                  )}
                </TouchableOpacity>

                {managerCobros && (
                  <TouchableOpacity
                    style={[styles.whatsappButton, { marginTop: SPACING.sm }]}
                    onPress={() => {
                      const phone = managerCobros.telefono_whatsapp.replace(/\D/g, '');
                      const phoneWithCode = phone.startsWith('51') ? phone : `51${phone}`;
                      const nombre = `${user.nombre} ${user.apellidos || ''}`.trim();
                      const apodo = user.peleador?.apodo ? ` "${user.peleador.apodo}"` : '';
                      const edad = user.peleador?.edad ? `, ${user.peleador.edad} años` : '';
                      const peso = user.peleador?.peso ? `, ${user.peleador.peso}kg` : '';
                      const evento = eventoData?.nombre || 'el evento';
                      const msg = `Hola, soy ${nombre}${apodo}${edad}${peso}. Quiero coordinar el pago de mi inscripción a ${evento}.`;
                      Linking.openURL(`https://wa.me/${phoneWithCode}?text=${encodeURIComponent(msg)}`);
                    }}
                  >
                    <Ionicons name="logo-whatsapp" size={20} color="#fff" />
                    <Text style={styles.whatsappButtonText}>
                      Dudas sobre pago? Contactar a {managerCobros.nombre_visible}
                    </Text>
                  </TouchableOpacity>
                )}
              </View>
            ) : inscripcionData && inscripcionData.estado_pago === 'pendiente' ? (
              /* ESTADO: PENDIENTE - pago enviado, esperando confirmación del admin */
              <View style={[styles.enrollmentCard, { borderColor: COLORS.warning + '50' }]}>
                <View style={styles.enrollmentHeader}>
                  <Ionicons name="trophy" size={24} color={COLORS.primary} />
                  <Text style={styles.enrollmentTitle}>{eventoData.nombre}</Text>
                </View>

                <View style={styles.enrollmentStatusRow}>
                  <View style={[styles.statusChip, { backgroundColor: COLORS.warning }]}>
                    <Text style={styles.statusChipText}>PAGO PENDIENTE</Text>
                  </View>
                  <Text style={styles.paymentMethod}>
                    {inscripcionData.metodo_pago?.charAt(0).toUpperCase() + inscripcionData.metodo_pago?.slice(1)} • S/ {Number(inscripcionData.monto_pagado || eventoData.precio_inscripcion_peleador || 0).toFixed(2)}
                  </Text>
                </View>

                <View style={styles.pendingMessage}>
                  <Ionicons name="time" size={18} color={COLORS.warning} />
                  <Text style={styles.pendingMessageText}>
                    Tu inscripción fue registrada. Un administrador confirmará tu pago pronto.
                  </Text>
                </View>

                <Text style={styles.pendingDate}>
                  Inscrito el {new Date(inscripcionData.fecha_inscripcion).toLocaleDateString('es-PE', { day: 'numeric', month: 'long', year: 'numeric' })}
                </Text>

                {managerGeneral && (
                  <TouchableOpacity
                    style={[styles.whatsappButton, { marginTop: SPACING.sm }]}
                    onPress={() => {
                      const phone = managerGeneral.telefono_whatsapp.replace(/\D/g, '');
                      const phoneWithCode = phone.startsWith('51') ? phone : `51${phone}`;
                      const nombre = `${user.nombre} ${user.apellidos || ''}`.trim();
                      const apodo = user.peleador?.apodo ? ` "${user.peleador.apodo}"` : '';
                      const evento = eventoData?.nombre || 'el evento';
                      const msg = `Hola ${managerGeneral.nombre_visible}, soy ${nombre}${apodo}. Tengo una consulta sobre mi pago pendiente para ${evento}.`;
                      Linking.openURL(`https://wa.me/${phoneWithCode}?text=${encodeURIComponent(msg)}`);
                    }}
                  >
                    <Ionicons name="logo-whatsapp" size={20} color="#fff" />
                    <Text style={styles.whatsappButtonText}>
                      Dudas? Contactar a {managerGeneral.nombre_visible}
                    </Text>
                  </TouchableOpacity>
                )}
              </View>
            ) : (
              /* ESTADO 4: PAGO CONFIRMADO */
              <View style={[styles.enrollmentCard, { borderColor: COLORS.success + '50' }]}>
                <View style={styles.enrollmentHeader}>
                  <Ionicons name="trophy" size={24} color={COLORS.primary} />
                  <Text style={styles.enrollmentTitle}>{eventoData.nombre}</Text>
                </View>

                <View style={styles.enrollmentStatusRow}>
                  <View style={[styles.statusChip, { backgroundColor: COLORS.success }]}>
                    <Text style={styles.statusChipText}>PAGO CONFIRMADO</Text>
                  </View>
                  <Text style={styles.paymentMethod}>
                    {inscripcionData.metodo_pago?.charAt(0).toUpperCase() + inscripcionData.metodo_pago?.slice(1)} • S/ {Number(inscripcionData.monto_pagado || 0).toFixed(2)}
                  </Text>
                </View>

                <View style={styles.confirmedMessage}>
                  <Ionicons name="checkmark-circle" size={20} color={COLORS.success} />
                  <Text style={styles.confirmedMessageText}>
                    Tu inscripción está confirmada. ¡Prepárate para la pelea!
                  </Text>
                </View>

                {inscripcionData.fecha_pago && (
                  <Text style={styles.pendingDate}>
                    Pagado el {new Date(inscripcionData.fecha_pago).toLocaleDateString('es-PE', { day: 'numeric', month: 'long', year: 'numeric' })}
                  </Text>
                )}

                {managerGeneral && (
                  <TouchableOpacity
                    style={[styles.whatsappButton, { marginTop: SPACING.sm }]}
                    onPress={() => {
                      const phone = managerGeneral.telefono_whatsapp.replace(/\D/g, '');
                      const phoneWithCode = phone.startsWith('51') ? phone : `51${phone}`;
                      const nombre = `${user.nombre} ${user.apellidos || ''}`.trim();
                      const apodo = user.peleador?.apodo ? ` "${user.peleador.apodo}"` : '';
                      const evento = eventoData?.nombre || 'el evento';
                      const msg = `Hola ${managerGeneral.nombre_visible}, soy ${nombre}${apodo}. Ya estoy inscrito en ${evento}, solo tenía una consulta.`;
                      Linking.openURL(`https://wa.me/${phoneWithCode}?text=${encodeURIComponent(msg)}`);
                    }}
                  >
                    <Ionicons name="logo-whatsapp" size={20} color="#fff" />
                    <Text style={styles.whatsappButtonText}>
                      Dudas? Contactar a {managerGeneral.nombre_visible}
                    </Text>
                  </TouchableOpacity>
                )}
              </View>
            )}
          </View>
        )}

        {/* Sección: Mi Club */}
        {(user.club_id || club) && (
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>🏛️ MI CLUB / GIMNASIO</Text>
            <View style={styles.clubCard}>
              <View style={styles.clubHeader}>
                <View style={styles.clubIconContainer}>
                  <Ionicons name="business" size={30} color={COLORS.primary} />
                </View>
                <View style={styles.clubHeaderText}>
                  <Text style={styles.clubName}>{club?.nombre || 'Mi Club'}</Text>
                  <Text style={styles.clubStatus}>Miembro Activo</Text>
                </View>
              </View>

              {club?.direccion && (
                <View style={styles.clubDetailRow}>
                  <Ionicons name="location-outline" size={18} color={COLORS.text.secondary} />
                  <Text style={styles.clubDetailText}>{club.direccion}</Text>
                </View>
              )}

              <TouchableOpacity
                style={styles.clubButton}
                onPress={() => Alert.alert('Próximamente', 'Podrás ver más detalles del club muy pronto.')}
              >
                <Text style={styles.clubButtonText}>VER PÁGINA DEL CLUB</Text>
                <Ionicons name="chevron-forward" size={16} color={COLORS.primary} />
              </TouchableOpacity>
            </View>
          </View>
        )}

        {/* Botón de Editar Perfil */}
        <TouchableOpacity
          style={styles.editProfileButton}
          onPress={() => Alert.alert('Editar Perfil', 'Esta función estará disponible en la siguiente actualización.')}
        >
          <Ionicons name="create-outline" size={24} color={COLORS.primary} />
          <Text style={styles.editProfileButtonText}>EDITAR MI INFORMACIÓN</Text>
        </TouchableOpacity>

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

        <ConfirmModal
          visible={logoutModalVisible}
          title="Cerrar Sesión"
          message="¿Estás seguro que deseas salir de tu cuenta?"
          confirmText="Sí, salir"
          cancelText="Cancelar"
          confirmColor={COLORS.error}
          onConfirm={confirmLogout}
          onCancel={() => setLogoutModalVisible(false)}
        />

        <ChangePasswordModal
          visible={passwordModalVisible}
          userId={user.id}
          onClose={() => setPasswordModalVisible(false)}
          onSuccess={() => {
            setPasswordModalVisible(false);
            onRefresh(); // Refresh to get the updated status
          }}
        />

        <View style={styles.bottomSpace} />
      </ScrollView>

      {/* Message Modal (success/error) */}
      <Modal
        visible={messageModal.visible}
        animationType="fade"
        transparent={true}
        onRequestClose={() => setMessageModal(prev => ({ ...prev, visible: false }))}
      >
        <View style={styles.messageModalOverlay}>
          <View style={styles.messageModalContainer}>
            <View style={[styles.messageModalIcon, {
              backgroundColor: messageModal.type === 'success' ? '#27ae6020' : '#e74c3c20'
            }]}>
              <Ionicons
                name={messageModal.type === 'success' ? 'checkmark-circle' : 'close-circle'}
                size={64}
                color={messageModal.type === 'success' ? '#27ae60' : '#e74c3c'}
              />
            </View>
            <Text style={styles.messageModalTitle}>
              {messageModal.type === 'success' ? 'Éxito' : 'Error'}
            </Text>
            <Text style={styles.messageModalMessage}>{messageModal.message}</Text>
            <TouchableOpacity
              style={[styles.messageModalBtn, {
                backgroundColor: messageModal.type === 'success' ? '#27ae60' : '#e74c3c'
              }]}
              onPress={() => setMessageModal(prev => ({ ...prev, visible: false }))}
            >
              <Text style={styles.messageModalBtnText}>Entendido</Text>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>
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
    paddingBottom: SPACING.xxl,
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
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
  },
  enrollmentCard: {
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.md,
    padding: SPACING.lg,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
    gap: SPACING.md,
  },
  enrollmentHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.sm,
  },
  enrollmentTitle: {
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
    flex: 1,
  },
  enrollmentStatusRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  statusChip: {
    backgroundColor: COLORS.success,
    paddingHorizontal: SPACING.sm,
    paddingVertical: 4,
    borderRadius: BORDER_RADIUS.sm,
  },
  statusChipText: {
    fontSize: 10,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.inverse,
    letterSpacing: 0.5,
  },
  paymentMethod: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    color: COLORS.text.secondary,
    fontWeight: TYPOGRAPHY.fontWeight.medium,
  },
  lockedMessage: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: SPACING.sm,
    backgroundColor: COLORS.warning + '10',
    padding: SPACING.md,
    borderRadius: BORDER_RADIUS.sm,
  },
  lockedMessageText: {
    flex: 1,
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.text.secondary,
    lineHeight: 20,
  },
  whatsappButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: SPACING.sm,
    backgroundColor: '#25D366',
    paddingVertical: 12,
    paddingHorizontal: SPACING.lg,
    borderRadius: BORDER_RADIUS.md,
    marginTop: SPACING.md,
  },
  whatsappButtonText: {
    color: '#fff',
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: '600',
  },
  eventoInfoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.sm,
  },
  eventoInfoText: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.text.secondary,
  },
  precioContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: COLORS.primary + '10',
    padding: SPACING.md,
    borderRadius: BORDER_RADIUS.sm,
  },
  precioLabel: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.text.secondary,
  },
  precioValue: {
    fontSize: TYPOGRAPHY.fontSize.xl,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.primary,
  },
  metodoPagoLabel: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
    color: COLORS.text.primary,
  },
  metodoPagoGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: SPACING.sm,
  },
  metodoPagoOption: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.xs,
    paddingVertical: SPACING.sm,
    paddingHorizontal: SPACING.md,
    borderRadius: BORDER_RADIUS.sm,
    borderWidth: 1.5,
    borderColor: COLORS.primary + '40',
    backgroundColor: COLORS.surface,
  },
  metodoPagoOptionSelected: {
    backgroundColor: COLORS.primary,
    borderColor: COLORS.primary,
  },
  metodoPagoText: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
    color: COLORS.primary,
  },
  metodoPagoTextSelected: {
    color: COLORS.text.inverse,
  },
  paymentInfoCard: {
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.sm,
    borderWidth: 1,
    borderColor: COLORS.primary + '30',
    padding: SPACING.md,
    gap: SPACING.xs,
  },
  paymentInfoTitle: {
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
  },
  paymentDetailRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.sm,
  },
  paymentInfoText: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.text.secondary,
  },
  paymentInfoHint: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    color: COLORS.text.tertiary,
  },
  downloadQrButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: SPACING.xs,
    paddingVertical: SPACING.sm,
    marginTop: SPACING.xs,
  },
  downloadQrText: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.primary,
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
  },
  paymentQr: {
    width: '100%',
    height: 160,
    marginTop: SPACING.xs,
    borderRadius: BORDER_RADIUS.sm,
    backgroundColor: COLORS.background,
  },
  comprobanteSection: {
    gap: SPACING.sm,
  },
  comprobanteLabel: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
    color: COLORS.text.primary,
  },
  comprobantePreviewRow: {
    flexDirection: 'row',
    gap: SPACING.md,
    alignItems: 'center',
    justifyContent: 'center',
  },
  comprobantePreview: {
    width: 150,
    height: 270,
    borderRadius: BORDER_RADIUS.sm,
    backgroundColor: COLORS.background,
  },
  comprobanteActions: {
    flex: 1,
    gap: SPACING.sm,
  },
  comprobanteIconActions: {
    flexDirection: 'column',
    gap: SPACING.sm,
  },
  comprobanteIconBtn: {
    width: 40,
    height: 40,
    borderRadius: BORDER_RADIUS.sm,
    backgroundColor: COLORS.primary + '15',
    justifyContent: 'center',
    alignItems: 'center',
  },
  comprobanteButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: SPACING.xs,
    backgroundColor: COLORS.primary,
    paddingVertical: SPACING.sm,
    paddingHorizontal: SPACING.md,
    borderRadius: BORDER_RADIUS.sm,
  },
  comprobanteRemoveButton: {
    backgroundColor: COLORS.error,
  },
  comprobanteButtonText: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.inverse,
  },
  inscribirseBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: SPACING.sm,
    backgroundColor: COLORS.primary,
    paddingVertical: SPACING.md,
    borderRadius: BORDER_RADIUS.md,
    marginTop: SPACING.xs,
  },
  inscribirseBtnText: {
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.inverse,
    letterSpacing: 0.5,
  },
  pendingMessage: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: SPACING.sm,
    backgroundColor: COLORS.warning + '10',
    padding: SPACING.md,
    borderRadius: BORDER_RADIUS.sm,
  },
  pendingMessageText: {
    flex: 1,
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.text.secondary,
    lineHeight: 20,
  },
  pendingDate: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    color: COLORS.text.tertiary,
    textAlign: 'right',
  },
  confirmedMessage: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.sm,
    backgroundColor: COLORS.success + '10',
    padding: SPACING.md,
    borderRadius: BORDER_RADIUS.sm,
  },
  confirmedMessageText: {
    flex: 1,
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.success,
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
    lineHeight: 20,
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
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
    textTransform: 'uppercase',
  },
  clubCard: {
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.md,
    padding: SPACING.lg,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
    ...SHADOWS.sm,
  },
  clubHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.md,
    marginBottom: SPACING.md,
  },
  clubIconContainer: {
    width: 50,
    height: 50,
    borderRadius: BORDER_RADIUS.sm,
    backgroundColor: COLORS.primary + '10',
    justifyContent: 'center',
    alignItems: 'center',
  },
  clubHeaderText: {
    flex: 1,
  },
  clubName: {
    fontSize: TYPOGRAPHY.fontSize.lg,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
  },
  clubStatus: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    color: COLORS.success,
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
  },
  clubDetailRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.sm,
    marginBottom: SPACING.md,
  },
  clubDetailText: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.text.secondary,
  },
  clubButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingTop: SPACING.md,
    borderTopWidth: 1,
    borderTopColor: COLORS.border.primary,
  },
  clubButtonText: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.primary,
  },
  editProfileButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.surface,
    paddingVertical: SPACING.md,
    marginHorizontal: SPACING.lg,
    borderRadius: BORDER_RADIUS.md,
    borderWidth: 2,
    borderColor: COLORS.primary,
    gap: SPACING.sm,
    marginBottom: SPACING.xl,
  },
  editProfileButtonText: {
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.primary,
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
    ...createShadow('#e74c3c', 0, 4, 0.3, 8, 8),
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
    height: 100,
  },
  messageModalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: SPACING.xl,
  },
  messageModalContainer: {
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.xl,
    width: '100%',
    maxWidth: 360,
    padding: SPACING.xl,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: COLORS.border.primary,
  },
  messageModalIcon: {
    width: 96,
    height: 96,
    borderRadius: 48,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: SPACING.md,
  },
  messageModalTitle: {
    fontSize: TYPOGRAPHY.fontSize.xxl,
    fontWeight: '700',
    color: COLORS.text.primary,
    marginBottom: SPACING.sm,
  },
  messageModalMessage: {
    fontSize: TYPOGRAPHY.fontSize.md,
    color: COLORS.text.secondary,
    textAlign: 'center',
    lineHeight: 22,
    marginBottom: SPACING.xl,
  },
  messageModalBtn: {
    width: '100%',
    paddingVertical: 14,
    borderRadius: BORDER_RADIUS.md,
    alignItems: 'center',
    justifyContent: 'center',
  },
  messageModalBtnText: {
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: '700',
    color: '#fff',
  },
});
