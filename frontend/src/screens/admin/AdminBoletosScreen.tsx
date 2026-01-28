import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  ActivityIndicator,
  Alert,
  TextInput,
  Modal,
  FlatList,
  RefreshControl,
  SafeAreaView,
  StatusBar,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import boletosService from '../../services/boletosService';
import { BoletoVendido, TipoBoleto, ReporteBoletos } from '../../types';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS } from '../../constants/theme';
import { createShadow } from '../../utils/shadows';

// Estilos de fuentes basados en TYPOGRAPHY
const FONTS = {
  h1: { fontSize: TYPOGRAPHY.fontSize.xxxl, fontWeight: TYPOGRAPHY.fontWeight.bold },
  h2: { fontSize: TYPOGRAPHY.fontSize.xxl, fontWeight: TYPOGRAPHY.fontWeight.bold },
  h3: { fontSize: TYPOGRAPHY.fontSize.xl, fontWeight: TYPOGRAPHY.fontWeight.semibold },
  body: { fontSize: TYPOGRAPHY.fontSize.md, fontWeight: TYPOGRAPHY.fontWeight.regular },
  bodySmall: { fontSize: TYPOGRAPHY.fontSize.sm, fontWeight: TYPOGRAPHY.fontWeight.regular },
};

type TabType = 'pendientes' | 'validar' | 'reportes' | 'tipos';

export default function AdminBoletosScreen({ navigation }: any) {
  const [activeTab, setActiveTab] = useState<TabType>('pendientes');
  const [loading, setLoading] = useState(false);
  const [refreshing, setRefreshing] = useState(false);

  const onRefresh = React.useCallback(() => {
    setRefreshing(true);
    loadData().finally(() => setRefreshing(false));
  }, [activeTab]);

  const loadData = async () => {
    // No hace nada aquí, cada tab maneja su propia carga
  };

  useEffect(() => {
    loadData();
  }, [activeTab]);

  const renderContent = () => {
    switch (activeTab) {
      case 'pendientes':
        return <PendientesTab loading={loading} refreshing={refreshing} onRefresh={onRefresh} />;
      case 'validar':
        return <ValidarQRTab />;
      case 'reportes':
        return <ReportesTab loading={loading} refreshing={refreshing} onRefresh={onRefresh} />;
      case 'tipos':
        return <TiposBoletosTab />;
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor={COLORS.background} />

      {/* Header */}
      <View style={styles.header}>
        <View style={styles.headerTop}>
          <TouchableOpacity onPress={() => navigation.navigate('AdminPanel')} style={styles.backButton}>
            <Ionicons name="arrow-back" size={28} color="#fff" />
          </TouchableOpacity>
          <View>
            <Text style={styles.headerTitle}>GESTIÓN DE BOLETOS</Text>
            <Text style={styles.headerSubtitle}>Administración de entradas</Text>
          </View>
        </View>
      </View>

      {/* Tabs */}
      <View style={styles.tabs}>
        <TabButton
          label="Pendientes"
          icon="time-outline"
          active={activeTab === 'pendientes'}
          onPress={() => setActiveTab('pendientes')}
        />
        <TabButton
          label="Validar QR"
          icon="qr-code-outline"
          active={activeTab === 'validar'}
          onPress={() => setActiveTab('validar')}
        />
        <TabButton
          label="Reportes"
          icon="stats-chart-outline"
          active={activeTab === 'reportes'}
          onPress={() => setActiveTab('reportes')}
        />
        <TabButton
          label="Tipos"
          icon="pricetag-outline"
          active={activeTab === 'tipos'}
          onPress={() => setActiveTab('tipos')}
        />
      </View>

      {/* Content */}
      {renderContent()}
    </SafeAreaView>
  );
}

// Tab Button Component
interface TabButtonProps {
  label: string;
  icon: string;
  active: boolean;
  onPress: () => void;
}

function TabButton({ label, icon, active, onPress }: TabButtonProps) {
  return (
    <TouchableOpacity
      style={[styles.tabButton, active && styles.tabButtonActive]}
      onPress={onPress}
    >
      <Ionicons
        name={icon as any}
        size={18}
        color={active ? COLORS.primary : COLORS.text.tertiary}
      />
      <Text style={[styles.tabLabel, active && styles.tabLabelActive]}>{label}</Text>
    </TouchableOpacity>
  );
}

// PENDIENTES TAB
interface PendientesTabProps {
  loading: boolean;
  refreshing: boolean;
  onRefresh: () => void;
}

function PendientesTab({ loading, refreshing, onRefresh }: PendientesTabProps) {
  const [pendientes, setPendientes] = useState<BoletoVendido[]>([]);

  const loadPendientes = async () => {
    try {
      const boletos = await boletosService.getBoletosPendientes();
      setPendientes(boletos || []);
    } catch (error) {
      console.error('Error loading pendientes:', error);
    }
  };

  useEffect(() => {
    loadPendientes();
  }, []);

  const handleValidar = async (boletoId: number, accion: 'aprobar' | 'rechazar') => {
    try {
      const response = await boletosService.validarPago(boletoId, accion);
      if (response.success) {
        Alert.alert('Éxito', response.message);
        loadPendientes();
      } else {
        Alert.alert('Error', response.message);
      }
    } catch (error: any) {
      Alert.alert('Error', error.message || 'Error al validar pago');
    }
  };

  if (loading && !refreshing) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color={COLORS.primary} />
      </View>
    );
  }

  return (
    <ScrollView
      style={styles.content}
      refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
    >
      {pendientes.length === 0 ? (
        <View style={styles.emptyContainer}>
          <Ionicons name="checkmark-circle-outline" size={64} color={COLORS.success} />
          <Text style={styles.emptyText}>No hay pagos pendientes</Text>
        </View>
      ) : (
        pendientes.map((boleto) => (
          <View key={boleto.id} style={styles.boletoCard}>
            <View style={styles.boletoHeader}>
              <Text style={styles.boletoComprador}>{boleto.comprador_nombres_apellidos}</Text>
              <Text style={styles.boletoPrecio}>S/ {boleto.precio_total.toFixed(2)}</Text>
            </View>

            <View style={styles.boletoDetails}>
              <DetailRow icon="card-outline" label="DNI" value={boleto.comprador_dni} />
              <DetailRow icon="call-outline" label="Teléfono" value={boleto.comprador_telefono} />
              <DetailRow icon="pricetag-outline" label="Tipo" value={boleto.tipo_boleto_nombre || ''} />
              <DetailRow icon="albums-outline" label="Cantidad" value={boleto.cantidad.toString()} />
              <DetailRow
                icon="calendar-outline"
                label="Fecha"
                value={new Date(boleto.fecha_compra).toLocaleDateString()}
              />
            </View>

            {boleto.comprobante_pago && (
              <TouchableOpacity style={styles.comprobanteButton}>
                <Ionicons name="image-outline" size={20} color={COLORS.primary} />
                <Text style={styles.comprobanteText}>Ver comprobante</Text>
              </TouchableOpacity>
            )}

            <View style={styles.boletoActions}>
              <TouchableOpacity
                style={[styles.actionButton, styles.rejectButton]}
                onPress={() =>
                  Alert.alert(
                    'Rechazar pago',
                    '¿Estás seguro de rechazar este pago?',
                    [
                      { text: 'Cancelar', style: 'cancel' },
                      {
                        text: 'Rechazar',
                        style: 'destructive',
                        onPress: () => handleValidar(boleto.id, 'rechazar'),
                      },
                    ]
                  )
                }
              >
                <Ionicons name="close-circle" size={20} color="#fff" />
                <Text style={styles.actionButtonText}>Rechazar</Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={[styles.actionButton, styles.approveButton]}
                onPress={() =>
                  Alert.alert(
                    'Aprobar pago',
                    '¿Confirmar que el pago es válido?',
                    [
                      { text: 'Cancelar', style: 'cancel' },
                      {
                        text: 'Aprobar',
                        onPress: () => handleValidar(boleto.id, 'aprobar'),
                      },
                    ]
                  )
                }
              >
                <Ionicons name="checkmark-circle" size={20} color="#fff" />
                <Text style={styles.actionButtonText}>Aprobar</Text>
              </TouchableOpacity>
            </View>
          </View>
        ))
      )}
    </ScrollView>
  );
}

