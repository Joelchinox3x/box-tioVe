import React, { useState, useEffect, useMemo } from 'react';
import {
  View,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  ActivityIndicator,
  TextInput,
  Platform,
  LayoutAnimation,
  UIManager,
  Modal,
  KeyboardAvoidingView,
  Linking,
  ImageBackground,
  Alert,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS, SHADOWS } from '../../constants/theme';
import { AdminService } from '../../services/AdminService';
import { Config } from '../../config/config';
import * as Sharing from 'expo-sharing';
import * as FileSystem from 'expo-file-system/legacy';
import { FighterCard } from '../../components/common/FighterCard';

// Enable LayoutAnimation on Android
if (Platform.OS === 'android' && UIManager.setLayoutAnimationEnabledExperimental) {
  UIManager.setLayoutAnimationEnabledExperimental(true);
}

interface Peleador {
  id: number;
  nombre: string;
  apellidos?: string;
  email: string;
  telefono: string;
  apodo: string;
  documento_identidad: string;
  peso_actual: number;
  altura: number;
  fecha_nacimiento: string;
  edad?: number;
  foto_perfil?: string | null;
  club_id: number | null;
  club_nombre: string | null;
  genero: string;
  estilo: string | null;
  categoria: string | null;
  experiencia_anos: number;
  victorias: number;
  derrotas: number;
  empates: number;
  estado_inscripcion: string;
  fecha_inscripcion: string;
  card_url: string | null;
  composition_json?: string | null;
}

interface Club {
  id: number;
  nombre: string;
}

type FilterType = 'todos' | 'pendiente' | 'aprobado' | 'rechazado';

const ESTADO_CONFIG: Record<string, { color: string; icon: keyof typeof Ionicons.glyphMap; label: string }> = {
  aprobado:  { color: '#27ae60', icon: 'checkmark-circle', label: 'APROBADO' },
  rechazado: { color: '#e74c3c', icon: 'close-circle',    label: 'RECHAZADO' },
  pendiente: { color: '#f39c12', icon: 'time',            label: 'PENDIENTE' },
};

