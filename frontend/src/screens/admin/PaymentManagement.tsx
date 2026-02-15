import React, { useState, useEffect } from 'react';
import {
  View,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  ActivityIndicator,
  TextInput,
  Modal,
  Platform,
  Image,
  Linking,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import * as ImagePicker from 'expo-image-picker';
import { AdminService } from '../../services/AdminService';
import { Config } from '../../config/config';

interface Inscripcion {
  id: number;
  peleador_id: number;
  evento_id: number;
  estado_pago: string;
  monto_pagado: string;
  fecha_inscripcion: string;
  fecha_pago: string | null;
  metodo_pago: string | null;
  comprobante_pago: string | null;
  notas_admin: string | null;
  peleador_nombre: string;
  peleador_email: string;
  peleador_telefono: string;
  peleador_apodo: string;
  peleador_dni: string;
  evento_nombre: string;
  evento_fecha: string;
  evento_hora: string;
  precio_evento: string;
  club_nombre: string | null;
}

interface MetodoPago {
  id: number;
  codigo: string;
  nombre: string;
  requiere_comprobante: number;
  activo: number;
  orden: number;
  qr_imagen_url: string | null;
  telefono_receptor: string | null;
  nombre_receptor: string | null;
}

export default function PaymentManagement() {
  const [activeTab, setActiveTab] = useState<'inscripciones' | 'metodos'>('inscripciones');
  const [inscripciones, setInscripciones] = useState<Inscripcion[]>([]);
  const [loading, setLoading] = useState(true);
  const [filtro, setFiltro] = useState<'todas' | 'inscritas' | 'pendientes' | 'pagadas'>('todas');
  const [voucherModal, setVoucherModal] = useState<{ visible: boolean; uri: string | null }>({ visible: false, uri: null });
  const [messageModal, setMessageModal] = useState<{ visible: boolean; type: 'success' | 'error'; message: string }>({ visible: false, type: 'success', message: '' });
  const [processing, setProcessing] = useState(false);
  const [metodosPago, setMetodosPago] = useState<MetodoPago[]>([]);
  const [loadingMetodos, setLoadingMetodos] = useState(false);
  const [showMetodoModal, setShowMetodoModal] = useState(false);
  const [editingMetodo, setEditingMetodo] = useState<MetodoPago | null>(null);
  const [expandedCards, setExpandedCards] = useState<Set<number>>(new Set());
  const [uploadingQR, setUploadingQR] = useState(false);
  const [qrPreview, setQrPreview] = useState<string | null>(null);

  // Helper: convertir path relativo a URL completa
  const getImageUrl = (path: string | null | undefined): string | null => {
    if (!path) return null;
    if (path.startsWith('http')) return path;
    return `${Config.BASE_URL}/${path}`;
  };

  const [metodoForm, setMetodoForm] = useState({
    codigo: '',
    nombre: '',
    requiere_comprobante: 0,
    activo: 1,
    orden: 0,
    qr_imagen_url: '',
    telefono_receptor: '',
    nombre_receptor: '',
  });

  useEffect(() => {
    loadInscripciones();
  }, [filtro]);

  useEffect(() => {
    loadMetodosPago();
  }, []);

  const loadInscripciones = async () => {
    try {
      setLoading(true);
      let data;

      if (filtro === 'inscritas') {
        data = await AdminService.getInscripciones({ estado_pago: 'inscrito' });
      } else if (filtro === 'pendientes') {
        data = await AdminService.getInscripcionesPendientes();
      } else if (filtro === 'pagadas') {
        data = await AdminService.getInscripciones({ estado_pago: 'pagado' });
      } else {
        data = await AdminService.getInscripciones();
      }

      setInscripciones(data.inscripciones || []);
    } catch (error) {
      console.error('Error loading inscriptions:', error);
      setMessageModal({ visible: true, type: 'error', message: 'No se pudieron cargar las inscripciones' });
      setInscripciones([]);
    } finally {
      setLoading(false);
    }
  };

  const loadMetodosPago = async () => {
    try {
      setLoadingMetodos(true);
      const data = await AdminService.getMetodosPago();
      setMetodosPago(data.metodos || []);
    } catch (error) {
      console.error('Error loading payment methods:', error);
      setMetodosPago([]);
    } finally {
      setLoadingMetodos(false);
    }
  };

  const handleConfirmarPago = async (inscripcion: Inscripcion) => {
    try {
      setProcessing(true);

      // Validar monto
      const monto = parseFloat(inscripcion.precio_evento || inscripcion.monto_pagado);
      if (isNaN(monto) || monto <= 0) {
        setMessageModal({ visible: true, type: 'error', message: 'Monto inválido' });
        return;
      }

      // 1. Confirmar pago
      await AdminService.confirmarPago(inscripcion.id, {
        monto_pagado: monto,
        metodo_pago: inscripcion.metodo_pago || 'transferencia',
        comprobante_pago: inscripcion.comprobante_pago || undefined,
        notas_admin: undefined,
      });

      // 2. Aprobar automáticamente al peleador
      await AdminService.cambiarEstadoPeleador(inscripcion.peleador_id, 'aprobado', '');

      setMessageModal({ visible: true, type: 'success', message: '✅ Pago confirmado y peleador aprobado' });
      loadInscripciones();
    } catch (error) {
      setMessageModal({ visible: true, type: 'error', message: 'No se pudo confirmar el pago' });
    } finally {
      setProcessing(false);
    }
  };

  const openMetodoModal = (metodo?: MetodoPago) => {
    if (metodo) {
      setEditingMetodo(metodo);
      setMetodoForm({
        codigo: metodo.codigo,
        nombre: metodo.nombre,
        requiere_comprobante: metodo.requiere_comprobante,
        activo: metodo.activo,
        orden: metodo.orden,
        qr_imagen_url: metodo.qr_imagen_url || '',
        telefono_receptor: metodo.telefono_receptor || '',
        nombre_receptor: metodo.nombre_receptor || '',
      });
      setQrPreview(getImageUrl(metodo.qr_imagen_url) || null);
    } else {
      setEditingMetodo(null);
      setMetodoForm({
        codigo: '',
        nombre: '',
        requiere_comprobante: 0,
        activo: 1,
        orden: 0,
        qr_imagen_url: '',
        telefono_receptor: '',
        nombre_receptor: '',
      });
      setQrPreview(null);
    }
    setShowMetodoModal(true);
  };

  const pickQRImage = async () => {
    try {
      const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
      if (status !== 'granted') {
        setMessageModal({ visible: true, type: 'error', message: 'Se necesita permiso para acceder a tus fotos' });
        return;
      }

      const result = await ImagePicker.launchImageLibraryAsync({
        mediaTypes: ['images'],
        allowsEditing: true,
        aspect: [1, 1],
        quality: 0.8,
      });

      if (!result.canceled && result.assets[0]) {
        setUploadingQR(true);
        const asset = result.assets[0];
        const uri = asset.uri;

        // Establecer preview local inmediatamente
        setQrPreview(uri);

        try {
          // Subir al servidor - Usar global.FormData para React Native
          const formData = new (global as any).FormData();
          const filename = uri.split('/').pop() || 'qr-image.jpg';
          const match = /\.(\w+)$/.exec(filename);
          const type = match ? `image/${match[1]}` : 'image/jpeg';

          if (Platform.OS === 'web') {
            // En web, necesitamos convertir a Blob
            const response = await fetch(uri);
            const blob = await response.blob();
            formData.append('qr_image', blob, filename);
          } else {
            // En mobile (iOS/Android), usar el formato de React Native
            // Normalizar URI para iOS (quitar file:// si está presente)
            const normalizedUri = Platform.OS === 'ios' && uri.startsWith('file://')
              ? uri
              : uri;

            console.log('📱 Preparando upload mobile:', {
              platform: Platform.OS,
              uri: normalizedUri,
              filename,
              type
            });

            formData.append('qr_image', {
              uri: normalizedUri,
              name: filename,
              type,
            } as any);
          }

          console.log('📤 Enviando FormData a servidor...');
          const uploadResponse = await AdminService.uploadQRImage(formData);
          console.log('📥 Respuesta del servidor:', uploadResponse);

          if (uploadResponse.success && uploadResponse.url) {
            // Guardar la URL del servidor
            setMetodoForm({ ...metodoForm, qr_imagen_url: uploadResponse.url });
            setMessageModal({ visible: true, type: 'success', message: '✅ Imagen QR subida correctamente' });
          } else {
            const errorMsg = uploadResponse.message || uploadResponse.debug || 'No se recibió URL del servidor';
            console.error('❌ Upload failed:', uploadResponse);
            throw new Error(typeof errorMsg === 'object' ? JSON.stringify(errorMsg) : errorMsg);
          }

          setUploadingQR(false);
        } catch (uploadError: any) {
          console.error('❌ Error en upload:', uploadError);
          console.error('Error response:', uploadError.response?.data);
          console.error('Error status:', uploadError.response?.status);

          // Intentar extraer el mensaje de error real del servidor
          const serverError = uploadError.response?.data?.message || uploadError.response?.data?.debug;
          const errorMessage = serverError
            ? `Error del servidor: ${typeof serverError === 'object' ? JSON.stringify(serverError) : serverError}`
            : uploadError.message || 'Error desconocido al subir la imagen';

          throw new Error(errorMessage);
        }
      }
    } catch (error: any) {
      console.error('❌ Error uploading QR:', error);
      console.error('Error details:', {
        message: error.message,
        response: error.response?.data,
        status: error.response?.status
      });

      setMessageModal({
        visible: true,
        type: 'error',
        message: error.message || 'No se pudo subir la imagen QR. Revisa los logs para más detalles.'
      });
      setQrPreview(null);
      setUploadingQR(false);
    }
  };

  const toggleCard = (id: number) => {
    const newExpanded = new Set(expandedCards);
    if (newExpanded.has(id)) {
      newExpanded.delete(id);
    } else {
      newExpanded.add(id);
    }
    setExpandedCards(newExpanded);
  };

  const getPaymentMethodStyle = (codigo: string) => {
    const styles: { [key: string]: { color: string; gradient: string[]; icon: string } } = {
      yape: {
        color: '#722C85',
        gradient: ['#722C85', '#8B3A9F'],
        icon: 'phone-portrait'
      },
      plin: {
        color: '#00D39E',
        gradient: ['#00D39E', '#00E8B0'],
        icon: 'phone-portrait'
      },
      efectivo: {
        color: '#27ae60',
        gradient: ['#27ae60', '#2ecc71'],
        icon: 'cash'
      },
      transferencia: {
        color: '#3498db',
        gradient: ['#3498db', '#5DADE2'],
        icon: 'swap-horizontal'
      },
      deposito: {
        color: '#e67e22',
        gradient: ['#e67e22', '#f39c12'],
        icon: 'business'
      },
      tarjeta: {
        color: '#9b59b6',
        gradient: ['#9b59b6', '#AF7AC5'],
        icon: 'card'
      },
    };

    return styles[codigo.toLowerCase()] || {
      color: '#f05d4b',
      gradient: ['#f05d4b', '#e74c3c'],
      icon: 'wallet'
    };
  };

  const submitMetodoPago = async () => {
    if (!metodoForm.codigo.trim() || !metodoForm.nombre.trim()) {
      setMessageModal({ visible: true, type: 'error', message: 'Código y nombre son requeridos' });
      return;
    }

    try {
      setProcessing(true);
      const payload = {
        codigo: metodoForm.codigo.trim().toLowerCase(),
        nombre: metodoForm.nombre.trim(),
        requiere_comprobante: metodoForm.requiere_comprobante,
        activo: metodoForm.activo,
        orden: Number(metodoForm.orden) || 0,
        qr_imagen_url: metodoForm.qr_imagen_url.trim() || null,
        telefono_receptor: metodoForm.telefono_receptor.trim() || null,
        nombre_receptor: metodoForm.nombre_receptor.trim() || null,
      };

      if (editingMetodo) {
        await AdminService.actualizarMetodoPago(editingMetodo.id, payload);
      } else {
        await AdminService.crearMetodoPago(payload);
      }

      setShowMetodoModal(false);
      loadMetodosPago();
    } catch (error) {
      setMessageModal({ visible: true, type: 'error', message: 'No se pudo guardar el método de pago' });
    } finally {
      setProcessing(false);
    }
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-PE', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const getMetodoNombre = (codigo?: string | null) => {
    if (!codigo) return '--';
    const metodo = metodosPago.find((m) => m.codigo === codigo);
    return metodo ? metodo.nombre : codigo;
  };

  const openWhatsApp = (phone: string, name: string) => {
    // Limpiar el número de teléfono (quitar espacios, guiones, etc.)
    const cleanPhone = phone.replace(/[^0-9+]/g, '');

    // Mensaje predeterminado
    const message = `Hola ${name}, te contacto desde la administración del evento de Box TioVe.`;
    const encodedMessage = encodeURIComponent(message);

    // URL para WhatsApp (funciona en web y móvil)
    const whatsappUrl = Platform.OS === 'web'
      ? `https://wa.me/${cleanPhone}?text=${encodedMessage}`
      : `whatsapp://send?phone=${cleanPhone}&text=${encodedMessage}`;

    Linking.openURL(whatsappUrl).catch(() => {
      setMessageModal({ visible: true, type: 'error', message: 'No se pudo abrir WhatsApp. Verifica que esté instalado.' });
    });
  };

  const metodosActivos = metodosPago.filter((m) => m.activo === 1);

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color="#f05d4b" />
        <Text style={styles.loadingText}>Cargando inscripciones...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.hero}>
 
      </View>

      {/* Pestañas */}
      <View style={styles.tabsContainer}>
        <TouchableOpacity
          style={[styles.tab, activeTab === 'inscripciones' && styles.tabActive]}
          onPress={() => setActiveTab('inscripciones')}
        >
          <Ionicons
            name="list-outline"
            size={20}
            color={activeTab === 'inscripciones' ? '#f05d4b' : '#9b9b9b'}
          />
          <Text style={[styles.tabText, activeTab === 'inscripciones' && styles.tabTextActive]}>
            Inscripciones
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          style={[styles.tab, activeTab === 'metodos' && styles.tabActive]}
          onPress={() => setActiveTab('metodos')}
        >
          <Ionicons
            name="wallet-outline"
            size={20}
            color={activeTab === 'metodos' ? '#f05d4b' : '#9b9b9b'}
          />
          <Text style={[styles.tabText, activeTab === 'metodos' && styles.tabTextActive]}>
            Métodos de Pago
          </Text>
        </TouchableOpacity>
      </View>

      <ScrollView style={styles.page} contentContainerStyle={styles.pageContent}>
        {/* Pestaña de Inscripciones */}
        {activeTab === 'inscripciones' && (
          <>
            <View style={styles.statsRow}>
              <TouchableOpacity
                style={[styles.statCard, filtro === 'inscritas' && styles.statCardActive]}
                onPress={() => setFiltro('inscritas')}
                activeOpacity={0.7}
              >
                <Ionicons name="person-add-outline" size={20} color="#3498db" style={styles.statIcon} />
                <Text style={styles.statLabel}>Inscritos</Text>
                <Text style={styles.statValue}>
                  {inscripciones.filter((i) => i.estado_pago === 'inscrito').length}
                </Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={[styles.statCard, filtro === 'pendientes' && styles.statCardActive]}
                onPress={() => setFiltro('pendientes')}
                activeOpacity={0.7}
              >
                <Ionicons name="time-outline" size={20} color="#c97a1b" style={styles.statIcon} />
                <Text style={styles.statLabel}>Pendientes</Text>
                <Text style={styles.statValue}>
                  {inscripciones.filter((i) => i.estado_pago === 'pendiente').length}
                </Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={[styles.statCard, filtro === 'pagadas' && styles.statCardActive]}
                onPress={() => setFiltro('pagadas')}
                activeOpacity={0.7}
              >
                <Ionicons name="checkmark-circle-outline" size={20} color="#1f8f57" style={styles.statIcon} />
                <Text style={styles.statLabel}>Pagados</Text>
                <Text style={styles.statValue}>
                  {inscripciones.filter((i) => i.estado_pago === 'pagado').length}
                </Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={[styles.statCard, filtro === 'todas' && styles.statCardActive]}
                onPress={() => setFiltro('todas')}
                activeOpacity={0.7}
              >
                <Ionicons name="list-outline" size={20} color="#fff" style={styles.statIcon} />
                <Text style={styles.statLabel}>Total</Text>
                <Text style={styles.statValue}>{inscripciones.length}</Text>
              </TouchableOpacity>
            </View>

        {inscripciones.length === 0 ? (
          <View style={styles.emptyContainer}>
            <Text style={styles.emptyText}>No hay inscripciones para mostrar</Text>
          </View>
        ) : (
          inscripciones.map((inscripcion) => (
            <View key={inscripcion.id} style={styles.card}>
              {/* Header con nombre, fecha y estado */}
              <View style={styles.cardHeader}>
                <View style={styles.headerLeft}>
                  <Text style={styles.peleadorName}>{inscripcion.peleador_nombre}</Text>
                  {inscripcion.peleador_apodo && (
                    <Text style={styles.peleadorApodo}>"{inscripcion.peleador_apodo}"</Text>
                  )}
                  {inscripcion.fecha_pago && (
                    <Text style={styles.headerDate}>{formatDate(inscripcion.fecha_pago)}</Text>
                  )}
                </View>
                <View
                  style={[
                    styles.badge,
                    inscripcion.estado_pago === 'pagado' ? styles.badgePaid
                      : inscripcion.estado_pago === 'pendiente' ? styles.badgePending
                      : styles.badgeInscrito,
                  ]}
                >
                  <Text style={styles.badgeText}>
                    {inscripcion.estado_pago === 'pagado' ? 'PAGADO'
                      : inscripcion.estado_pago === 'pendiente' ? 'PENDIENTE'
                      : 'INSCRITO'}
                  </Text>
                </View>
              </View>

              {/* Body con 2 columnas */}
              <View style={styles.cardBodyColumns}>
                {/* Columna izquierda: DNI, Teléfono, Monto */}
                <View style={styles.columnLeft}>
                  <View style={styles.infoRowCompact}>
                    <Ionicons name="card-outline" size={14} color="#7f8c8d" />
                    <Text style={styles.infoLabelCompact}>DNI</Text>
                    <Text style={styles.infoValueCompact}>{inscripcion.peleador_dni}</Text>
                  </View>

                  <TouchableOpacity
                    style={styles.infoRowCompactClickable}
                    onPress={() => openWhatsApp(inscripcion.peleador_telefono, inscripcion.peleador_nombre)}
                    activeOpacity={0.7}
                  >
                    <Ionicons name="logo-whatsapp" size={14} color="#25D366" />
                    <Text style={styles.infoLabelCompact}>Tel</Text>
                    <Text style={styles.infoValueCompact}>{inscripcion.peleador_telefono}</Text>
                  </TouchableOpacity>

                  <View style={styles.infoRowCompact}>
                    <Ionicons name="cash-outline" size={14} color="#27ae60" />
                    <Text style={styles.infoLabelCompact}>Monto</Text>
                    <Text style={styles.montoTextCompact}>S/ {inscripcion.monto_pagado}</Text>
                  </View>
                </View>

                {/* Columna derecha: Comprobante (click para agrandar) */}
                {inscripcion.comprobante_pago && (
                  <TouchableOpacity
                    style={styles.columnRight}
                    activeOpacity={0.7}
                    onPress={() => setVoucherModal({ visible: true, uri: getImageUrl(inscripcion.comprobante_pago) })}
                  >
                    <Text style={styles.comprobanteLabel}>
                      {getMetodoNombre(inscripcion.metodo_pago)}
                    </Text>
                    <View style={styles.comprobanteThumbnailWrapper}>
                      <Image
                        source={{ uri: getImageUrl(inscripcion.comprobante_pago) || '' }}
                        style={styles.comprobanteThumbnail}
                        resizeMode="cover"
                      />
                    </View>
                    <Text style={styles.comprobanteHint}>Tap para ver</Text>
                  </TouchableOpacity>
                )}
              </View>

              {/* Botón de confirmar pago */}
              {inscripcion.estado_pago === 'pendiente' && (
                <TouchableOpacity
                  style={styles.confirmButton}
                  onPress={() => handleConfirmarPago(inscripcion)}
                >
                  <Text style={styles.confirmButtonText}>Confirmar pago</Text>
                </TouchableOpacity>
              )}
            </View>
          ))
        )}
          </>
        )}

        {/* Pestaña de Métodos de Pago */}
        {activeTab === 'metodos' && (
          <>
            <View style={styles.methodsHeader}>
              <Text style={styles.methodsTitle}>Configura los métodos de pago disponibles</Text>
              <TouchableOpacity style={styles.addMetodoButton} onPress={() => openMetodoModal()}>
                <Ionicons name="add-circle-outline" size={18} color="#fff" />
                <Text style={styles.addMetodoButtonText}>Agregar Método</Text>
              </TouchableOpacity>
            </View>

            {loadingMetodos ? (
              <View style={styles.centerContainer}>
                <ActivityIndicator size="large" color="#f05d4b" />
                <Text style={styles.loadingText}>Cargando métodos...</Text>
              </View>
            ) : metodosPago.length === 0 ? (
              <View style={styles.emptyContainer}>
                <Ionicons name="wallet-outline" size={64} color="#444" />
                <Text style={styles.emptyText}>No hay métodos configurados</Text>
                <Text style={styles.emptySubtext}>Agrega un método de pago para empezar</Text>
              </View>
            ) : (
              metodosPago.map((metodo) => {
                const methodStyle = getPaymentMethodStyle(metodo.codigo);
                return (
                <View key={metodo.id} style={styles.methodCardFull}>
                  {/* Accent bar */}
                  <View style={[styles.methodAccentBar, { backgroundColor: methodStyle.color }]} />

                  <TouchableOpacity
                    style={styles.methodCardHeader}
                    onPress={() => toggleCard(metodo.id)}
                    activeOpacity={0.7}
                  >
                    <View style={styles.methodCardLeft}>
                      <View style={[styles.methodIconContainer, { backgroundColor: methodStyle.color + '20' }]}>
                        <Ionicons name={methodStyle.icon as any} size={24} color={methodStyle.color} />
                      </View>
                      <View style={styles.methodInfo}>
                        <Text style={styles.methodName}>{metodo.nombre}</Text>
                        <Text style={styles.methodCode}>{metodo.codigo}</Text>
                      </View>
                    </View>
                    <View style={styles.methodCardRight}>
                      <View style={[
                        styles.methodStatus,
                        metodo.activo === 1 ? styles.methodActive : styles.methodInactive
                      ]}>
                        <View style={[styles.statusDot, {
                          backgroundColor: metodo.activo === 1 ? '#27ae60' : '#7f8c8d'
                        }]} />
                        <Text style={styles.methodStatusText}>
                          {metodo.activo === 1 ? 'Activo' : 'Inactivo'}
                        </Text>
                      </View>
                      <Ionicons
                        name={expandedCards.has(metodo.id) ? 'chevron-up' : 'chevron-down'}
                        size={20}
                        color="#9b9b9b"
                      />
                    </View>
                  </TouchableOpacity>

                  {expandedCards.has(metodo.id) && (
                    <>
                      <View style={styles.methodExpandedContent}>
                        {/* Columna izquierda - Receptor y Teléfono apilados */}
                        <View style={styles.leftColumn}>
                          {/* Receptor arriba */}
                          <View style={styles.infoRow}>
                            <View style={[styles.detailIconBg, { backgroundColor: methodStyle.color + '15' }]}>
                              <Ionicons name="person-outline" size={16} color={methodStyle.color} />
                            </View>
                            <View style={styles.detailTextContainer}>
                              <Text style={styles.detailLabel}>Receptor</Text>
                              <Text style={styles.detailValue}>
                                {metodo.nombre_receptor || 'No especificado'}
                              </Text>
                            </View>
                          </View>

                          {/* Teléfono abajo */}
                          <View style={styles.infoRow}>
                            <View style={[styles.detailIconBg, { backgroundColor: methodStyle.color + '15' }]}>
                              <Ionicons name="call-outline" size={16} color={methodStyle.color} />
                            </View>
                            <View style={styles.detailTextContainer}>
                              <Text style={styles.detailLabel}>Teléfono</Text>
                              <Text style={styles.detailValue}>
                                {metodo.telefono_receptor || 'No especificado'}
                              </Text>
                            </View>
                          </View>
                        </View>

                        {/* Columna derecha - QR */}
                        {metodo.qr_imagen_url && (
                          <View style={styles.rightColumn}>
                            <View style={[styles.qrImageWrapper, { borderColor: methodStyle.color }]}>
                              <Image
                                source={{ uri: getImageUrl(metodo.qr_imagen_url) || '' }}
                                style={styles.qrImageSmall}
                                resizeMode="contain"
                              />
                            </View>
                          </View>
                        )}
                      </View>

                      <TouchableOpacity
                        style={[styles.editMethodButtonFull, { backgroundColor: methodStyle.color }]}
                        onPress={() => openMetodoModal(metodo)}
                      >
                        <Ionicons name="create-outline" size={18} color="#fff" />
                        <Text style={styles.editMethodButtonText}>Editar método</Text>
                      </TouchableOpacity>
                    </>
                  )}
                </View>
                );
              })
            )}
          </>
        )}
      </ScrollView>

      {/* Modal de métodos de pago */}
      <Modal
        visible={showMetodoModal}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setShowMetodoModal(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            {/* Header */}
            <View style={styles.modalHeaderMethod}>
              <View style={styles.modalHeaderLeft}>
                <Ionicons name="wallet" size={22} color="#f05d4b" />
                <Text style={styles.modalTitleMethod}>
                  {editingMetodo ? 'Editar Método' : 'Nuevo Método'}
                </Text>
              </View>
              <TouchableOpacity onPress={() => setShowMetodoModal(false)} style={styles.modalCloseBtn}>
                <Ionicons name="close" size={24} color="#999" />
              </TouchableOpacity>
            </View>

            {/* Body */}
            <ScrollView style={styles.modalBodyMethod} showsVerticalScrollIndicator={false}>
              {/* Primera fila - 2 columnas */}
              <View style={styles.formRow}>
                <View style={styles.formGroupHalf}>
                  <Text style={styles.labelCompact}>Código *</Text>
                  <TextInput
                    style={styles.inputCompact}
                    placeholder="yape"
                    placeholderTextColor="#666"
                    value={metodoForm.codigo}
                    onChangeText={(text) => setMetodoForm({ ...metodoForm, codigo: text })}
                    autoCapitalize="none"
                  />
                </View>

                <View style={styles.formGroupHalf}>
                  <Text style={styles.labelCompact}>Nombre *</Text>
                  <TextInput
                    style={styles.inputCompact}
                    placeholder="Yape"
                    placeholderTextColor="#666"
                    value={metodoForm.nombre}
                    onChangeText={(text) => setMetodoForm({ ...metodoForm, nombre: text })}
                  />
                </View>
              </View>

              {/* Segunda fila - 2 columnas */}
              <View style={styles.formRow}>
                <View style={styles.formGroupHalf}>
                  <Text style={styles.labelCompact}>Orden</Text>
                  <TextInput
                    style={styles.inputCompact}
                    placeholder="0"
                    placeholderTextColor="#666"
                    value={String(metodoForm.orden)}
                    onChangeText={(text) => setMetodoForm({ ...metodoForm, orden: Number(text) || 0 })}
                    keyboardType="number-pad"
                  />
                </View>

                <View style={styles.formGroupHalf}>
                  <Text style={styles.labelCompact}>Teléfono</Text>
                  <TextInput
                    style={styles.inputCompact}
                    placeholder="999888777"
                    placeholderTextColor="#666"
                    value={metodoForm.telefono_receptor}
                    onChangeText={(text) => setMetodoForm({ ...metodoForm, telefono_receptor: text })}
                    keyboardType="phone-pad"
                  />
                </View>
              </View>

              {/* Tercera fila - 2 columnas */}
              <View style={styles.formRow}>
                <View style={styles.formGroupHalf}>
                  <Text style={styles.labelCompact}>Nombre receptor</Text>
                  <TextInput
                    style={styles.inputCompact}
                    placeholder="Juan Perez"
                    placeholderTextColor="#666"
                    value={metodoForm.nombre_receptor}
                    onChangeText={(text) => setMetodoForm({ ...metodoForm, nombre_receptor: text })}
                  />
                </View>

                <View style={styles.formGroupHalf}>
                  <Text style={styles.labelCompact}>Código QR</Text>
                  <TouchableOpacity
                    style={styles.uploadQRButton}
                    onPress={pickQRImage}
                    disabled={uploadingQR}
                  >
                    {uploadingQR ? (
                      <ActivityIndicator size="small" color="#f05d4b" />
                    ) : (
                      <>
                        <Ionicons name="cloud-upload-outline" size={18} color="#f05d4b" />
                        <Text style={styles.uploadQRText}>
                          {qrPreview ? 'Cambiar QR' : 'Subir QR'}
                        </Text>
                      </>
                    )}
                  </TouchableOpacity>
                  {qrPreview && (
                    <View style={styles.qrPreviewContainer}>
                      <Image source={{ uri: qrPreview }} style={styles.qrPreview} />
                      <TouchableOpacity
                        style={styles.removeQRButton}
                        onPress={() => {
                          setQrPreview(null);
                          setMetodoForm({ ...metodoForm, qr_imagen_url: '' });
                        }}
                      >
                        <Ionicons name="close-circle" size={20} color="#e74c3c" />
                      </TouchableOpacity>
                    </View>
                  )}
                </View>
              </View>

              {/* Cuarta fila - Toggles */}
              <View style={styles.formRow}>
                <View style={styles.formGroupHalf}>
                  <Text style={styles.labelCompact}>Comprobante</Text>
                  <View style={styles.toggleRowCompact}>
                    <TouchableOpacity
                      style={[
                        styles.toggleButtonCompact,
                        metodoForm.requiere_comprobante === 1 && styles.toggleButtonActive,
                      ]}
                      onPress={() => setMetodoForm({ ...metodoForm, requiere_comprobante: 1 })}
                    >
                      <Text
                        style={[
                          styles.toggleTextCompact,
                          metodoForm.requiere_comprobante === 1 && styles.toggleTextActive,
                        ]}
                      >
                        Sí
                      </Text>
                    </TouchableOpacity>
                    <TouchableOpacity
                      style={[
                        styles.toggleButtonCompact,
                        metodoForm.requiere_comprobante === 0 && styles.toggleButtonActive,
                      ]}
                      onPress={() => setMetodoForm({ ...metodoForm, requiere_comprobante: 0 })}
                    >
                      <Text
                        style={[
                          styles.toggleTextCompact,
                          metodoForm.requiere_comprobante === 0 && styles.toggleTextActive,
                        ]}
                      >
                        No
                      </Text>
                    </TouchableOpacity>
                  </View>
                </View>

                <View style={styles.formGroupHalf}>
                  <Text style={styles.labelCompact}>Estado</Text>
                  <View style={styles.toggleRowCompact}>
                    <TouchableOpacity
                      style={[
                        styles.toggleButtonCompact,
                        metodoForm.activo === 1 && styles.toggleButtonActive,
                      ]}
                      onPress={() => setMetodoForm({ ...metodoForm, activo: 1 })}
                    >
                      <Text
                        style={[
                          styles.toggleTextCompact,
                          metodoForm.activo === 1 && styles.toggleTextActive,
                        ]}
                      >
                        Activo
                      </Text>
                    </TouchableOpacity>
                    <TouchableOpacity
                      style={[
                        styles.toggleButtonCompact,
                        metodoForm.activo === 0 && styles.toggleButtonActive,
                      ]}
                      onPress={() => setMetodoForm({ ...metodoForm, activo: 0 })}
                    >
                      <Text
                        style={[
                          styles.toggleTextCompact,
                          metodoForm.activo === 0 && styles.toggleTextActive,
                        ]}
                      >
                        Inactivo
                      </Text>
                    </TouchableOpacity>
                  </View>
                </View>
              </View>
            </ScrollView>

            {/* Footer */}
            <View style={styles.modalFooterMethod}>
              <TouchableOpacity
                style={styles.cancelBtnMethod}
                onPress={() => setShowMetodoModal(false)}
              >
                <Text style={styles.cancelBtnText}>Cancelar</Text>
              </TouchableOpacity>

              <TouchableOpacity
                style={[styles.saveBtnMethod, processing && { opacity: 0.7 }]}
                onPress={submitMetodoPago}
                disabled={processing}
              >
                {processing ? (
                  <ActivityIndicator color="#fff" size="small" />
                ) : (
                  <>
                    <Ionicons name="checkmark" size={20} color="#fff" />
                    <Text style={styles.saveBtnText}>Guardar</Text>
                  </>
                )}
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>

      {/* Modal para ver voucher ampliado */}
      <Modal
        visible={voucherModal.visible}
        animationType="fade"
        transparent={true}
        onRequestClose={() => setVoucherModal({ visible: false, uri: null })}
      >
        <TouchableOpacity
          style={styles.voucherModalOverlay}
          activeOpacity={1}
          onPress={() => setVoucherModal({ visible: false, uri: null })}
        >
          <View style={styles.voucherModalContent}>
            {voucherModal.uri && (
              <Image
                source={{ uri: voucherModal.uri }}
                style={styles.voucherModalImage}
                resizeMode="contain"
              />
            )}
            <TouchableOpacity
              style={styles.voucherModalClose}
              onPress={() => setVoucherModal({ visible: false, uri: null })}
            >
              <Ionicons name="close-circle" size={36} color="#fff" />
            </TouchableOpacity>
          </View>
        </TouchableOpacity>
      </Modal>

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
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#121212',
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#121212',
  },
  loadingText: {
    color: '#fff',
    marginTop: 10,
    fontSize: 16,
  },
  page: {
    flex: 1,
  },
  pageContent: {
    paddingBottom: 120,
  },
  hero: {
    paddingHorizontal: 20,
    paddingTop: 24,
    paddingBottom: 16,
  },
  heroHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  heroText: {
    flex: 1,
  },
  heroTitle: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#fff',
  },
  heroSubtitle: {
    fontSize: 14,
    color: '#b3b3b3',
    marginTop: 2,
  },
  tabsContainer: {
    flexDirection: 'row',
    paddingHorizontal: 20,
    gap: 10,
    marginBottom: 16,
  },
  tab: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    paddingVertical: 12,
    paddingHorizontal: 16,
    borderRadius: 12,
    backgroundColor: '#1f1f1f',
    borderWidth: 1,
    borderColor: '#2a2a2a',
  },
  tabActive: {
    backgroundColor: '#2a2020',
    borderColor: '#f05d4b',
  },
  tabText: {
    color: '#9b9b9b',
    fontSize: 14,
    fontWeight: '600',
  },
  tabTextActive: {
    color: '#f05d4b',
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#fff',
  },
  subtitle: {
    fontSize: 14,
    color: '#b3b3b3',
    marginTop: 2,
  },
  statsRow: {
    flexDirection: 'row',
    gap: 10,
    paddingHorizontal: 20,
    marginBottom: 14,
  },
  statCard: {
    flex: 1,
    backgroundColor: '#1d1d1d',
    borderRadius: 12,
    paddingVertical: 12,
    paddingHorizontal: 10,
    borderWidth: 1,
    borderColor: '#262626',
    alignItems: 'center',
  },
  statCardActive: {
    backgroundColor: '#2a2020',
    borderColor: '#f05d4b',
    borderWidth: 2,
  },
  statIcon: {
    marginBottom: 4,
  },
  statLabel: {
    color: '#9b9b9b',
    fontSize: 11,
    textTransform: 'uppercase',
    letterSpacing: 0.8,
  },
  statValue: {
    color: '#fff',
    fontSize: 18,
    fontWeight: '700',
    marginTop: 6,
  },
  methodsHeader: {
    paddingHorizontal: 20,
    marginBottom: 16,
  },
  methodsTitle: {
    color: '#b3b3b3',
    fontSize: 14,
    marginBottom: 12,
  },
  addMetodoButton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: '#f05d4b',
    borderRadius: 10,
    paddingVertical: 10,
    paddingHorizontal: 16,
    alignSelf: 'flex-start',
  },
  addMetodoButtonText: {
    color: '#fff',
    fontSize: 14,
    fontWeight: '700',
  },
  methodCardFull: {
    backgroundColor: '#1c1c1c',
    borderRadius: 16,
    marginHorizontal: 20,
    marginBottom: 14,
    borderWidth: 1,
    borderColor: '#262626',
    overflow: 'hidden',
    position: 'relative',
  },
  methodAccentBar: {
    position: 'absolute',
    left: 0,
    top: 0,
    bottom: 0,
    width: 4,
  },
  methodCardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 16,
  },
  methodIconContainer: {
    width: 48,
    height: 48,
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
  },
  methodCardLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    flex: 1,
  },
  methodCardRight: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  methodInfo: {
    flex: 1,
  },
  uploadQRButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    paddingVertical: 10,
    paddingHorizontal: 12,
    borderRadius: 8,
    backgroundColor: '#1a1a1a',
    borderWidth: 1,
    borderColor: '#f05d4b',
    borderStyle: 'dashed',
  },
  uploadQRText: {
    color: '#f05d4b',
    fontSize: 13,
    fontWeight: '600',
  },
  qrPreviewContainer: {
    marginTop: 8,
    position: 'relative',
    alignItems: 'center',
  },
  qrPreview: {
    width: 80,
    height: 80,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#444',
  },
  removeQRButton: {
    position: 'absolute',
    top: -8,
    right: '35%',
    backgroundColor: '#1a1a1a',
    borderRadius: 10,
  },
  qrDisplayContainer: {
    paddingHorizontal: 16,
    paddingVertical: 16,
    alignItems: 'center',
    backgroundColor: '#1a1a1a',
    marginHorizontal: 16,
    borderRadius: 12,
    marginBottom: 12,
  },
  qrLabelContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 12,
  },
  qrLabel: {
    fontSize: 13,
    fontWeight: '700',
    textTransform: 'uppercase',
    letterSpacing: 1,
  },
  qrImageWrapper: {
    backgroundColor: '#722C85',
    borderRadius: 3,
    padding: 3,
    borderWidth: 2,
  },
  qrDisplay: {
    width: 160,
    height: 160,
  },
  qrImageSmall: {
    width: 120,
    height: 120,
  },
  methodExpandedContent: {
    flexDirection: 'row',
    paddingHorizontal: 16,
    paddingBottom: 12,
    gap: 12,
  },
  leftColumn: {
    flex: 1,
    gap: 10,
  },
  rightColumn: {
    justifyContent: 'center',
    alignItems: 'center',
  },
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    gap: 10,
    backgroundColor: '#1a1a1a',
    padding: 12,
    borderRadius: 10,
  },
  methodDetails: {
    paddingHorizontal: 16,
    paddingBottom: 12,
    gap: 12,
  },
  twoColumnRow: {
    flexDirection: 'row',
    gap: 10,
  },
  columnItem: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    backgroundColor: '#1a1a1a',
    padding: 12,
    borderRadius: 10,
  },
  methodDetailRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    backgroundColor: '#1a1a1a',
    padding: 12,
    borderRadius: 10,
  },
  detailIconBg: {
    width: 36,
    height: 36,
    borderRadius: 8,
    alignItems: 'center',
    justifyContent: 'center',
  },
  detailTextContainer: {
    flex: 1,
  },
  detailLabel: {
    color: '#7f8c8d',
    fontSize: 11,
    fontWeight: '600',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    marginBottom: 2,
  },
  detailValue: {
    color: '#fff',
    fontSize: 14,
    fontWeight: '600',
  },
  editMethodButtonFull: {
    marginHorizontal: 16,
    marginBottom: 16,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    paddingVertical: 12,
    paddingHorizontal: 16,
    borderRadius: 10,
  },
  editMethodButtonText: {
    color: '#fff',
    fontSize: 14,
    fontWeight: '700',
  },
  emptySubtext: {
    color: '#666',
    fontSize: 14,
    marginTop: 4,
  },
  methodsSection: {
    paddingHorizontal: 20,
    paddingTop: 8,
    paddingBottom: 16,
  },
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 6,
  },
  heroSectionHeader: {
    width: '100%',
  },
  sectionTitleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  sectionTitle: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '700',
    marginBottom: 0,
  },
  sectionToggle: {
    color: '#f05d4b',
    fontSize: 12,
    fontWeight: '700',
  },
  sectionSubtitle: {
    color: '#9b9b9b',
    fontSize: 13,
  },
  methodsScroll: {
    marginTop: 4,
  },
  methodsList: {
    gap: 12,
    paddingBottom: 6,
  },
  methodName: {
    color: '#fff',
    fontSize: 17,
    fontWeight: '700',
    marginBottom: 2,
  },
  methodCode: {
    color: '#7f8c8d',
    fontSize: 11,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    fontWeight: '600',
  },
  methodStatus: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 20,
    backgroundColor: '#2a2a2a',
  },
  methodActive: {
    backgroundColor: '#27ae6020',
  },
  methodInactive: {
    backgroundColor: '#7f8c8d20',
  },
  statusDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
  },
  methodStatusText: {
    color: '#ccc',
    fontSize: 11,
    fontWeight: '600',
  },
  filterContainer: {
    flexDirection: 'row',
    paddingHorizontal: 20,
    paddingBottom: 8,
    gap: 10,
  },
  filterButton: {
    flex: 1,
    paddingVertical: 9,
    paddingHorizontal: 15,
    borderRadius: 12,
    backgroundColor: '#1f1f1f',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#2a2a2a',
  },
  filterButtonActive: {
    backgroundColor: '#f05d4b',
    borderColor: '#f05d4b',
  },
  filterText: {
    color: '#b5b5b5',
    fontSize: 14,
    fontWeight: '600',
  },
  filterTextActive: {
    color: '#fff',
  },
  emptyContainer: {
    paddingVertical: 40,
    alignItems: 'center',
  },
  emptyText: {
    color: '#8a8a8a',
    fontSize: 16,
  },
  card: {
    backgroundColor: '#1c1c1c',
    borderRadius: 16,
    padding: 16,
    marginHorizontal: 20,
    marginBottom: 14,
    borderWidth: 1,
    borderColor: '#262626',
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 12,
  },
  headerLeft: {
    flex: 1,
  },
  peleadorName: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#fff',
  },
  peleadorApodo: {
    fontSize: 14,
    color: '#f05d4b',
    fontStyle: 'italic',
    marginTop: 2,
  },
  headerDate: {
    fontSize: 12,
    color: '#7f8c8d',
    marginTop: 4,
  },
  badge: {
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: 6,
  },
  badgePaid: {
    backgroundColor: '#1f8f57',
  },
  badgePending: {
    backgroundColor: '#c97a1b',
  },
  badgeInscrito: {
    backgroundColor: '#3498db',
  },
  badgeText: {
    color: '#fff',
    fontSize: 11,
    fontWeight: 'bold',
  },
  cardBody: {
    gap: 8,
  },
  cardBodyColumns: {
    flexDirection: 'row',
    gap: 12,
    marginTop: 4,
  },
  columnLeft: {
    flex: 1,
    gap: 8,
  },
  columnRight: {
    alignItems: 'center',
    justifyContent: 'center',
    minWidth: 100,
  },
  infoRowCompact: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: '#1a1a1a',
    padding: 10,
    borderRadius: 8,
  },
  infoRowCompactClickable: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: '#1a1a1a',
    padding: 10,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#25D366',
  },
  infoLabelCompact: {
    fontSize: 11,
    color: '#7f8c8d',
    fontWeight: '600',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  infoValueCompact: {
    fontSize: 13,
    color: '#fff',
    fontWeight: '500',
    flex: 1,
    textAlign: 'right',
  },
  montoTextCompact: {
    fontSize: 14,
    color: '#27ae60',
    fontWeight: 'bold',
    flex: 1,
    textAlign: 'right',
  },
  comprobanteLabel: {
    fontSize: 10,
    color: '#7f8c8d',
    fontWeight: '600',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
    marginBottom: 6,
    textAlign: 'center',
  },
  comprobanteThumbnailWrapper: {
    borderWidth: 2,
    borderColor: '#f05d4b',
    borderRadius: 8,
    overflow: 'hidden',
    backgroundColor: '#fff',
  },
  comprobanteThumbnail: {
    width: 80,
    height: 100,
  },

  infoLabel: {
    fontSize: 13,
    color: '#9b9b9b',
  },
  infoValue: {
    fontSize: 13,
    color: '#fff',
    fontWeight: '500',
  },
  paymentInfo: {
    marginTop: 10,
    paddingTop: 10,
    borderTopWidth: 1,
    borderTopColor: '#2d2d2d',
    gap: 8,
  },
  montoText: {
    fontSize: 16,
    color: '#39c071',
    fontWeight: 'bold',
  },
  confirmButton: {
    marginTop: 12,
    backgroundColor: '#2fb56a',
    paddingVertical: 12,
    borderRadius: 8,
    alignItems: 'center',
  },
  confirmButtonText: {
    color: '#fff',
    fontSize: 14,
    fontWeight: '700',
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.8)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  modalContent: {
    backgroundColor: '#2c2c2c',
    borderRadius: 20,
    width: Platform.OS === 'web' ? '100%' : '90%',
    maxWidth: Platform.OS === 'web' ? 550 : undefined,
    maxHeight: '80%',
    padding: 20,
  },
  modalContainer: {
    backgroundColor: '#1c1c1c',
    borderRadius: 16,
    width: '94%',
    maxWidth: 550,
    maxHeight: '90%',
    borderWidth: 1,
    borderColor: '#262626',
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  modalHeaderMethod: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingVertical: 16,
    borderBottomWidth: 1,
    borderBottomColor: '#262626',
  },
  modalHeaderLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  modalCloseBtn: {
    padding: 4,
  },
  modalTitle: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#fff',
  },
  modalTitleMethod: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#fff',
  },
  modalBodyMethod: {
    paddingHorizontal: 20,
    paddingVertical: 4,
  },
  modalFooterMethod: {
    flexDirection: 'row',
    gap: 12,
    paddingHorizontal: 20,
    paddingVertical: 16,
    borderTopWidth: 1,
    borderTopColor: '#262626',
  },
  cancelBtnMethod: {
    flex: 1,
    paddingVertical: 12,
    borderRadius: 8,
    alignItems: 'center',
    backgroundColor: '#2a2a2a',
    borderWidth: 1,
    borderColor: '#3a3a3a',
  },
  cancelBtnText: {
    color: '#ccc',
    fontSize: 14,
    fontWeight: '600',
  },
  saveBtnMethod: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 6,
    paddingVertical: 12,
    borderRadius: 8,
    backgroundColor: '#27ae60',
  },
  saveBtnText: {
    color: '#fff',
    fontSize: 14,
    fontWeight: 'bold',
  },
  modalSubtitle: {
    fontSize: 14,
    color: '#999',
    marginBottom: 20,
  },
  closeButton: {
    fontSize: 28,
    color: '#999',
    fontWeight: 'bold',
  },
  formGroup: {
    marginBottom: 15,
  },
  formRow: {
    flexDirection: 'row',
    gap: 10,
    marginBottom: 10,
  },
  formGroupHalf: {
    flex: 1,
  },
  label: {
    fontSize: 14,
    color: '#ccc',
    marginBottom: 8,
    fontWeight: '600',
  },
  labelCompact: {
    fontSize: 12,
    color: '#ccc',
    marginBottom: 6,
    fontWeight: '600',
  },
  input: {
    backgroundColor: '#1a1a1a',
    borderRadius: 10,
    padding: 12,
    color: '#fff',
    fontSize: 16,
    borderWidth: 1,
    borderColor: '#444',
  },
  inputCompact: {
    backgroundColor: '#1a1a1a',
    borderRadius: 8,
    padding: 10,
    color: '#fff',
    fontSize: 14,
    borderWidth: 1,
    borderColor: '#444',
  },
  textArea: {
    minHeight: 80,
    textAlignVertical: 'top',
  },
  methodsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  methodButton: {
    paddingHorizontal: 15,
    paddingVertical: 10,
    borderRadius: 8,
    backgroundColor: '#1a1a1a',
    borderWidth: 1,
    borderColor: '#444',
  },
  methodButtonActive: {
    backgroundColor: '#e74c3c',
    borderColor: '#e74c3c',
  },
  methodText: {
    color: '#999',
    fontSize: 13,
    fontWeight: '600',
  },
  methodTextActive: {
    color: '#fff',
  },
  toggleRow: {
    flexDirection: 'row',
    gap: 8,
  },
  toggleRowCompact: {
    flexDirection: 'row',
    gap: 6,
  },
  toggleButton: {
    flex: 1,
    paddingVertical: 10,
    borderRadius: 8,
    backgroundColor: '#2c2c2c',
    alignItems: 'center',
  },
  toggleButtonCompact: {
    flex: 1,
    paddingVertical: 8,
    borderRadius: 6,
    backgroundColor: '#1a1a1a',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#444',
  },
  toggleButtonActive: {
    backgroundColor: '#e74c3c',
    borderColor: '#e74c3c',
  },
  toggleText: {
    color: '#999',
    fontSize: 13,
    fontWeight: '600',
  },
  toggleTextCompact: {
    color: '#999',
    fontSize: 12,
    fontWeight: '600',
  },
  toggleTextActive: {
    color: '#fff',
  },
  modalActions: {
    flexDirection: 'row',
    gap: 10,
    marginTop: 10,
  },
  modalActionsCompact: {
    flexDirection: 'row',
    gap: 8,
    marginTop: 12,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: '#444',
  },
  modalButton: {
    flex: 1,
    paddingVertical: 14,
    borderRadius: 10,
    alignItems: 'center',
  },
  modalButtonCompact: {
    flex: 1,
    paddingVertical: 10,
    borderRadius: 8,
    alignItems: 'center',
  },
  cancelButton: {
    backgroundColor: '#555',
  },
  submitButton: {
    backgroundColor: '#27ae60',
  },
  buttonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
  buttonTextCompact: {
    color: '#fff',
    fontSize: 14,
    fontWeight: 'bold',
  },
  comprobanteHint: {
    fontSize: 9,
    color: '#f05d4b',
    textAlign: 'center',
    marginTop: 4,
    fontWeight: '600',
  },
  voucherModalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.92)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  voucherModalContent: {
    width: '90%',
    maxHeight: '80%',
    alignItems: 'center',
  },
  voucherModalImage: {
    width: '100%',
    height: 500,
    borderRadius: 12,
  },
  voucherModalClose: {
    position: 'absolute',
    top: -20,
    right: 0,
  },
  voucherPreviewContainer: {
    marginBottom: 16,
    backgroundColor: '#1a1a1a',
    borderRadius: 12,
    padding: 12,
    borderWidth: 1,
    borderColor: '#f05d4b',
  },
  voucherPreviewLabel: {
    color: '#ccc',
    fontSize: 13,
    fontWeight: '600',
    marginBottom: 8,
  },
  voucherPreviewImage: {
    width: '100%',
    height: 200,
    borderRadius: 8,
    backgroundColor: '#fff',
  },
  voucherPreviewHint: {
    fontSize: 11,
    color: '#f05d4b',
    textAlign: 'center',
    marginTop: 6,
    fontWeight: '600',
  },
  messageModalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.8)',
    justifyContent: 'center',
    alignItems: 'center',
  },
  messageModalContainer: {
    backgroundColor: '#1c1c1c',
    borderRadius: 20,
    padding: 30,
    width: '85%',
    maxWidth: 400,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#262626',
  },
  messageModalIcon: {
    width: 100,
    height: 100,
    borderRadius: 50,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 20,
  },
  messageModalTitle: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#fff',
    marginBottom: 12,
  },
  messageModalMessage: {
    fontSize: 15,
    color: '#ccc',
    textAlign: 'center',
    marginBottom: 24,
    lineHeight: 22,
  },
  messageModalBtn: {
    paddingVertical: 12,
    paddingHorizontal: 40,
    borderRadius: 10,
    minWidth: 140,
  },
  messageModalBtnText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
    textAlign: 'center',
  },
});