// VALIDAR QR TAB
function ValidarQRTab() {
  const [codigoQR, setCodigoQR] = useState('');
  const [validando, setValidando] = useState(false);
  const [resultado, setResultado] = useState<any>(null);

  const handleValidar = async () => {
    if (!codigoQR.trim()) {
      Alert.alert('Error', 'Ingresa un código QR');
      return;
    }

    setValidando(true);
    setResultado(null);

    try {
      const response = await boletosService.verificarBoleto(codigoQR.trim());
      setResultado(response);

      if (response.valido) {
        Alert.alert('✅ BOLETO VÁLIDO', response.message);
      } else {
        Alert.alert('❌ BOLETO INVÁLIDO', response.message);
      }
    } catch (error: any) {
      Alert.alert('Error', error.message || 'Error al validar boleto');
    } finally {
      setValidando(false);
    }
  };

  return (
    <ScrollView style={styles.content}>
      <View style={styles.qrContainer}>
        <Text style={styles.qrTitle}>Escanear código QR</Text>
        <Text style={styles.qrSubtitle}>
          Ingresa el código del boleto para validar la entrada
        </Text>

        <TextInput
          style={styles.qrInput}
          placeholder="BOX-JD-2026-000001"
          value={codigoQR}
          onChangeText={setCodigoQR}
          autoCapitalize="characters"
          autoCorrect={false}
        />

        <TouchableOpacity
          style={[styles.validateButton, validando && styles.validateButtonDisabled]}
          onPress={handleValidar}
          disabled={validando}
        >
          {validando ? (
            <ActivityIndicator color="#fff" />
          ) : (
            <>
              <Ionicons name="qr-code" size={24} color="#fff" />
              <Text style={styles.validateButtonText}>Validar Boleto</Text>
            </>
          )}
        </TouchableOpacity>

        {resultado && (
          <View
            style={[
              styles.resultadoCard,
              resultado.valido ? styles.resultadoValido : styles.resultadoInvalido,
            ]}
          >
            <Ionicons
              name={resultado.valido ? 'checkmark-circle' : 'close-circle'}
              size={48}
              color={resultado.valido ? COLORS.success : COLORS.error}
            />
            <Text style={styles.resultadoTitle}>{resultado.message}</Text>

            {resultado.valido && resultado.data && (
              <View style={styles.resultadoDetails}>
                <Text style={styles.resultadoLabel}>Comprador:</Text>
                <Text style={styles.resultadoValue}>{resultado.data.comprador}</Text>

                <Text style={styles.resultadoLabel}>DNI:</Text>
                <Text style={styles.resultadoValue}>{resultado.data.dni}</Text>

                <Text style={styles.resultadoLabel}>Tipo de Boleto:</Text>
                <Text style={styles.resultadoValue}>{resultado.data.tipo_boleto}</Text>

                <Text style={styles.resultadoLabel}>Evento:</Text>
                <Text style={styles.resultadoValue}>{resultado.data.evento}</Text>
              </View>
            )}
          </View>
        )}
      </View>
    </ScrollView>
  );
}