export default function ApprovalFighters() {
  const [peleadores, setPeleadores] = useState<Peleador[]>([]);
  const [loading, setLoading] = useState(true);
  const [processingId, setProcessingId] = useState<number | null>(null);
  const [notasMap, setNotasMap] = useState<Record<number, string>>({});
  const [activeFilter, setActiveFilter] = useState<FilterType>('todos');
  const [searchQuery, setSearchQuery] = useState('');
  const [expandedIds, setExpandedIds] = useState<Set<number>>(new Set());
  const [editModalVisible, setEditModalVisible] = useState(false);
  const [editingPeleador, setEditingPeleador] = useState<Peleador | null>(null);
  const [editForm, setEditForm] = useState<Record<string, string>>({});
  const [savingEdit, setSavingEdit] = useState(false);
  const [clubs, setClubs] = useState<Club[]>([]);
  const [showClubPicker, setShowClubPicker] = useState(false);
  const [showGeneroPicker, setShowGeneroPicker] = useState(false);
  const [showEstiloPicker, setShowEstiloPicker] = useState(false);
  const [detailModalVisible, setDetailModalVisible] = useState(false);
  const [selectedPeleador, setSelectedPeleador] = useState<Peleador | null>(null);
  const [cardImageError, setCardImageError] = useState(false);
  const [companyLogoUri, setCompanyLogoUri] = useState<string | null>(null);
  const parsedComposition = React.useMemo(() => {
    if (!selectedPeleador?.composition_json) return null;
    try {
      return JSON.parse(selectedPeleador.composition_json);
    } catch {
      return null;
    }
  }, [selectedPeleador?.composition_json]);

  const normalizeUrl = (url?: string | null) => {
    if (!url) return null;
    return url.startsWith('http') ? url : `${Config.BASE_URL}/${url}`;
  };

  const compositionAssets = React.useMemo(() => {
    if (!parsedComposition) return null;
    const backgroundUri = normalizeUrl(parsedComposition?.background?.url);
    const borderUri = normalizeUrl(parsedComposition?.border?.url);
    const compLogo = normalizeUrl(parsedComposition?.companyLogo?.url);
    const layers = Array.isArray(parsedComposition?.layers)
      ? parsedComposition.layers.map((layer: any) => ({
          ...layer,
          uri: normalizeUrl(layer.uri) || layer.uri,
        }))
      : [];
    const stickers = Array.isArray(parsedComposition?.stickers) ? parsedComposition.stickers : [];
    const selectedStickers = stickers.map((s: any) => normalizeUrl(s.url) || s.url).filter(Boolean);
    const stickerTransforms = stickers.reduce((acc: any, s: any) => {
      const key = normalizeUrl(s.url) || s.url;
      if (key) acc[key] = s.transform || { x: 0, y: 0, scale: 1, rotation: 0, flipX: false };
      return acc;
    }, {});

    return {
      backgroundUri,
      borderUri,
      companyLogoUri: compLogo,
      fighterLayers: layers,
      selectedStickers,
      stickerTransforms
    };
  }, [parsedComposition]);

  // Modal states
  const [confirmModal, setConfirmModal] = useState<{
    visible: boolean;
    title: string;
    message: string;
    confirmText: string;
    confirmColor: string;
    onConfirm: () => void;
  }>({
    visible: false,
    title: '',
    message: '',
    confirmText: 'Confirmar',
    confirmColor: COLORS.primary,
    onConfirm: () => {},
  });

  const [messageModal, setMessageModal] = useState<{
    visible: boolean;
    type: 'success' | 'error';
    message: string;
  }>({
    visible: false,
    type: 'success',
    message: '',
  });

  useEffect(() => {
    loadPeleadores();
  }, []);

  useEffect(() => {
    loadClubs();
  }, []);

  const loadPeleadores = async () => {
    try {
      setLoading(true);
      const data = await AdminService.getPeleadores(activeFilter);
      setPeleadores(data.peleadores || []);
    } catch (error) {
      console.error('Error loading fighters:', error);
      setMessageModal({
        visible: true,
        type: 'error',
        message: 'No se pudieron cargar los peleadores',
      });
      setPeleadores([]);
    } finally {
      setLoading(false);
    }
  };

  const searchBase = useMemo(() => {
    if (!searchQuery.trim()) return peleadores;
    const query = searchQuery.toLowerCase();
    return peleadores.filter((p) =>
      p.nombre.toLowerCase().includes(query) ||
      (p.apellidos && p.apellidos.toLowerCase().includes(query)) ||
      p.email.toLowerCase().includes(query) ||
      p.documento_identidad.includes(query) ||
      (p.apodo && p.apodo.toLowerCase().includes(query)) ||
      (p.club_nombre && p.club_nombre.toLowerCase().includes(query))
    );
  }, [searchQuery, peleadores]);

  const displayList = useMemo(() => {
    if (activeFilter === 'todos') return searchBase;
    return searchBase.filter((p) => p.estado_inscripcion === activeFilter);
  }, [searchBase, activeFilter]);


  const loadClubs = async () => {
    try {
      const data = await AdminService.getAllClubs();
      setClubs(data.clubs || []);
    } catch (error) {
      console.error('Error loading clubs:', error);
    }
  };

  const openDetailModal = (peleador: Peleador) => {
    setSelectedPeleador(peleador);
    setCardImageError(false); // Reset error state
    setDetailModalVisible(true);

    // Cargar branding para tarjeta
    AdminService.getActiveLogos()
      .then((data) => {
        if (data?.success && data?.logos?.card?.url) {
          setCompanyLogoUri(data.logos.card.url);
        }
      })
      .catch((e) => console.log('Error fetching branding', e));
  };

  const handleShareCard = async () => {
    if (!selectedPeleador?.card_url) {
      setMessageModal({
        visible: true,
        type: 'error',
        message: 'Este peleador no tiene una tarjeta para compartir',
      });
      return;
    }

    try {
      if (Platform.OS === 'web') {
        setMessageModal({
          visible: true,
          type: 'error',
          message: 'La función de compartir no está disponible en web',
        });
        return;
      }

      console.log('📥 Descargando tarjeta para compartir...');
      const filename = `fighter_card_${selectedPeleador.id}_${Date.now()}.png`;
      const downloadPath = `${FileSystem.cacheDirectory}${filename}`;
      const imageUrl = `${Config.BASE_URL}/${selectedPeleador.card_url}`;

      const downloadResult = await FileSystem.downloadAsync(imageUrl, downloadPath);

      if (await Sharing.isAvailableAsync()) {
        await Sharing.shareAsync(downloadResult.uri);
      } else {
        setMessageModal({
          visible: true,
          type: 'error',
          message: 'Compartir no está disponible en este dispositivo',
        });
      }
    } catch (error) {
      console.error('Error sharing card:', error);
      setMessageModal({
        visible: true,
        type: 'error',
        message: 'No se pudo compartir la tarjeta',
      });
    }
  };

  const openEditModal = (peleador: Peleador) => {
    setEditingPeleador(peleador);
    setEditForm({
      nombre: peleador.nombre || '',
      apodo: peleador.apodo || '',
      email: peleador.email || '',
      telefono: peleador.telefono || '',
      documento_identidad: peleador.documento_identidad || '',
      peso_actual: peleador.peso_actual?.toString() || '',
      altura: peleador.altura?.toString() || '',
      genero: peleador.genero || 'masculino',
      estilo: peleador.estilo || '',
      victorias: peleador.victorias?.toString() || '0',
      derrotas: peleador.derrotas?.toString() || '0',
      empates: peleador.empates?.toString() || '0',
      club_id: peleador.club_id?.toString() || '',
      experiencia_anos: peleador.experiencia_anos?.toString() || '0',
    });
    setShowClubPicker(false);
    setShowGeneroPicker(false);
    setShowEstiloPicker(false);
    setEditModalVisible(true);
  };

  const handleSaveEdit = async () => {
    if (!editingPeleador) return;

    setSavingEdit(true);
    try {
      const payload: Record<string, any> = {
        nombre: editForm.nombre,
        apodo: editForm.apodo,
        email: editForm.email,
        telefono: editForm.telefono,
        documento_identidad: editForm.documento_identidad,
        peso_actual: parseFloat(editForm.peso_actual) || 0,
        altura: parseFloat(editForm.altura) || 0,
        genero: editForm.genero,
        estilo: editForm.estilo || null,
        victorias: parseInt(editForm.victorias) || 0,
        derrotas: parseInt(editForm.derrotas) || 0,
        empates: parseInt(editForm.empates) || 0,
        club_id: editForm.club_id ? parseInt(editForm.club_id) : null,
        experiencia_anos: parseInt(editForm.experiencia_anos) || 0,
      };

      const result = await AdminService.editPeleador(editingPeleador.id, payload);

      if (result.success) {
        setEditModalVisible(false);
        setEditingPeleador(null);
        setMessageModal({
          visible: true,
          type: 'success',
          message: 'Peleador actualizado correctamente',
        });
        await loadPeleadores();
      }
    } catch (error: any) {
      const msg = error.response?.data?.message || 'No se pudo actualizar el peleador';
      setMessageModal({
        visible: true,
        type: 'error',
        message: msg,
      });
    } finally {
      setSavingEdit(false);
    }
  };

  const toggleExpanded = (id: number) => {
    LayoutAnimation.configureNext(LayoutAnimation.Presets.easeInEaseOut);
    setExpandedIds(prev => {
      const next = new Set(prev);
      if (next.has(id)) next.delete(id);
      else next.add(id);
      return next;
    });
  };

  const handleWhatsApp = (telefono: string) => {
    const cleanPhone = telefono.replace(/[^\d]/g, '');
    const url = `https://wa.me/51${cleanPhone}`;
    Linking.openURL(url).catch(() => {
      setMessageModal({
        visible: true,
        type: 'error',
        message: 'No se pudo abrir WhatsApp. Verifica que el número sea válido.',
      });
    });
  };

  const handleApproval = async (peleadorId: number, estado_inscripcion: 'aprobado' | 'rechazado') => {
    const notas = notasMap[peleadorId] || '';
    const confirmMessage =
      estado_inscripcion === 'aprobado'
        ? '¿Estás seguro de aprobar este peleador?'
        : '¿Estás seguro de rechazar este peleador?';

    const ejecutarCambio = async () => {
      try {
        setProcessingId(peleadorId);
        await AdminService.cambiarEstadoPeleador(peleadorId, estado_inscripcion, notas);
        setMessageModal({
          visible: true,
          type: 'success',
          message: `Peleador ${estado_inscripcion === 'aprobado' ? 'aprobado' : 'rechazado'} correctamente`,
        });
        await loadPeleadores();
      } catch (error: any) {
        const errorMessage = error.response?.data?.message || 'No se pudo procesar la solicitud';
        setMessageModal({
          visible: true,
          type: 'error',
          message: errorMessage,
        });
      } finally {
        setProcessingId(null);
      }
    };

    setConfirmModal({
      visible: true,
      title: 'Confirmar acción',
      message: confirmMessage,
      confirmText: 'Confirmar',
      confirmColor: estado_inscripcion === 'aprobado' ? COLORS.primary : COLORS.error,
      onConfirm: ejecutarCambio,
    });
  };

  const handleDelete = async (peleadorId: number, peleadorNombre: string) => {
    const confirmMessage = `ADVERTENCIA: Esta acción es IRREVERSIBLE.\n\n¿Estás seguro de eliminar a "${peleadorNombre}"?\n\nSe eliminarán:\n• Todos sus datos personales\n• Sus tarjetas y fotos\n• Sus inscripciones a eventos\n• Su cuenta de usuario`;

    const ejecutar = async () => {
      try {
        setProcessingId(peleadorId);
        await AdminService.deletePeleador(peleadorId);
        setMessageModal({
          visible: true,
          type: 'success',
          message: `Peleador "${peleadorNombre}" eliminado exitosamente`,
        });
        await loadPeleadores();
      } catch (error: any) {
        const errorMessage = error.response?.data?.message || 'No se pudo eliminar el peleador';
        setMessageModal({
          visible: true,
          type: 'error',
          message: errorMessage,
        });
      } finally {
        setProcessingId(null);
      }
    };

    setConfirmModal({
      visible: true,
      title: 'ELIMINAR PELEADOR',
      message: confirmMessage,
      confirmText: 'Eliminar',
      confirmColor: COLORS.error,
      onConfirm: ejecutar,
    });
  };

  const getInitials = (nombre: string) => {
    const parts = nombre.trim().split(' ');
    if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
    return nombre.substring(0, 2).toUpperCase();
  };

  const formatDate = (dateStr: string) => {
    if (!dateStr) return '--';
    const d = new Date(dateStr);
    return d.toLocaleDateString('es-PE', { day: '2-digit', month: 'short', year: 'numeric' });
  };

  const FilterPill = ({ type, label, icon }: { type: FilterType; label: string; icon?: keyof typeof Ionicons.glyphMap }) => {
    const isActive = activeFilter === type;
    const isIconOnly = type !== 'todos';
    const iconColor = isActive
      ? (type === 'todos' ? COLORS.text.inverse : COLORS.primary)
      : (type === 'pendiente' ? ESTADO_CONFIG.pendiente.color
        : type === 'aprobado' ? ESTADO_CONFIG.aprobado.color
        : type === 'rechazado' ? ESTADO_CONFIG.rechazado.color
        : COLORS.text.tertiary);

    const iconSize = isIconOnly ? 40 : 18;

    return (
      <TouchableOpacity
        style={[
          styles.filterButton,
          isIconOnly ? styles.filterButtonIconOnly : styles.filterButton,
          type === 'todos' && styles.filterButtonTodos,
          !isIconOnly && isActive && styles.filterButtonActive,
        ]}
        onPress={() => setActiveFilter(type)}
      >
        {icon && <Ionicons name={icon} size={iconSize} color={iconColor} />}
        {!isIconOnly && (
          <Text style={[styles.filterButtonText, isActive && styles.filterButtonTextActive]}>{label}</Text>
        )}
      </TouchableOpacity>
    );
  };

  const renderCard = (peleador: Peleador) => {
    const estado = ESTADO_CONFIG[peleador.estado_inscripcion] || ESTADO_CONFIG.pendiente;
    const isExpanded = expandedIds.has(peleador.id);
    const isProcessing = processingId === peleador.id;

    return (
      <View key={peleador.id} style={[styles.card, { borderLeftColor: estado.color }]}>
        {/* Top row: Avatar + Info + Status - Clickeable para expandir/colapsar */}
        <TouchableOpacity
          style={styles.cardTop}
          onPress={() => toggleExpanded(peleador.id)}
          activeOpacity={0.7}
        >
          <View style={[styles.avatar, { backgroundColor: `${estado.color}20` }]}>
            <Text style={[styles.avatarText, { color: estado.color }]}>{getInitials(peleador.nombre)}</Text>
          </View>

          <View style={styles.cardInfo}>
            <Text style={styles.fighterName} numberOfLines={1}>{peleador.nombre}</Text>
            {peleador.apodo ? (
              <Text style={styles.fighterNickname} numberOfLines={1}>"{peleador.apodo}"</Text>
            ) : null}
            <View style={styles.clubRow}>
              <Ionicons name="shield-outline" size={12} color="#9CA3AF" />
              <Text style={styles.clubText}>{peleador.club_nombre || 'Sin club'}</Text>
            </View>
          </View>

          <View style={[styles.statusBadge, { backgroundColor: `${estado.color}20` }]}>
            <Ionicons name={estado.icon} size={14} color={estado.color} />
            <Text style={[styles.statusText, { color: estado.color }]}>{estado.label}</Text>
          </View>

          <Ionicons
            name={isExpanded ? "chevron-up" : "chevron-down"}
            size={24}
            color={COLORS.text.tertiary}
            style={{ marginLeft: 8 }}
          />
        </TouchableOpacity>

        {/* Contenido expandible */}
        {isExpanded && (
          <>
            {/* Stats Row */}
            <View style={styles.statsRow}>
              <View style={styles.statItem}>
                <Text style={styles.statValue}>{peleador.peso_actual}</Text>
                <Text style={styles.statLabel}>KG</Text>
              </View>
              <View style={styles.statDivider} />
              <View style={styles.statItem}>
                <Text style={styles.statValue}>{peleador.edad || '--'}</Text>
                <Text style={styles.statLabel}>EDAD</Text>
              </View>
              <View style={styles.statDivider} />
              <View style={styles.statItem}>
                <Text style={styles.statValue}>{peleador.altura}</Text>
                <Text style={styles.statLabel}>MT</Text>
              </View>
              <View style={styles.statDivider} />
              <View style={styles.statItem}>
                <Text style={styles.statRecord}>
                  <Text style={{ color: '#27ae60' }}>{peleador.victorias}</Text>
                  /
                  <Text style={{ color: '#e74c3c' }}>{peleador.derrotas}</Text>
                  /
                  <Text style={{ color: '#f39c12' }}>{peleador.empates}</Text>
                </Text>
                <Text style={styles.statLabel}>V/D/E</Text>
              </View>
            </View>

            {/* Quick Actions Bar */}
            <View style={styles.quickActions}>
              <TouchableOpacity style={styles.quickBtn} onPress={() => openDetailModal(peleador)}>
                <Ionicons name="eye-outline" size={24} color={COLORS.primary} />
              </TouchableOpacity>

              <TouchableOpacity style={styles.quickBtn} onPress={() => handleWhatsApp(peleador.telefono)}>
                <Ionicons name="logo-whatsapp" size={24} color="#25D366" />
              </TouchableOpacity>

              <TouchableOpacity
                style={styles.quickBtn}
                onPress={() => {
                  setMessageModal({
                    visible: true,
                    type: 'error',
                    message: 'Función en construcción. Próximamente podrás enviar documentos PDF.',
                  });
                }}
              >
                <Ionicons name="document-text-outline" size={24} color="#9333ea" />
              </TouchableOpacity>

              <TouchableOpacity style={styles.quickBtn} onPress={() => openEditModal(peleador)}>
                <Ionicons name="create-outline" size={24} color={COLORS.info} />
              </TouchableOpacity>

              <TouchableOpacity style={styles.quickBtn} onPress={() => handleDelete(peleador.id, peleador.nombre)} disabled={isProcessing}>
                <Ionicons name="trash-outline" size={24} color={COLORS.error} />
              </TouchableOpacity>
            </View>
          </>
        )}

        {/* Approval Actions (pendiente only) - dentro del expandible */}
        {isExpanded && peleador.estado_inscripcion === 'pendiente' && (
          <View style={styles.approvalSection}>
            <TextInput
              style={styles.notasInput}
              placeholder="Notas del administrador (opcional)..."
              placeholderTextColor={COLORS.text.tertiary}
              value={notasMap[peleador.id] || ''}
              onChangeText={(text) => setNotasMap(prev => ({ ...prev, [peleador.id]: text }))}
              multiline
            />
            <View style={styles.approvalActions}>
              <TouchableOpacity
                style={[styles.approvalBtn, styles.rejectBtn]}
                onPress={() => handleApproval(peleador.id, 'rechazado')}
                disabled={isProcessing}
              >
                {isProcessing ? <ActivityIndicator color="#fff" size="small" /> : (
                  <>
                    <Ionicons name="close" size={18} color="#fff" />
                    <Text style={styles.approvalBtnText}>Rechazar</Text>
                  </>
                )}
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.approvalBtn, styles.approveBtn]}
                onPress={() => handleApproval(peleador.id, 'aprobado')}
                disabled={isProcessing}
              >
                {isProcessing ? <ActivityIndicator color="#fff" size="small" /> : (
                  <>
                    <Ionicons name="checkmark" size={18} color="#fff" />
                    <Text style={styles.approvalBtnText}>Aprobar</Text>
                  </>
                )}
              </TouchableOpacity>
            </View>
          </View>
        )}

        {/* Revert action (rechazado only) - dentro del expandible */}
        {isExpanded && peleador.estado_inscripcion === 'rechazado' && (
          <TouchableOpacity
            style={[styles.approvalBtn, styles.revertBtn]}
            onPress={() => handleApproval(peleador.id, 'aprobado')}
            disabled={isProcessing}
          >
            {isProcessing ? <ActivityIndicator color="#fff" size="small" /> : (
              <>
                <Ionicons name="refresh" size={18} color="#fff" />
                <Text style={styles.approvalBtnText}>Revertir y Aprobar</Text>
              </>
            )}
          </TouchableOpacity>
        )}
      </View>
    );
  };

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color={COLORS.primary} />
        <Text style={styles.loadingText}>Cargando peleadores...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      {/* Buscador */}
      <View style={styles.searchRow}>
        <View style={styles.searchContainer}>
          <Ionicons name="search" size={20} color={COLORS.text.tertiary} />
          <TextInput
            style={styles.searchInput}
            placeholder="Nombre, DNI, email, club..."
            placeholderTextColor={COLORS.text.tertiary}
            value={searchQuery}
            onChangeText={setSearchQuery}
          />
          {searchQuery.length > 0 && (
            <TouchableOpacity onPress={() => setSearchQuery('')}>
              <Ionicons name="close-circle" size={20} color={COLORS.text.tertiary} />
            </TouchableOpacity>
          )}
        </View>
      </View>

      {/* Chips + contador */}
      <View style={styles.filtersRow}>
        <View style={styles.filtersGroup}>
          <FilterPill type="todos" label="Todos" icon="list" />
          <FilterPill type="pendiente" label="Pendientes" icon="time" />
          <FilterPill type="aprobado" label="Aprobados" icon="checkmark-circle" />
          <FilterPill type="rechazado" label="Rechazados" icon="close-circle" />
        </View>
        <View style={styles.countBadge}>
          <Text style={styles.countText}>{displayList.length}</Text>
        </View>
      </View>

      {displayList.length === 0 ? (
        <View style={styles.emptyContainer}>
          <Ionicons name="people-outline" size={64} color={COLORS.text.tertiary} />
          <Text style={styles.emptyText}>
            {searchQuery ? 'No se encontraron resultados' : `No hay peleadores ${activeFilter === 'todos' ? '' : activeFilter + 's'}`}
          </Text>
          <TouchableOpacity style={styles.refreshButton} onPress={loadPeleadores}>
            <Ionicons name="refresh" size={18} color={COLORS.text.inverse} />
            <Text style={styles.refreshButtonText}>Recargar</Text>
          </TouchableOpacity>
        </View>
      ) : (
        <ScrollView contentContainerStyle={{ paddingBottom: 100, paddingTop: SPACING.sm }}>
          {displayList.map(renderCard)}
        </ScrollView>
      )}

      {/* Edit Fighter Modal */}
      <Modal
        visible={editModalVisible}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setEditModalVisible(false)}
      >
        <KeyboardAvoidingView
          style={styles.modalOverlay}
          behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        >
          <View style={styles.modalContainer}>
            {/* Modal Header */}
            <View style={styles.modalHeader}>
              <View style={{ flexDirection: 'row', alignItems: 'center', gap: 10 }}>
                <Ionicons name="create" size={22} color={COLORS.primary} />
                <Text style={styles.modalTitle}>Editar Peleador</Text>
              </View>
              <TouchableOpacity onPress={() => setEditModalVisible(false)} style={styles.modalCloseBtn}>
                <Ionicons name="close" size={24} color={COLORS.text.secondary} />
              </TouchableOpacity>
            </View>

            <ScrollView style={styles.modalBody} showsVerticalScrollIndicator={false}>
              {/* Datos Personales */}
              <Text style={styles.sectionLabel}>DATOS PERSONALES</Text>

              <View style={styles.fieldRow}>
                <View style={styles.fieldFull}>
                  <Text style={styles.fieldLabel}>Nombre completo</Text>
                  <TextInput
                    style={styles.fieldInput}
                    value={editForm.nombre}
                    onChangeText={(v) => setEditForm(p => ({ ...p, nombre: v }))}
                    placeholderTextColor={COLORS.text.tertiary}
                  />
                </View>
              </View>

              <View style={styles.fieldRow}>
                <View style={styles.fieldHalf}>
                  <Text style={styles.fieldLabel}>Apodo</Text>
                  <TextInput
                    style={styles.fieldInput}
                    value={editForm.apodo}
                    onChangeText={(v) => setEditForm(p => ({ ...p, apodo: v }))}
                    placeholderTextColor={COLORS.text.tertiary}
                  />
                </View>
                <View style={styles.fieldHalf}>
                  <Text style={styles.fieldLabel}>DNI</Text>
                  <TextInput
                    style={styles.fieldInput}
                    value={editForm.documento_identidad}
                    onChangeText={(v) => setEditForm(p => ({ ...p, documento_identidad: v }))}
                    placeholderTextColor={COLORS.text.tertiary}
                  />
                </View>
              </View>

              <View style={styles.fieldRow}>
                <View style={styles.fieldHalf}>
                  <Text style={styles.fieldLabel}>Email</Text>
                  <TextInput
                    style={styles.fieldInput}
                    value={editForm.email}
                    onChangeText={(v) => setEditForm(p => ({ ...p, email: v }))}
                    keyboardType="email-address"
                    placeholderTextColor={COLORS.text.tertiary}
                  />
                </View>
                <View style={styles.fieldHalf}>
                  <Text style={styles.fieldLabel}>Teléfono</Text>
                  <TextInput
                    style={styles.fieldInput}
                    value={editForm.telefono}
                    onChangeText={(v) => setEditForm(p => ({ ...p, telefono: v }))}
                    keyboardType="phone-pad"
                    placeholderTextColor={COLORS.text.tertiary}
                  />
                </View>
              </View>

              {/* Datos del Peleador */}
              <Text style={styles.sectionLabel}>DATOS DEL PELEADOR</Text>

              <View style={styles.fieldRow}>
                <View style={styles.fieldHalf}>
                  <Text style={styles.fieldLabel}>Peso (kg)</Text>
                  <TextInput
                    style={styles.fieldInput}
                    value={editForm.peso_actual}
                    onChangeText={(v) => setEditForm(p => ({ ...p, peso_actual: v }))}
                    keyboardType="decimal-pad"
                    placeholderTextColor={COLORS.text.tertiary}
                  />
                </View>
                <View style={styles.fieldHalf}>
                  <Text style={styles.fieldLabel}>Altura (m)</Text>
                  <TextInput
                    style={styles.fieldInput}
                    value={editForm.altura}
                    onChangeText={(v) => setEditForm(p => ({ ...p, altura: v }))}
                    keyboardType="decimal-pad"
                    placeholderTextColor={COLORS.text.tertiary}
                  />
                </View>
              </View>

              <View style={styles.fieldRow}>
                <View style={styles.fieldHalf}>
                  <Text style={styles.fieldLabel}>Género</Text>
                  <TouchableOpacity
                    style={styles.pickerButton}
                    onPress={() => setShowGeneroPicker(!showGeneroPicker)}
                  >
                    <Text style={styles.pickerText}>
                      {editForm.genero === 'masculino' ? 'Masculino' : 'Femenino'}
                    </Text>
                    <Ionicons name={showGeneroPicker ? 'chevron-up' : 'chevron-down'} size={18} color={COLORS.text.tertiary} />
                  </TouchableOpacity>
                  {showGeneroPicker && (
                    <View style={styles.pickerDropdown}>
                      {['masculino', 'femenino'].map(g => (
                        <TouchableOpacity
                          key={g}
                          style={[styles.pickerOption, editForm.genero === g && styles.pickerOptionActive]}
                          onPress={() => { setEditForm(p => ({ ...p, genero: g })); setShowGeneroPicker(false); }}
                        >
                          <Text style={[styles.pickerOptionText, editForm.genero === g && { color: COLORS.primary }]}>
                            {g === 'masculino' ? 'Masculino' : 'Femenino'}
                          </Text>
                        </TouchableOpacity>
                      ))}
                    </View>
                  )}
                </View>
                <View style={styles.fieldHalf}>
                  <Text style={styles.fieldLabel}>Estilo</Text>
                  <TouchableOpacity
                    style={styles.pickerButton}
                    onPress={() => setShowEstiloPicker(!showEstiloPicker)}
                  >
                    <Text style={styles.pickerText}>
                      {editForm.estilo ? editForm.estilo.charAt(0).toUpperCase() + editForm.estilo.slice(1) : 'Sin estilo'}
                    </Text>
                    <Ionicons name={showEstiloPicker ? 'chevron-up' : 'chevron-down'} size={18} color={COLORS.text.tertiary} />
                  </TouchableOpacity>
                  {showEstiloPicker && (
                    <View style={styles.pickerDropdown}>
                      {['fajador', 'estilista', 'mixto'].map(e => (
                        <TouchableOpacity
                          key={e}
                          style={[styles.pickerOption, editForm.estilo === e && styles.pickerOptionActive]}
                          onPress={() => { setEditForm(p => ({ ...p, estilo: e })); setShowEstiloPicker(false); }}
                        >
                          <Text style={[styles.pickerOptionText, editForm.estilo === e && { color: COLORS.primary }]}>
                            {e.charAt(0).toUpperCase() + e.slice(1)}
                          </Text>
                        </TouchableOpacity>
                      ))}
                    </View>
                  )}
                </View>
              </View>

              <View style={styles.fieldRow}>
                <View style={styles.fieldHalf}>
                  <Text style={styles.fieldLabel}>Exp. (años)</Text>
                  <TextInput
                    style={styles.fieldInput}
                    value={editForm.experiencia_anos}
                    onChangeText={(v) => setEditForm(p => ({ ...p, experiencia_anos: v }))}
                    keyboardType="number-pad"
                    placeholderTextColor={COLORS.text.tertiary}
                  />
                </View>
                <View style={styles.fieldHalf}>
                  <Text style={styles.fieldLabel}>Club</Text>
                  <TouchableOpacity
                    style={styles.pickerButton}
                    onPress={() => setShowClubPicker(!showClubPicker)}
                  >
                    <Text style={styles.pickerText} numberOfLines={1}>
                      {editForm.club_id
                        ? clubs.find(c => c.id.toString() === editForm.club_id)?.nombre || 'Club ID: ' + editForm.club_id
                        : 'Sin club'}
                    </Text>
                    <Ionicons name={showClubPicker ? 'chevron-up' : 'chevron-down'} size={18} color={COLORS.text.tertiary} />
                  </TouchableOpacity>
                  {showClubPicker && (
                    <View style={styles.pickerDropdown}>
                      <TouchableOpacity
                        style={[styles.pickerOption, !editForm.club_id && styles.pickerOptionActive]}
                        onPress={() => { setEditForm(p => ({ ...p, club_id: '' })); setShowClubPicker(false); }}
                      >
                        <Text style={[styles.pickerOptionText, !editForm.club_id && { color: COLORS.primary }]}>Sin club</Text>
                      </TouchableOpacity>
                      {clubs.map(c => (
                        <TouchableOpacity
                          key={c.id}
                          style={[styles.pickerOption, editForm.club_id === c.id.toString() && styles.pickerOptionActive]}
                          onPress={() => { setEditForm(p => ({ ...p, club_id: c.id.toString() })); setShowClubPicker(false); }}
                        >
                          <Text style={[styles.pickerOptionText, editForm.club_id === c.id.toString() && { color: COLORS.primary }]}>
                            {c.nombre}
                          </Text>
                        </TouchableOpacity>
                      ))}
                    </View>
                  )}
                </View>
              </View>

              {/* Record */}
              <Text style={styles.sectionLabel}>RÉCORD</Text>

              <View style={styles.fieldRow}>
                <View style={styles.fieldThird}>
                  <Text style={[styles.fieldLabel, { color: '#27ae60' }]}>Victorias</Text>
                  <TextInput
                    style={styles.fieldInput}
                    value={editForm.victorias}
                    onChangeText={(v) => setEditForm(p => ({ ...p, victorias: v }))}
                    keyboardType="number-pad"
                    placeholderTextColor={COLORS.text.tertiary}
                  />
                </View>
                <View style={styles.fieldThird}>
                  <Text style={[styles.fieldLabel, { color: '#e74c3c' }]}>Derrotas</Text>
                  <TextInput
                    style={styles.fieldInput}
                    value={editForm.derrotas}
                    onChangeText={(v) => setEditForm(p => ({ ...p, derrotas: v }))}
                    keyboardType="number-pad"
                    placeholderTextColor={COLORS.text.tertiary}
                  />
                </View>
                <View style={styles.fieldThird}>
                  <Text style={[styles.fieldLabel, { color: '#f39c12' }]}>Empates</Text>
                  <TextInput
                    style={styles.fieldInput}
                    value={editForm.empates}
                    onChangeText={(v) => setEditForm(p => ({ ...p, empates: v }))}
                    keyboardType="number-pad"
                    placeholderTextColor={COLORS.text.tertiary}
                  />
                </View>
              </View>

              <View style={{ height: 20 }} />
            </ScrollView>

            {/* Modal Footer */}
            <View style={styles.modalFooter}>
              <TouchableOpacity
                style={styles.cancelBtn}
                onPress={() => setEditModalVisible(false)}
              >
                <Text style={styles.cancelBtnText}>Cancelar</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.saveBtn, savingEdit && { opacity: 0.7 }]}
                onPress={handleSaveEdit}
                disabled={savingEdit}
              >
                {savingEdit ? (
                  <ActivityIndicator color={COLORS.text.inverse} size="small" />
                ) : (
                  <>
                    <Ionicons name="checkmark" size={20} color={COLORS.text.inverse} />
                    <Text style={styles.saveBtnText}>Guardar</Text>
                  </>
                )}
              </TouchableOpacity>
            </View>
          </View>
        </KeyboardAvoidingView>
      </Modal>

      {/* Detail Modal */}
      <Modal
        visible={detailModalVisible}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setDetailModalVisible(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            {/* Modal Header */}
            <View style={styles.modalHeader}>
              <View style={{ flexDirection: 'row', alignItems: 'center', gap: 10 }}>
                <Ionicons name="information-circle" size={22} color={COLORS.primary} />
                <Text style={styles.modalTitle}>Detalles del Peleador</Text>
              </View>
              <TouchableOpacity onPress={() => setDetailModalVisible(false)} style={styles.modalCloseBtn}>
                <Ionicons name="close" size={24} color={COLORS.text.secondary} />
              </TouchableOpacity>
            </View>

            <ScrollView style={styles.modalBody} showsVerticalScrollIndicator={false}>
              {selectedPeleador && (
                <>
                  {/* Datos Personales */}
                  <Text style={styles.sectionLabel}>DATOS PERSONALES</Text>

                  <View style={styles.fieldRow}>
                    <View style={styles.fieldFull}>
                      <View style={styles.detailLabelRow}>
                        <Ionicons name="person" size={16} color={COLORS.primary} />
                        <Text style={styles.detailLabel}>Nombre completo</Text>
                      </View>
                      <View style={styles.detailInput}>
                        <Text style={styles.detailInputText}>
                          {`${selectedPeleador.nombre} ${selectedPeleador.apellidos || ''}`.trim()}
                        </Text>
                      </View>
                    </View>
                  </View>

                  <View style={styles.fieldRow}>
                    <View style={styles.fieldHalf}>
                      <View style={styles.detailLabelRow}>
                        <Ionicons name="star" size={16} color={COLORS.primary} />
                        <Text style={styles.detailLabel}>Apodo</Text>
                      </View>
                      <View style={styles.detailInput}>
                        <Text style={styles.detailInputText}>
                          {selectedPeleador.apodo ? `"${selectedPeleador.apodo}"` : 'Sin apodo'}
                        </Text>
                      </View>
                    </View>
                    <View style={styles.fieldHalf}>
                      <View style={styles.detailLabelRow}>
                        <Ionicons name="card" size={16} color={COLORS.primary} />
                        <Text style={styles.detailLabel}>DNI</Text>
                      </View>
                      <View style={styles.detailInput}>
                        <Text style={styles.detailInputText}>{selectedPeleador.documento_identidad}</Text>
                      </View>
                    </View>
                  </View>

                  <View style={styles.fieldRow}>
                    <View style={styles.fieldHalf}>
                      <View style={styles.detailLabelRow}>
                        <Ionicons name="call" size={16} color={COLORS.primary} />
                        <Text style={styles.detailLabel}>Teléfono</Text>
                      </View>
                      <View style={styles.detailInput}>
                        <Text style={styles.detailInputText}>{selectedPeleador.telefono}</Text>
                      </View>
                    </View>
                    <View style={styles.fieldHalf}>
                      <View style={styles.detailLabelRow}>
                        <Ionicons name="calendar" size={16} color={COLORS.primary} />
                        <Text style={styles.detailLabel}>Fecha de nacimiento</Text>
                      </View>
                      <View style={styles.detailInput}>
                        <Text style={styles.detailInputText}>{formatDate(selectedPeleador.fecha_nacimiento)}</Text>
                      </View>
                    </View>
                  </View>

                  <View style={styles.fieldRow}>
                    <View style={styles.fieldFull}>
                      <View style={styles.detailLabelRow}>
                        <Ionicons name="mail" size={16} color={COLORS.primary} />
                        <Text style={styles.detailLabel}>Email</Text>
                      </View>
                      <View style={styles.detailInput}>
                        <Text style={styles.detailInputText}>{selectedPeleador.email}</Text>
                      </View>
                    </View>
                  </View>

                  {/* Datos del Peleador */}
                  <Text style={styles.sectionLabel}>DATOS DEL PELEADOR</Text>

                  <View style={styles.fieldRow}>
                    <View style={styles.fieldHalf}>
                      <View style={styles.detailLabelRow}>
                        <Ionicons name="barbell" size={18} color={COLORS.primary} />
                        <Text style={styles.detailLabel}>Peso</Text>
                      </View>
                      <View style={styles.detailInput}>
                        <Text style={styles.detailInputText}>{selectedPeleador.peso_actual} kg</Text>
                      </View>
                    </View>
                    <View style={styles.fieldHalf}>
                      <View style={styles.detailLabelRow}>
                        <Ionicons name="resize" size={18} color={COLORS.primary} />
                        <Text style={styles.detailLabel}>Altura</Text>
                      </View>
                      <View style={styles.detailInput}>
                        <Text style={styles.detailInputText}>{selectedPeleador.altura} m</Text>
                      </View>
                    </View>
                  </View>

                  <View style={styles.fieldRow}>
                    <View style={styles.fieldHalf}>
                      <View style={styles.detailLabelRow}>
                        <Ionicons name="flame" size={18} color={COLORS.primary} />
                        <Text style={styles.detailLabel}>Estilo</Text>
                      </View>
                      <View style={styles.detailInput}>
                        <Text style={styles.detailInputText}>
                          {selectedPeleador.estilo ? selectedPeleador.estilo.charAt(0).toUpperCase() + selectedPeleador.estilo.slice(1) : 'Sin estilo'}
                        </Text>
                      </View>
                    </View>
                    <View style={styles.fieldHalf}>
                      <View style={styles.detailLabelRow}>
                        <Ionicons name="time" size={18} color={COLORS.primary} />
                        <Text style={styles.detailLabel}>Experiencia</Text>
                      </View>
                      <View style={styles.detailInput}>
                        <Text style={styles.detailInputText}>{selectedPeleador.experiencia_anos} años</Text>
                      </View>
                    </View>
                  </View>

                  <View style={styles.fieldRow}>
                    <View style={styles.fieldHalf}>
                      <View style={styles.detailLabelRow}>
                        <Ionicons name="person" size={18} color={COLORS.primary} />
                        <Text style={styles.detailLabel}>Género</Text>
                      </View>
                      <View style={styles.detailInput}>
                        <Text style={styles.detailInputText}>
                          {selectedPeleador.genero === 'masculino' ? 'Masculino' : 'Femenino'}
                        </Text>
                      </View>
                    </View>
                    <View style={styles.fieldHalf}>
                      <View style={styles.detailLabelRow}>
                        <Ionicons name="calendar" size={18} color={COLORS.primary} />
                        <Text style={styles.detailLabel}>Edad</Text>
                      </View>
                      <View style={styles.detailInput}>
                        <Text style={styles.detailInputText}>
                          {selectedPeleador.edad ?? '--'}
                        </Text>
                      </View>
                    </View>
                  </View>

                  <View style={styles.fieldRow}>
                    <View style={styles.fieldFull}>
                      <View style={styles.detailLabelRow}>
                        <Ionicons name="shield" size={18} color={COLORS.primary} />
                        <Text style={styles.detailLabel}>Club</Text>
                      </View>
                      <View style={styles.detailInput}>
                        <Text style={styles.detailInputText}>{selectedPeleador.club_nombre || 'Sin club'}</Text>
                      </View>
                    </View>
                  </View>

                  {/* Récord */}
                  <Text style={styles.sectionLabel}>RÉCORD</Text>

                  <View style={styles.recordGrid}>
                    <View style={[styles.recordItem, { borderColor: '#27ae60' }]}>
                      <View style={[styles.recordIconWrap, { backgroundColor: '#27ae6020' }]}>
                        <Ionicons name="trophy" size={32} color="#27ae60" />
                      </View>
                      <Text style={[styles.recordValue, { color: '#27ae60' }]}>{selectedPeleador.victorias}</Text>
                      <Text style={[styles.recordLabel, { color: '#27ae60' }]}>Victorias</Text>
                    </View>
                    <View style={[styles.recordItem, { borderColor: '#e74c3c' }]}>
                      <View style={[styles.recordIconWrap, { backgroundColor: '#e74c3c20' }]}>
                        <Ionicons name="close-circle" size={32} color="#e74c3c" />
                      </View>
                      <Text style={[styles.recordValue, { color: '#e74c3c' }]}>{selectedPeleador.derrotas}</Text>
                      <Text style={[styles.recordLabel, { color: '#e74c3c' }]}>Derrotas</Text>
                    </View>
                    <View style={[styles.recordItem, { borderColor: '#f39c12' }]}>
                      <View style={[styles.recordIconWrap, { backgroundColor: '#f39c1220' }]}>
                        <Ionicons name="remove-circle" size={32} color="#f39c12" />
                      </View>
                      <Text style={[styles.recordValue, { color: '#f39c12' }]}>{selectedPeleador.empates}</Text>
                      <Text style={[styles.recordLabel, { color: '#f39c12' }]}>Empates</Text>
                    </View>
                  </View>

                  {/* Fighter Card Image */}
                  <View style={styles.cardImageSection}>
                    <Text style={styles.sectionLabel}>TARJETA DEL PELEADOR</Text>
                    {selectedPeleador.card_url && !cardImageError ? (
                      <View style={styles.bakedCardWrapper}>
                        <View style={[styles.cardSourceBadge, styles.cardSourceBadgeBaked]}>
                          <Text style={styles.cardSourceBadgeText}>B</Text>
                        </View>
                        <ImageBackground
                          source={{ uri: `${Config.BASE_URL}/${selectedPeleador.card_url}` }}
                          style={styles.bakedImage}
                          imageStyle={{ borderRadius: 12 }}
                          resizeMode="contain"
                          onError={(e) => {
                            console.log('❌ Error cargando tarjeta:', e.nativeEvent.error);
                            setCardImageError(true);
                          }}
                        />
                        <TouchableOpacity
                          style={styles.shareCardOverlay}
                          onPress={handleShareCard}
                        >
                          <View style={styles.shareCardButton}>
                            <Ionicons name="share-social" size={20} color="#fff" />
                            <Text style={styles.shareCardText}>Compartir</Text>
                          </View>
                        </TouchableOpacity>
                      </View>
                    ) : (
                      <View style={styles.cardFallbackWrapper}>
                        <View style={[styles.cardSourceBadge, styles.cardSourceBadgeFallback]}>
                          <Text style={styles.cardSourceBadgeText}>F</Text>
                        </View>
                        <FighterCard
                          fighter={{
                            nombre: selectedPeleador.nombre,
                            apellidos: selectedPeleador.apellidos,
                            apodo: selectedPeleador.apodo,
                            peso: selectedPeleador.peso_actual?.toString(),
                            genero: selectedPeleador.genero,
                            photoUri: selectedPeleador.foto_perfil ? `${Config.BASE_URL}/${selectedPeleador.foto_perfil}` : null,
                            edad: selectedPeleador.edad !== undefined && selectedPeleador.edad !== null
                              ? String(selectedPeleador.edad)
                              : undefined,
                            altura: selectedPeleador.altura ? String(selectedPeleador.altura) : undefined,
                            clubName: selectedPeleador.club_nombre || undefined,
                            record: `${selectedPeleador.victorias}-${selectedPeleador.derrotas}-${selectedPeleador.empates}`,
                          }}
                          variant="large"
                          backgroundUri={compositionAssets?.backgroundUri || undefined}
                          borderUri={compositionAssets?.borderUri || undefined}
                          fighterLayers={compositionAssets?.fighterLayers || []}
                          selectedStickers={compositionAssets?.selectedStickers || []}
                          stickerTransforms={compositionAssets?.stickerTransforms || {}}
                          companyLogoUri={compositionAssets?.companyLogoUri || companyLogoUri}
                          onShare={handleShareCard}
                        />
                      </View>
                    )}

                    <View style={styles.cardFallbackWrapper}>
                      <FighterCard
                        fighter={{
                          nombre: selectedPeleador.nombre,
                          apellidos: selectedPeleador.apellidos,
                          apodo: selectedPeleador.apodo,
                          peso: selectedPeleador.peso_actual?.toString(),
                          genero: selectedPeleador.genero,
                          photoUri: selectedPeleador.foto_perfil ? `${Config.BASE_URL}/${selectedPeleador.foto_perfil}` : null,
                          edad: selectedPeleador.edad !== undefined && selectedPeleador.edad !== null
                            ? String(selectedPeleador.edad)
                            : undefined,
                          altura: selectedPeleador.altura ? String(selectedPeleador.altura) : undefined,
                          clubName: selectedPeleador.club_nombre || undefined,
                          record: `${selectedPeleador.victorias}-${selectedPeleador.derrotas}-${selectedPeleador.empates}`,
                        }}
                        variant="large"
                        backgroundUri={compositionAssets?.backgroundUri || undefined}
                        borderUri={compositionAssets?.borderUri || undefined}
                        fighterLayers={compositionAssets?.fighterLayers || []}
                        selectedStickers={compositionAssets?.selectedStickers || []}
                        stickerTransforms={compositionAssets?.stickerTransforms || {}}
                        companyLogoUri={compositionAssets?.companyLogoUri || companyLogoUri}
                        onShare={handleShareCard}
                      />
                    </View>
                  </View>

                  <View style={{ height: 20 }} />
                </>
              )}
            </ScrollView>

            {/* Modal Footer */}
            <View style={styles.modalFooter}>
              <TouchableOpacity
                style={[styles.saveBtn, { flex: 1 }]}
                onPress={() => setDetailModalVisible(false)}
              >
                <Ionicons name="checkmark" size={20} color={COLORS.text.inverse} />
                <Text style={styles.saveBtnText}>Cerrar</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>

      {/* Confirm Modal */}
      <Modal
        visible={confirmModal.visible}
        animationType="fade"
        transparent={true}
        onRequestClose={() => setConfirmModal(prev => ({ ...prev, visible: false }))}
      >
        <View style={styles.confirmModalOverlay}>
          <View style={styles.confirmModalContainer}>
            <View style={styles.confirmModalHeader}>
              <Ionicons
                name="alert-circle"
                size={28}
                color={confirmModal.confirmColor}
              />
              <Text style={styles.confirmModalTitle}>{confirmModal.title}</Text>
            </View>

            <Text style={styles.confirmModalMessage}>{confirmModal.message}</Text>

            <View style={styles.confirmModalActions}>
              <TouchableOpacity
                style={styles.confirmModalCancelBtn}
                onPress={() => setConfirmModal(prev => ({ ...prev, visible: false }))}
              >
                <Text style={styles.confirmModalCancelText}>Cancelar</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[styles.confirmModalConfirmBtn, { backgroundColor: confirmModal.confirmColor }]}
                onPress={() => {
                  setConfirmModal(prev => ({ ...prev, visible: false }));
                  confirmModal.onConfirm();
                }}
              >
                <Text style={styles.confirmModalConfirmText}>{confirmModal.confirmText}</Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>

      {/* Message Modal */}
      <Modal
        visible={messageModal.visible}
        animationType="fade"
        transparent={true}
        onRequestClose={() => setMessageModal(prev => ({ ...prev, visible: false }))}
      >
        <View style={styles.confirmModalOverlay}>
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
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: COLORS.background,
  },
  loadingText: {
    color: COLORS.text.secondary,
    marginTop: SPACING.sm,
    fontSize: TYPOGRAPHY.fontSize.md,
  },
  // Search
  searchRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginHorizontal: SPACING.md,
    marginTop: SPACING.md,
    marginBottom: SPACING.xs,
    gap: SPACING.sm,
  },
  searchContainer: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.surface,
    paddingHorizontal: SPACING.md,
    borderRadius: BORDER_RADIUS.lg,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
    height: 48,
    gap: SPACING.sm,
  },
  searchInput: {
    flex: 1,
    color: COLORS.text.primary,
    fontSize: TYPOGRAPHY.fontSize.md,
  },
  countBadge: {
    backgroundColor: COLORS.primary,
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 10,
    minWidth: 30,
    alignItems: 'center',
    justifyContent: 'center',
  },
  countText: {
    fontSize: 16,
    fontWeight: "600",
    color: COLORS.text.inverse,
  },
  // Filters
  filtersRow: {
    flexDirection: 'row',
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.sm,
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  filtersGroup: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.sm,
    flexShrink: 1,
  },
  filterButton: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 2,
    paddingHorizontal: 3,
    borderRadius: BORDER_RADIUS.full,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
    backgroundColor: COLORS.surface,
    gap: 3,
    position: 'relative',
  },
  filterButtonTodos: {
    paddingVertical: 4,
  },
  filterButtonIconOnly: {
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 0,
    paddingVertical: 0,
    borderWidth: 0,
    backgroundColor: 'transparent',
  },
  filterButtonActive: {
    backgroundColor: COLORS.primary,
    borderColor: COLORS.primary,
  },
  filterButtonText: {
    fontSize: 16,
    color: COLORS.text.tertiary,
    fontWeight: '600',
  },
  filterButtonTextActive: {
    color: COLORS.text.inverse,
  },
  // Empty
  emptyContainer: {
    alignItems: 'center',
    marginTop: SPACING.xxl,
    paddingHorizontal: SPACING.lg,
  },
  emptyText: {
    color: COLORS.text.secondary,
    fontSize: TYPOGRAPHY.fontSize.lg,
    textAlign: 'center',
    marginTop: SPACING.md,
    marginBottom: SPACING.lg,
  },
  refreshButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.primary,
    paddingHorizontal: SPACING.lg,
    paddingVertical: SPACING.sm,
    borderRadius: BORDER_RADIUS.lg,
    gap: SPACING.sm,
  },
  refreshButtonText: {
    color: COLORS.text.inverse,
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: '700',
  },
  // Card
  card: {
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.lg,
    marginHorizontal: SPACING.md,
    marginBottom: SPACING.md,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
    borderLeftWidth: 4,
    overflow: 'hidden',
  },
  // Card Top
  cardTop: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: SPACING.md,
    gap: SPACING.md,
  },
  avatar: {
    width: 48,
    height: 48,
    borderRadius: 24,
    justifyContent: 'center',
    alignItems: 'center',
  },
  avatarText: {
    fontSize: TYPOGRAPHY.fontSize.lg,
    fontWeight: '800',
  },
  cardInfo: {
    flex: 1,
  },
  fighterName: {
    fontSize: TYPOGRAPHY.fontSize.lg,
    fontWeight: '700',
    color: COLORS.text.primary,
  },
  fighterNickname: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.primary,
    fontStyle: 'italic',
  },
  dateText: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    color: COLORS.text.tertiary,
    marginTop: 2,
  },
  clubRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 2,
    gap: 4,
  },
  clubText: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    color: COLORS.text.tertiary,
  },
  statusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: BORDER_RADIUS.full,
    gap: 4,
  },
  statusText: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    fontWeight: '700',
  },
  // Stats Row
  statsRow: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.background,
    marginHorizontal: SPACING.md,
    borderRadius: BORDER_RADIUS.md,
    paddingVertical: SPACING.sm,
  },
  statItem: {
    flex: 1,
    alignItems: 'center',
  },
  statValue: {
    fontSize: TYPOGRAPHY.fontSize.lg,
    fontWeight: '700',
    color: COLORS.text.primary,
  },
  statRecord: {
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: '700',
  },
  statLabel: {
    fontSize: 9,
    fontWeight: '600',
    color: COLORS.text.tertiary,
    letterSpacing: 1,
    marginTop: 2,
  },
  statDivider: {
    width: 1,
    height: 24,
    backgroundColor: COLORS.border.primary,
  },
  // Quick Actions
  quickActions: {
    flexDirection: 'row',
    borderTopWidth: 1,
    borderTopColor: COLORS.border.primary,
    marginTop: SPACING.md,
  },
  quickBtn: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: SPACING.sm + 2,
    gap: 6,
  },
  quickBtnText: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    fontWeight: '600',
    color: COLORS.primary,
  },
  // Expanded Section
  expandedSection: {
    paddingHorizontal: SPACING.md,
    paddingBottom: SPACING.md,
    gap: SPACING.sm,
  },
  detailRow: {
    flexDirection: 'row',
    gap: SPACING.md,
  },
  detailItem: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: SPACING.sm,
    backgroundColor: COLORS.background,
    padding: SPACING.sm,
    borderRadius: BORDER_RADIUS.md,
  },

  // Approval Section
  approvalSection: {
    paddingHorizontal: SPACING.md,
    paddingBottom: SPACING.md,
  },
  notasInput: {
    backgroundColor: COLORS.background,
    borderRadius: BORDER_RADIUS.md,
    padding: SPACING.sm,
    color: COLORS.text.primary,
    fontSize: TYPOGRAPHY.fontSize.sm,
    marginBottom: SPACING.sm,
    minHeight: 50,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
  },
  approvalActions: {
    flexDirection: 'row',
    gap: SPACING.sm,
  },
  approvalBtn: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 10,
    borderRadius: BORDER_RADIUS.md,
    gap: 6,
  },
  approveBtn: {
    backgroundColor: '#27ae60',
  },
  rejectBtn: {
    backgroundColor: '#e74c3c',
  },
  revertBtn: {
    backgroundColor: '#2980b9',
    marginHorizontal: SPACING.md,
    marginBottom: SPACING.md,
  },
  approvalBtnText: {
    color: '#fff',
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: '700',
  },
  // Modal
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.7)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  modalContainer: {
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.xl,
    width: '94%',
    maxWidth: 500,
    maxHeight: '90%',
    borderWidth: 1,
    borderColor: COLORS.border.primary,
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: SPACING.lg,
    paddingVertical: SPACING.md,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border.primary,
  },
  modalTitle: {
    fontSize: TYPOGRAPHY.fontSize.xl,
    fontWeight: '700',
    color: COLORS.text.primary,
  },
  modalCloseBtn: {
    padding: 4,
  },
  modalBody: {
    paddingHorizontal: SPACING.lg,
  },
  sectionLabel: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    fontWeight: '700',
    color: COLORS.primary,
    letterSpacing: 1.5,
    marginTop: SPACING.lg,
    marginBottom: SPACING.sm,
  },
  fieldRow: {
    flexDirection: 'row',
    gap: SPACING.sm,
    marginBottom: SPACING.sm,
  },
  fieldFull: {
    flex: 1,
  },
  fieldHalf: {
    flex: 1,
  },
  fieldThird: {
    flex: 1,
  },
  fieldLabel: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    color: COLORS.text.tertiary,
    marginBottom: 4,
    fontWeight: '600',
  },
  fieldInput: {
    backgroundColor: COLORS.background,
    borderRadius: BORDER_RADIUS.md,
    paddingHorizontal: SPACING.sm + 2,
    paddingVertical: Platform.OS === 'web' ? 10 : 8,
    color: COLORS.text.primary,
    fontSize: TYPOGRAPHY.fontSize.md,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
  },
  detailInput: {
    backgroundColor: COLORS.background,
    borderRadius: BORDER_RADIUS.md,
    paddingHorizontal: SPACING.sm + 2,
    paddingVertical: Platform.OS === 'web' ? 10 : 8,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
  },
  detailInputText: {
    color: COLORS.text.primary,
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: '600',
  },
  detailLabelRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.xs,
    marginBottom: 4,
  },
  detailLabel: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.text.tertiary,
    fontWeight: '700',
  },

  pickerButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: COLORS.background,
    borderRadius: BORDER_RADIUS.md,
    paddingHorizontal: SPACING.sm + 2,
    paddingVertical: Platform.OS === 'web' ? 10 : 8,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
  },
  pickerText: {
    color: COLORS.text.primary,
    fontSize: TYPOGRAPHY.fontSize.md,
    flex: 1,
  },
  pickerDropdown: {
    backgroundColor: COLORS.background,
    borderRadius: BORDER_RADIUS.md,
    marginTop: 4,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
    overflow: 'hidden',
  },
  pickerOption: {
    paddingVertical: 10,
    paddingHorizontal: SPACING.sm + 2,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border.primary,
  },
  pickerOptionActive: {
    backgroundColor: 'rgba(255, 215, 0, 0.08)',
  },
  pickerOptionText: {
    color: COLORS.text.primary,
    fontSize: TYPOGRAPHY.fontSize.sm,
  },
  modalFooter: {
    flexDirection: 'row',
    gap: SPACING.sm,
    paddingHorizontal: SPACING.lg,
    paddingVertical: SPACING.md,
    borderTopWidth: 1,
    borderTopColor: COLORS.border.primary,
  },
  cancelBtn: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 12,
    borderRadius: BORDER_RADIUS.md,
    borderWidth: 1,
    borderColor: COLORS.border.light,
  },
  cancelBtnText: {
    color: COLORS.text.secondary,
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: '600',
  },
  saveBtn: {
    flex: 2,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 12,
    borderRadius: BORDER_RADIUS.md,
    backgroundColor: COLORS.primary,
    gap: 6,
  },
  saveBtnText: {
    color: COLORS.text.inverse,
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: '700',
  },
  // Confirm Modal
  confirmModalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.75)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: SPACING.lg,
  },
  confirmModalContainer: {
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.xl,
    width: '100%',
    maxWidth: 400,
    padding: SPACING.xl,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
    ...SHADOWS.lg,
  },
  confirmModalHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: SPACING.md,
    gap: SPACING.sm,
  },
  confirmModalTitle: {
    fontSize: TYPOGRAPHY.fontSize.xl,
    fontWeight: '700',
    color: COLORS.text.primary,
    flex: 1,
  },
  confirmModalMessage: {
    fontSize: TYPOGRAPHY.fontSize.md,
    color: COLORS.text.secondary,
    lineHeight: 22,
    marginBottom: SPACING.xl,
  },
  confirmModalActions: {
    flexDirection: 'row',
    gap: SPACING.sm,
  },
  confirmModalCancelBtn: {
    flex: 1,
    paddingVertical: 12,
    borderRadius: BORDER_RADIUS.md,
    borderWidth: 1,
    borderColor: COLORS.border.light,
    alignItems: 'center',
    justifyContent: 'center',
  },
  confirmModalCancelText: {
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: '600',
    color: COLORS.text.secondary,
  },
  confirmModalConfirmBtn: {
    flex: 1,
    paddingVertical: 12,
    borderRadius: BORDER_RADIUS.md,
    alignItems: 'center',
    justifyContent: 'center',
  },
  confirmModalConfirmText: {
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: '700',
    color: '#fff',
  },
  // Message Modal
  messageModalContainer: {
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.xl,
    width: '100%',
    maxWidth: 360,
    padding: SPACING.xl,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: COLORS.border.primary,
    ...SHADOWS.lg,
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
  // Detail Modal
  cardImageSection: {
    marginBottom: SPACING.lg,
  },
  cardFallbackWrapper: {
    position: 'relative',
    alignItems: 'center',
    justifyContent: 'center',
  },
  bakedCardWrapper: {
    width: '100%',
    aspectRatio: 1.9,
    backgroundColor: COLORS.background,
    borderRadius: 12,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: COLORS.border.primary,
    ...SHADOWS.md,
  },
  bakedImage: {
    width: '100%',
    height: '100%',
  },
  shareCardOverlay: {
    position: 'absolute',
    top: SPACING.md,
    right: SPACING.md,
  },
  shareCardButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(0,0,0,0.8)',
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.sm,
    borderRadius: BORDER_RADIUS.full,
    gap: SPACING.xs,
    ...SHADOWS.md,
  },
  shareCardText: {
    color: '#fff',
    fontSize: TYPOGRAPHY.fontSize.sm,
    fontWeight: '600',
  },
  cardSourceBadge: {
    position: 'absolute',
    top: SPACING.md,
    left: SPACING.md,
    width: 28,
    height: 28,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
    zIndex: 3,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.35)',
    ...SHADOWS.sm,
  },
  cardSourceBadgeBaked: {
    backgroundColor: 'rgba(37, 99, 235, 0.9)',
  },
  cardSourceBadgeFallback: {
    backgroundColor: 'rgba(249, 115, 22, 0.9)',
  },
  cardSourceBadgeText: {
    color: '#fff',
    fontSize: TYPOGRAPHY.fontSize.sm,
    fontWeight: '800',
    lineHeight: 16,
  },
  cardImagePlaceholder: {
    fontSize: TYPOGRAPHY.fontSize.md,
    color: COLORS.text.tertiary,
    marginTop: SPACING.sm,
    textAlign: 'center',
  },
  cardImageNote: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.text.tertiary,
    marginTop: SPACING.xs,
    fontStyle: 'italic',
    textAlign: 'center',
  },
  detailInfoRow: {
    marginBottom: SPACING.sm,
  },
  detailInfoItem: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    backgroundColor: COLORS.background,
    borderRadius: BORDER_RADIUS.md,
    padding: SPACING.md,
    gap: SPACING.sm,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
  },
  detailInfoLabel: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    color: COLORS.text.tertiary,
    marginBottom: 2,
    fontWeight: '600',
  },
  detailInfoValue: {
    fontSize: TYPOGRAPHY.fontSize.md,
    color: COLORS.text.primary,
    fontWeight: '600',
  },
  detailFieldWithIcon: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    backgroundColor: COLORS.background,
    borderRadius: BORDER_RADIUS.md,
    padding: SPACING.md,
    gap: SPACING.sm,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
  },
  detailValue: {
    fontSize: TYPOGRAPHY.fontSize.md,
    color: COLORS.text.primary,
    fontWeight: '600',
    marginTop: 2,
  },
  recordGrid: {
    flexDirection: 'row',
    gap: SPACING.sm,
    marginTop: SPACING.sm,
    justifyContent: 'flex-end',
    alignSelf: 'flex-end',
  },
  recordItem: {
    flex: 1,
    backgroundColor: COLORS.background,
    borderRadius: BORDER_RADIUS.lg,
    paddingVertical: SPACING.sm,
    paddingHorizontal: SPACING.sm,
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: COLORS.border.primary,
  },
  recordIconWrap: {
    width: 40,
    height: 40,
    borderRadius: 22,
    alignItems: 'center',
    justifyContent: 'center',
  },
  recordValue: {
    fontSize: 32,
    fontWeight: '800',
    marginTop: 4,
  },
  recordLabel: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    color: COLORS.text.secondary,
    fontWeight: '700',
    textTransform: 'uppercase',
    letterSpacing: 1,
    marginTop: 6,
  },
});