// REPORTES TAB
interface ReportesTabProps {
  loading: boolean;
  refreshing: boolean;
  onRefresh: () => void;
}

function ReportesTab({ loading, refreshing, onRefresh }: ReportesTabProps) {
  const [reporte, setReporte] = useState<ReporteBoletos | null>(null);
  const [eventoId, setEventoId] = useState('1'); // Por defecto evento 1

  const loadReportes = async () => {
    try {
      const reporte = await boletosService.getReporte(parseInt(eventoId));
      setReporte(reporte);
    } catch (error) {
      console.error('Error loading reportes:', error);
    }
  };

  useEffect(() => {
    loadReportes();
  }, [eventoId]);

  if (loading && !refreshing) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color={COLORS.primary} />
      </View>
    );
  }

  return (
    <ScrollView
      style={styles.content}
      refreshControl={<RefreshControl refreshing={refreshing} onRefresh={onRefresh} />}
    >
      {reporte && (
        <>
          {/* Totales */}
          <View style={styles.totalesCard}>
            <Text style={styles.totalesTitle}>Resumen General</Text>
            <View style={styles.totalesRow}>
              <View style={styles.totalItem}>
                <Text style={styles.totalLabel}>Total Ventas</Text>
                <Text style={styles.totalValue}>{reporte.totales.total_ventas}</Text>
              </View>
              <View style={styles.totalItem}>
                <Text style={styles.totalLabel}>Total Boletos</Text>
                <Text style={styles.totalValue}>{reporte.totales.total_boletos}</Text>
              </View>
              <View style={styles.totalItem}>
                <Text style={styles.totalLabel}>Ingresos</Text>
                <Text style={[styles.totalValue, styles.totalIngresos]}>
                  S/ {reporte.totales.ingresos_totales.toFixed(2)}
                </Text>
              </View>
            </View>
          </View>

          {/* Por tipo */}
          <Text style={styles.sectionTitle}>Desglose por Tipo</Text>
          {reporte.resumen_por_tipo.map((tipo) => (
            <View key={tipo.tipo_boleto} style={styles.tipoCard}>
              <View style={styles.tipoHeader}>
                <Text style={styles.tipoNombre}>{tipo.tipo_boleto}</Text>
                <Text style={styles.tipoPrecio}>S/ {tipo.precio.toFixed(2)}</Text>
              </View>

              <View style={styles.tipoStats}>
                <StatItem label="Vendidos" value={tipo.total_vendidos.toString()} />
                <StatItem label="Boletos" value={tipo.cantidad_boletos.toString()} />
                <StatItem label="Ingresos" value={`S/ ${tipo.ingresos_total.toFixed(2)}`} />
              </View>

              <View style={styles.tipoEstados}>
                <EstadoChip label="Verificados" count={tipo.verificados} color={COLORS.success} />
                <EstadoChip label="Pendientes" count={tipo.pendientes} color={COLORS.warning} />
                <EstadoChip label="Rechazados" count={tipo.rechazados} color={COLORS.error} />
              </View>
            </View>
          ))}
        </>
      )}
    </ScrollView>
  );
}

// TIPOS DE BOLETOS TAB
function TiposBoletosTab() {
  const [tipos, setTipos] = useState<TipoBoleto[]>([]);
  const [loading, setLoading] = useState(false);
  const [modalVisible, setModalVisible] = useState(false);
  const [editingTipo, setEditingTipo] = useState<TipoBoleto | null>(null);

  const loadTipos = async () => {
    setLoading(true);
    try {
      // Asumiendo que hay un endpoint para obtener tipos por evento
      // Por ahora usamos evento_id = 1
      const eventoId = 1;
      const tipos = await boletosService.getTiposDisponibles(eventoId);
      setTipos(tipos || []);
    } catch (error) {
      console.error('Error loading tipos:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadTipos();
  }, []);

  const handleCrear = () => {
    setEditingTipo(null);
    setModalVisible(true);
  };

  const handleEditar = (tipo: TipoBoleto) => {
    setEditingTipo(tipo);
    setModalVisible(true);
  };

  const handleToggleActivo = async (id: number, isActivo: boolean) => {
    const accion = isActivo ? 'desactivar' : 'activar';
    Alert.alert(
      `${accion.charAt(0).toUpperCase() + accion.slice(1)} tipo de boleto`,
      `¿Estás seguro de ${accion} este tipo de boleto?`,
      [
        { text: 'Cancelar', style: 'cancel' },
        {
          text: accion.charAt(0).toUpperCase() + accion.slice(1),
          style: isActivo ? 'destructive' : 'default',
          onPress: async () => {
            try {
              let response;
              if (isActivo) {
                response = await boletosService.desactivarTipoBoleto(id);
              } else {
                response = await boletosService.activarTipoBoleto(id);
              }

              if (response.success) {
                Alert.alert('Éxito', `Tipo de boleto ${accion}do`);
                loadTipos();
              }
            } catch (error: any) {
              Alert.alert('Error', error.message);
            }
          },
        },
      ]
    );
  };

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color={COLORS.primary} />
      </View>
    );
  }

  return (
    <View style={styles.content}>
      <ScrollView>
        <TouchableOpacity style={styles.createButton} onPress={handleCrear}>
          <LinearGradient
            colors={['#FFFFFF', '#E0E0E0']}
            style={styles.createButtonGradient}
          >
            <Ionicons name="add-circle" size={22} color="#CC0000" />
            <Text style={styles.createButtonText}>CREAR TIPO DE BOLETO</Text>
          </LinearGradient>
        </TouchableOpacity>

        {tipos.map((tipo) => (
          <View key={tipo.id} style={[
            styles.tipoManageCard,
            !tipo.activo && styles.tipoManageCardInactive
          ]}>
            {/* Barra de color lateral */}
            <View style={[styles.tipoColorBar, { backgroundColor: tipo.color_hex }]} />

            {/* Contenido principal */}
            <View style={styles.tipoManageContent}>
              {/* Header con nombre y acciones */}
              <View style={styles.tipoManageHeader}>
                <View style={styles.tipoManageHeaderLeft}>
                  <Ionicons name="ticket" size={24} color={tipo.color_hex} />
                  <View style={{ flex: 1 }}>
                    <Text style={styles.tipoManageNombre}>{tipo.nombre}</Text>
                    <Text style={styles.tipoManagePrecio}>S/ {tipo.precio.toFixed(2)}</Text>
                  </View>
                </View>

                {/* Botones de acción rápida */}
                <View style={styles.quickActions}>
                  {!tipo.activo && (
                    <View style={styles.inactiveBadgeSmall}>
                      <Text style={styles.inactiveBadgeTextSmall}>OFF</Text>
                    </View>
                  )}
                  <TouchableOpacity
                    style={styles.iconButton}
                    onPress={() => handleEditar(tipo)}
                  >
                    <View style={styles.iconButtonCircle}>
                      <Ionicons name="create-outline" size={20} color={COLORS.primary} />
                    </View>
                  </TouchableOpacity>
                  <TouchableOpacity
                    style={styles.iconButton}
                    onPress={() => handleToggleActivo(tipo.id, tipo.activo)}
                  >
                    <View style={[
                      styles.iconButtonCircle,
                      tipo.activo ? styles.iconButtonActive : styles.iconButtonInactive
                    ]}>
                      <Ionicons
                        name={tipo.activo ? "power" : "power-outline"}
                        size={20}
                        color={tipo.activo ? COLORS.success : COLORS.error}
                      />
                    </View>
                  </TouchableOpacity>
                </View>
              </View>

              {/* Info de stock */}
              <View style={styles.tipoStockContainer}>
                <View style={styles.tipoStockInfo}>
                  <Ionicons name="people" size={16} color={COLORS.text.secondary} />
                  <Text style={styles.tipoManageStock}>
                    {tipo.cantidad_disponible} / {tipo.cantidad_total} disponibles
                  </Text>
                </View>
                <View style={styles.progressBarContainer}>
                  <View
                    style={[
                      styles.progressBar,
                      {
                        width: `${(tipo.cantidad_vendida / tipo.cantidad_total) * 100}%`,
                        backgroundColor: tipo.color_hex
                      }
                    ]}
                  />
                </View>
              </View>

              {/* Descripción */}
              {tipo.descripcion && (
                <View style={styles.tipoDescContainer}>
                  <Ionicons name="information-circle-outline" size={16} color={COLORS.text.tertiary} />
                  <Text style={styles.tipoManageDesc}>{tipo.descripcion}</Text>
                </View>
              )}
            </View>
          </View>
        ))}
      </ScrollView>

      <TipoBoletoModal
        visible={modalVisible}
        tipo={editingTipo}
        onClose={() => {
          setModalVisible(false);
          setEditingTipo(null);
        }}
        onSuccess={() => {
          setModalVisible(false);
          setEditingTipo(null);
          loadTipos();
        }}
      />
    </View>
  );
}

// Modal para crear/editar tipo de boleto
interface TipoBoletoModalProps {
  visible: boolean;
  tipo: TipoBoleto | null;
  onClose: () => void;
  onSuccess: () => void;
}

function TipoBoletoModal({ visible, tipo, onClose, onSuccess }: TipoBoletoModalProps) {
  const [formData, setFormData] = useState({
    nombre: '',
    precio: '',
    cantidad_total: '',
    color_hex: '#e74c3c',
    descripcion: '',
    orden: '0',
  });
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    if (tipo) {
      setFormData({
        nombre: tipo.nombre || '',
        precio: tipo.precio?.toString() || '',
        cantidad_total: tipo.cantidad_total?.toString() || '',
        color_hex: tipo.color_hex || '#e74c3c',
        descripcion: tipo.descripcion || '',
        orden: tipo.orden?.toString() || '0',
      });
    } else {
      setFormData({
        nombre: '',
        precio: '',
        cantidad_total: '',
        color_hex: '#e74c3c',
        descripcion: '',
        orden: '0',
      });
    }
  }, [tipo, visible]);

  const handleSave = async () => {
    if (!formData.nombre || !formData.precio || !formData.cantidad_total) {
      Alert.alert('Error', 'Completa todos los campos requeridos');
      return;
    }

    setSaving(true);
    try {
      const data = {
        evento_id: 1, // Por defecto evento 1
        nombre: formData.nombre,
        precio: parseFloat(formData.precio),
        cantidad_total: parseInt(formData.cantidad_total),
        color_hex: formData.color_hex,
        descripcion: formData.descripcion || undefined,
        orden: parseInt(formData.orden),
      };

      let response;
      if (tipo) {
        response = await boletosService.editarTipoBoleto(tipo.id, data);
      } else {
        response = await boletosService.crearTipoBoleto(data);
      }

      if (response.success) {
        Alert.alert('Éxito', tipo ? 'Tipo actualizado' : 'Tipo creado');
        onSuccess();
      }
    } catch (error: any) {
      Alert.alert('Error', error.message);
    } finally {
      setSaving(false);
    }
  };

  const colores = [
    { name: 'Rojo', hex: '#e74c3c' },
    { name: 'Azul', hex: '#3498db' },
    { name: 'Verde', hex: '#2ecc71' },
    { name: 'Dorado', hex: '#f39c12' },
    { name: 'Morado', hex: '#9b59b6' },
    { name: 'Turquesa', hex: '#1abc9c' },
  ];

  return (
    <Modal visible={visible} animationType="slide" transparent>
      <View style={styles.modalOverlay}>
        <View style={styles.modalContent}>
          {/* Header con título */}
          <View style={styles.modalHeader}>
            <View style={styles.modalHeaderTitleContainer}>
              <Ionicons
                name={tipo ? "create" : "add-circle"}
                size={24}
                color={COLORS.primary}
              />
              <Text style={styles.modalTitle}>
                {tipo ? 'Editar Tipo de Boleto' : 'Crear Tipo de Boleto'}
              </Text>
            </View>
            <TouchableOpacity onPress={onClose} style={styles.closeButton}>
              <Ionicons name="close-circle" size={28} color={COLORS.text.tertiary} />
            </TouchableOpacity>
          </View>

          {/* Formulario */}
          <ScrollView style={styles.modalForm} showsVerticalScrollIndicator={false}>
            {/* Nombre */}
            <View style={styles.formField}>
              <Text style={styles.formLabel}>Nombre del Boleto *</Text>
              <TextInput
                style={styles.formInput}
                value={formData.nombre}
                onChangeText={(text) => setFormData({ ...formData, nombre: text })}
                placeholder="Ej: General, VIP, Premium"
                placeholderTextColor={COLORS.text.tertiary}
              />
            </View>

            {/* Precio y Cantidad en fila */}
            <View style={styles.formRow}>
              <View style={[styles.formField, { flex: 1 }]}>
                <Text style={styles.formLabel}>Precio (S/) *</Text>
                <TextInput
                  style={styles.formInput}
                  value={formData.precio}
                  onChangeText={(text) => setFormData({ ...formData, precio: text })}
                  placeholder="50.00"
                  placeholderTextColor={COLORS.text.tertiary}
                  keyboardType="decimal-pad"
                />
              </View>
              <View style={[styles.formField, { flex: 1 }]}>
                <Text style={styles.formLabel}>Cantidad *</Text>
                <TextInput
                  style={styles.formInput}
                  value={formData.cantidad_total}
                  onChangeText={(text) => setFormData({ ...formData, cantidad_total: text })}
                  placeholder="100"
                  placeholderTextColor={COLORS.text.tertiary}
                  keyboardType="number-pad"
                />
              </View>
            </View>

            {/* Selector de Color */}
            <View style={styles.formField}>
              <Text style={styles.formLabel}>Color del Boleto</Text>
              <View style={styles.colorPickerContainer}>
                {colores.map((color) => (
                  <TouchableOpacity
                    key={color.hex}
                    style={[
                      styles.colorOption,
                      { backgroundColor: color.hex },
                      formData.color_hex === color.hex && styles.colorOptionSelected,
                    ]}
                    onPress={() => setFormData({ ...formData, color_hex: color.hex })}
                  >
                    {formData.color_hex === color.hex && (
                      <Ionicons name="checkmark" size={20} color="#fff" />
                    )}
                  </TouchableOpacity>
                ))}
              </View>
              <TextInput
                style={[styles.formInput, { marginTop: SPACING.sm }]}
                value={formData.color_hex}
                onChangeText={(text) => setFormData({ ...formData, color_hex: text })}
                placeholder="#e74c3c"
                placeholderTextColor={COLORS.text.tertiary}
              />
            </View>

            {/* Descripción */}
            <View style={styles.formField}>
              <Text style={styles.formLabel}>Descripción (Opcional)</Text>
              <TextInput
                style={[styles.formInput, styles.formInputMultiline]}
                value={formData.descripcion}
                onChangeText={(text) => setFormData({ ...formData, descripcion: text })}
                placeholder="Incluye acceso general, sin asiento asignado..."
                placeholderTextColor={COLORS.text.tertiary}
                multiline
                numberOfLines={3}
              />
            </View>

            {/* Orden */}
            <View style={styles.formField}>
              <Text style={styles.formLabel}>Orden de Visualización</Text>
              <TextInput
                style={styles.formInput}
                value={formData.orden}
                onChangeText={(text) => setFormData({ ...formData, orden: text })}
                placeholder="0"
                placeholderTextColor={COLORS.text.tertiary}
                keyboardType="number-pad"
              />
              <Text style={styles.formHint}>Los boletos se ordenarán de menor a mayor</Text>
            </View>
          </ScrollView>

          {/* Botones de acción */}
          <View style={styles.modalActions}>
            <TouchableOpacity
              style={styles.modalCancelButton}
              onPress={onClose}
              disabled={saving}
            >
              <LinearGradient
                colors={['#333333', '#1a1a1a']}
                style={styles.modalButtonGradient}
              >
                <Ionicons name="close-circle" size={18} color={COLORS.text.secondary} />
                <Text style={styles.modalCancelText}>Cancelar</Text>
              </LinearGradient>
            </TouchableOpacity>
            <TouchableOpacity
              style={[styles.modalSaveButton, saving && styles.modalSaveButtonDisabled]}
              onPress={handleSave}
              disabled={saving}
            >
              <LinearGradient
                colors={['#FFFFFF', '#E0E0E0']}
                style={styles.modalButtonGradient}
              >
                {saving ? (
                  <>
                    <ActivityIndicator color="#CC0000" size="small" />
                    <Text style={styles.modalSaveText}>GUARDANDO...</Text>
                  </>
                ) : (
                  <>
                    <Ionicons name="checkmark-circle" size={18} color="#CC0000" />
                    <Text style={styles.modalSaveText}>
                      {tipo ? 'ACTUALIZAR' : 'CREAR BOLETO'}
                    </Text>
                  </>
                )}
              </LinearGradient>
            </TouchableOpacity>
          </View>
        </View>
      </View>
    </Modal>
  );
}

// Helper Components
interface DetailRowProps {
  icon: string;
  label: string;
  value: string;
}

function DetailRow({ icon, label, value }: DetailRowProps) {
  return (
    <View style={styles.detailRow}>
      <Ionicons name={icon as any} size={16} color={COLORS.text.secondary} />
      <Text style={styles.detailLabel}>{label}:</Text>
      <Text style={styles.detailValue}>{value}</Text>
    </View>
  );
}

interface StatItemProps {
  label: string;
  value: string;
}

function StatItem({ label, value }: StatItemProps) {
  return (
    <View style={styles.statItem}>
      <Text style={styles.statLabel}>{label}</Text>
      <Text style={styles.statValue}>{value}</Text>
    </View>
  );
}

interface EstadoChipProps {
  label: string;
  count: number;
  color: string;
}

function EstadoChip({ label, count, color }: EstadoChipProps) {
  return (
    <View style={[styles.estadoChip, { backgroundColor: color + '20' }]}>
      <Text style={[styles.estadoChipText, { color }]}>
        {label}: {count}
      </Text>
    </View>
  );
}


const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  header: {
    padding: SPACING.lg,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border.primary,
  },
  headerTop: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.md,
  },
  backButton: {
    padding: SPACING.xs,
  },
  headerTitle: {
    fontSize: TYPOGRAPHY.fontSize.xl,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.primary,
    letterSpacing: 1,
  },
  headerSubtitle: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.text.secondary,
    marginTop: SPACING.xs,
  },
  tabs: {
    flexDirection: 'row',
    backgroundColor: COLORS.surface,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border.primary,
  },
  tabButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: SPACING.md,
    gap: SPACING.xs,
    borderBottomWidth: 3,
    borderBottomColor: 'transparent',
  },
  tabButtonActive: {
    borderBottomColor: COLORS.primary,
  },
  tabLabel: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    color: COLORS.text.tertiary,
    fontWeight: '500',
  },
  tabLabelActive: {
    color: COLORS.primary,
    fontWeight: '700',
  },
  content: {
    flex: 1,
    padding: SPACING.md,
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  emptyContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: SPACING.xl * 2,
  },
  emptyText: {
    ...FONTS.body,
    color: COLORS.text.secondary,
    marginTop: SPACING.md,
  },
  boletoCard: {
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: SPACING.md,
    marginBottom: SPACING.md,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
  },
  boletoHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: SPACING.sm,
  },
  boletoComprador: {
    ...FONTS.h3,
    flex: 1,
  },
  boletoPrecio: {
    ...FONTS.h3,
    color: COLORS.primary,
  },
  boletoDetails: {
    gap: SPACING.xs,
    marginBottom: SPACING.md,
  },
  detailRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.xs,
  },
  detailLabel: {
    ...FONTS.bodySmall,
    color: COLORS.text.secondary,
  },
  detailValue: {
    ...FONTS.bodySmall,
    color: COLORS.text.primary,
    fontWeight: '500',
  },
  comprobanteButton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.xs,
    padding: SPACING.sm,
    backgroundColor: COLORS.primary + '10',
    borderRadius: 8,
    marginBottom: SPACING.md,
  },
  comprobanteText: {
    ...FONTS.bodySmall,
    color: COLORS.primary,
    fontWeight: '600',
  },
  boletoActions: {
    flexDirection: 'row',
    gap: SPACING.sm,
  },
  actionButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: SPACING.xs,
    padding: SPACING.md,
    borderRadius: 8,
  },
  rejectButton: {
    backgroundColor: COLORS.error,
  },
  approveButton: {
    backgroundColor: COLORS.success,
  },
  actionButtonText: {
    ...FONTS.body,
    color: '#fff',
    fontWeight: '600',
  },
  qrContainer: {
    padding: SPACING.md,
  },
  qrTitle: {
    ...FONTS.h2,
    textAlign: 'center',
    marginBottom: SPACING.xs,
  },
  qrSubtitle: {
    ...FONTS.body,
    color: COLORS.text.secondary,
    textAlign: 'center',
    marginBottom: SPACING.xl,
  },
  qrInput: {
    backgroundColor: '#fff',
    padding: SPACING.md,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
    ...FONTS.body,
    textAlign: 'center',
    marginBottom: SPACING.md,
  },
  validateButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: SPACING.sm,
    backgroundColor: COLORS.primary,
    padding: SPACING.md,
    borderRadius: 8,
  },
  validateButtonDisabled: {
    opacity: 0.6,
  },
  validateButtonText: {
    ...FONTS.body,
    color: '#fff',
    fontWeight: '600',
  },
  resultadoCard: {
    marginTop: SPACING.xl,
    padding: SPACING.lg,
    borderRadius: 12,
    alignItems: 'center',
  },
  resultadoValido: {
    backgroundColor: COLORS.success + '20',
    borderWidth: 2,
    borderColor: COLORS.success,
  },
  resultadoInvalido: {
    backgroundColor: COLORS.error + '20',
    borderWidth: 2,
    borderColor: COLORS.error,
  },
  resultadoTitle: {
    ...FONTS.h2,
    marginTop: SPACING.md,
    textAlign: 'center',
  },
  resultadoDetails: {
    marginTop: SPACING.md,
    width: '100%',
  },
  resultadoLabel: {
    ...FONTS.bodySmall,
    color: COLORS.text.secondary,
    marginTop: SPACING.sm,
  },
  resultadoValue: {
    ...FONTS.body,
    fontWeight: '600',
  },
  totalesCard: {
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: SPACING.md,
    marginBottom: SPACING.md,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
  },
  totalesTitle: {
    ...FONTS.h3,
    marginBottom: SPACING.md,
  },
  totalesRow: {
    flexDirection: 'row',
    justifyContent: 'space-around',
  },
  totalItem: {
    alignItems: 'center',
  },
  totalLabel: {
    ...FONTS.bodySmall,
    color: COLORS.text.secondary,
  },
  totalValue: {
    ...FONTS.h2,
    marginTop: SPACING.xs,
  },
  totalIngresos: {
    color: COLORS.success,
  },
  sectionTitle: {
    ...FONTS.h3,
    marginVertical: SPACING.md,
  },
  tipoCard: {
    backgroundColor: '#fff',
    borderRadius: 12,
    padding: SPACING.md,
    marginBottom: SPACING.md,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
  },
  tipoHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: SPACING.md,
  },
  tipoNombre: {
    ...FONTS.h3,
  },
  tipoPrecio: {
    ...FONTS.h3,
    color: COLORS.primary,
  },
  tipoStats: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    marginBottom: SPACING.md,
  },
  statItem: {
    alignItems: 'center',
  },
  statLabel: {
    ...FONTS.bodySmall,
    color: COLORS.text.secondary,
  },
  statValue: {
    ...FONTS.body,
    fontWeight: '600',
    marginTop: SPACING.xs,
  },
  tipoEstados: {
    flexDirection: 'row',
    gap: SPACING.xs,
  },
  estadoChip: {
    paddingHorizontal: SPACING.sm,
    paddingVertical: SPACING.xs,
    borderRadius: 6,
  },
  estadoChipText: {
    ...FONTS.bodySmall,
    fontWeight: '600',
  },
  createButton: {
    marginBottom: SPACING.md,
  },
  createButtonGradient: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: SPACING.sm,
    paddingVertical: SPACING.md,
    borderRadius: BORDER_RADIUS.full,
  },
  createButtonText: {
    fontSize: 15,
    fontWeight: '900',
    letterSpacing: 1,
    color: '#CC0000',
  },
  tipoManageCard: {
    backgroundColor: COLORS.surface,
    borderRadius: 16,
    marginBottom: SPACING.md,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: COLORS.border.primary,
    flexDirection: 'row',
    ...createShadow('#000', 0, 2, 0.3, 4, 3),
  },
  tipoManageCardInactive: {
    opacity: 0.5,
    borderColor: COLORS.border.light,
  },
  tipoColorBar: {
    width: 8,
  },
  tipoManageContent: {
    flex: 1,
    padding: SPACING.lg,
  },
  tipoManageHeader: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    marginBottom: SPACING.md,
  },
  tipoManageHeaderLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.md,
    flex: 1,
  },
  tipoManageNombre: {
    fontSize: TYPOGRAPHY.fontSize.lg,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
    marginBottom: 2,
  },
  quickActions: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.xs,
  },
  inactiveBadgeSmall: {
    backgroundColor: COLORS.error,
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 12,
  },
  inactiveBadgeTextSmall: {
    fontSize: 10,
    color: '#fff',
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    letterSpacing: 0.5,
  },
  iconButton: {
    padding: 2,
  },
  iconButtonCircle: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: COLORS.surface,
    borderWidth: 2,
    borderColor: COLORS.border.primary,
    justifyContent: 'center',
    alignItems: 'center',
  },
  iconButtonActive: {
    borderColor: COLORS.success,
    backgroundColor: COLORS.success + '15',
  },
  iconButtonInactive: {
    borderColor: COLORS.error,
    backgroundColor: COLORS.error + '15',
  },
  tipoManagePrecio: {
    fontSize: TYPOGRAPHY.fontSize.md,
    color: COLORS.primary,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
  },
  tipoStockContainer: {
    marginBottom: SPACING.md,
  },
  tipoStockInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.xs,
    marginBottom: SPACING.xs,
  },
  tipoManageStock: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.text.secondary,
  },
  progressBarContainer: {
    height: 6,
    backgroundColor: COLORS.border.primary,
    borderRadius: 3,
    overflow: 'hidden',
  },
  progressBar: {
    height: '100%',
    borderRadius: 3,
  },
  tipoDescContainer: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: SPACING.xs,
    marginBottom: SPACING.md,
    paddingTop: SPACING.sm,
    borderTopWidth: 1,
    borderTopColor: COLORS.border.primary,
  },
  tipoManageDesc: {
    flex: 1,
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.text.secondary,
    lineHeight: 18,
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.85)',
    justifyContent: 'flex-end',
  },
  modalContent: {
    backgroundColor: COLORS.background,
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    maxHeight: '95%',
    borderWidth: 1,
    borderColor: COLORS.border.primary,
    ...createShadow(COLORS.primary, 0, -4, 0.3, 8, 8),
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: SPACING.lg,
    borderBottomWidth: 2,
    borderBottomColor: COLORS.primary,
    backgroundColor: COLORS.surface,
  },
  modalHeaderTitleContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.sm,
  },
  closeButton: {
    padding: SPACING.xs,
  },
  modalTitle: {
    fontSize: TYPOGRAPHY.fontSize.xl,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
  },
  modalForm: {
    padding: SPACING.lg,
    backgroundColor: COLORS.background,
  },
  formRow: {
    flexDirection: 'row',
    gap: SPACING.md,
  },
  formField: {
    marginBottom: SPACING.lg,
  },
  formLabel: {
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
    color: COLORS.text.primary,
    marginBottom: SPACING.sm,
  },
  formInput: {
    backgroundColor: COLORS.surface,
    padding: SPACING.md,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
    fontSize: TYPOGRAPHY.fontSize.md,
    color: COLORS.text.primary,
  },
  formInputMultiline: {
    minHeight: 90,
    textAlignVertical: 'top',
    paddingTop: SPACING.md,
  },
  formHint: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.text.tertiary,
    marginTop: SPACING.xs,
    fontStyle: 'italic',
  },
  colorPickerContainer: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: SPACING.md,
    marginBottom: SPACING.sm,
  },
  colorOption: {
    width: 50,
    height: 50,
    borderRadius: 25,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 3,
    borderColor: COLORS.border.primary,
  },
  colorOptionSelected: {
    borderColor: COLORS.primary,
    borderWidth: 4,
    ...createShadow(COLORS.primary, 0, 0, 0.6, 8, 8),
  },
  modalActions: {
    flexDirection: 'row',
    gap: SPACING.md,
    padding: SPACING.lg,
    borderTopWidth: 2,
    borderTopColor: COLORS.border.primary,
    backgroundColor: COLORS.surface,
  },
  modalCancelButton: {
    flex: 1,
  },
  modalSaveButton: {
    flex: 1.5,
  },
  modalButtonGradient: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: SPACING.sm,
    paddingVertical: SPACING.md,
    borderRadius: BORDER_RADIUS.full,
  },
  modalSaveButtonDisabled: {
    opacity: 0.5,
  },
  modalCancelText: {
    fontSize: 15,
    fontWeight: '900',
    letterSpacing: 0.5,
    color: COLORS.text.secondary,
  },
  modalSaveText: {
    fontSize: 15,
    fontWeight: '900',
    letterSpacing: 1,
    color: '#CC0000',
  },
});
