import React, { useState, useEffect, useRef, useCallback } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  ActivityIndicator,
  TextInput,
  Image,
  Dimensions,
  Animated,
  SafeAreaView,
  StatusBar,
  KeyboardAvoidingView,
  Platform,
  Linking,
  Alert,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import * as Haptics from 'expo-haptics';
import * as ImagePicker from 'expo-image-picker';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { useNavigation, useRoute, useFocusEffect } from '@react-navigation/native';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS, SHADOWS } from '../constants/theme';
import { ScreenHeader } from '../components/common/ScreenHeader';
import { SuccessModal } from '../components/SuccessModal';
import { ErrorModal } from '../components/ErrorModal';
import boletosService from '../services/boletosService';
import { AdminService } from '../services/AdminService';
import { fighterService } from '../services/fighterService';
import api from '../services/api';
import { TipoBoleto, ComprarBoletoRequest } from '../types';
import { Config } from '../config/config';

const { width: SCREEN_WIDTH } = Dimensions.get('window');

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

export default function BuyTicketsScreen() {
  const navigation = useNavigation();
  const route = useRoute();
  const params = (route.params as { eventoId?: number; eventoNombre?: string }) || {};
  const { eventoId, eventoNombre } = params;

  // Data State
  const [tiposBoleto, setTiposBoleto] = useState<TipoBoleto[]>([]);
  const [metodosPago, setMetodosPago] = useState<MetodoPagoInfo[]>([]);
  const [managerGeneral, setManagerGeneral] = useState<any>(null);
  const [activeEventoId, setActiveEventoId] = useState<number | null>(eventoId || null);
  const [loading, setLoading] = useState(true);
  const [userId, setUserId] = useState<number | null>(null);

  // Form State
  const [selectedTipo, setSelectedTipo] = useState<TipoBoleto | null>(null);
  const [cantidad, setCantidad] = useState(1);
  const [formData, setFormData] = useState({
    nombres_apellidos: '',
    telefono: '',
    dni: '',
    metodo_pago: '',
  });

  // UI State
  const [currentStep, setCurrentStep] = useState(1);
  const [submitting, setSubmitting] = useState(false);
  const [showSuccess, setShowSuccess] = useState(false);
  const [showError, setShowError] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');
  const [purchaseResponse, setPurchaseResponse] = useState<any>(null);
  const [comprobante, setComprobante] = useState<{ uri: string; name: string; type: string } | null>(null);
  const [uploading, setUploading] = useState(false);

  // Animation Refs
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const slideAnim = useRef(new Animated.Value(20)).current;

  // Reset screen state on focus
  useFocusEffect(
    useCallback(() => {
      // Regresar al primer paso
      setCurrentStep(1);

      // Limpiar errores previos
      setShowError(false);
      setErrorMessage('');

      // Reiniciar formulario y comprobante
      setFormData({
        nombres_apellidos: '',
        telefono: '',
        dni: '',
        metodo_pago: '',
      });
      setComprobante(null);
      setPurchaseResponse(null);

      // Intentar auto-llenar datos del usuario
      loadUserData();

      return () => {
        // Cleanup if needed
      };
    }, [])
  );

  const loadUserData = async () => {
    try {
      const userData = await AsyncStorage.getItem('user');
      if (userData) {
        const user = JSON.parse(userData);
        // Limpiar telefono de prefijos (+51)
        let cleanPhone = user.telefono || '';
        cleanPhone = cleanPhone.replace(/\D/g, ''); // Solo números
        if (cleanPhone.startsWith('51') && cleanPhone.length > 9) {
          cleanPhone = cleanPhone.substring(2);
        }

        setFormData(prev => ({
          ...prev,
          nombres_apellidos: `${user.nombre || ''} ${user.apellidos || ''}`.trim(),
          dni: user.dni || prev.dni,
          telefono: cleanPhone || prev.telefono,
        }));
        if (user.id) setUserId(user.id);
      }
    } catch (error) {
      console.log('Error al cargar datos de usuario en BuyTickets:', error);
    }
  };

  useEffect(() => {
    loadInitialData();
  }, []);

  useEffect(() => {
    Animated.parallel([
      Animated.timing(fadeAnim, { toValue: 1, duration: 400, useNativeDriver: true }),
      Animated.timing(slideAnim, { toValue: 0, duration: 400, useNativeDriver: true }),
    ]).start();
  }, [currentStep]);

  const loadInitialData = async () => {
    try {
      setLoading(true);

      let currentEventoId = activeEventoId;

      // Si no hay eventoId en params, buscar el evento activo
      if (!currentEventoId) {
        const response = await api.get('/eventos');
        if (response.data && response.data.evento) {
          currentEventoId = response.data.evento.id;
          setActiveEventoId(currentEventoId);
        }
      }

      if (!currentEventoId) {
        setLoading(false);
        return;
      }

      const [tiposRes, metodosRes, managerRes] = await Promise.all([
        boletosService.getTiposDisponibles(currentEventoId),
        AdminService.getMetodosPago({ activo: 1 }),
        fighterService.getManagerContacto('manager_general'),
      ]);

      setTiposBoleto(tiposRes || []);
      setMetodosPago(metodosRes.metodos || []);
      if (managerRes.success) setManagerGeneral(managerRes.manager);

      if (tiposRes && tiposRes.length > 0) {
        setSelectedTipo(tiposRes[0]);
      }
    } catch (error: any) {
      console.log('====================================');
      console.log('❌ ERROR EN COMPRA DE BOLETO ❌');
      console.log('Message:', error.message);
      if (error.response) {
        console.log('Status:', error.response.status);
        console.log('Data:', JSON.stringify(error.response.data, null, 2));
        console.log('Headers:', JSON.stringify(error.response.headers, null, 2));
      } else {
        console.log('Full Error:', JSON.stringify(error, null, 2));
      }
      console.log('====================================');

      // Mostrar mensaje más técnico si está disponible
      let msg = error.message || 'No se pudo procesar la compra.';
      if (error.response?.data?.message) {
        msg = `${error.response.data.message}`;
      } else if (error.response?.data?.error) {
        msg = `${error.response.data.error}`;
      }

      setErrorMessage(msg);
      setShowError(true);
    } finally {
      setLoading(false);
    }
  };

  const pickComprobante = async () => {
    try {
      const permissionResult = await ImagePicker.requestMediaLibraryPermissionsAsync();
      if (!permissionResult.granted) {
        Alert.alert('Permiso requerido', 'Se requiere permiso para acceder a tu galeria.');
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

  const requiresComprobante = (metodo: string) => {
    const found = metodosPago.find(m => m.codigo === metodo);
    return found ? found.requiere_comprobante === 1 : ['yape', 'plin', 'transferencia', 'deposito'].includes(metodo);
  };

  const handleNextStep = () => {
    if (currentStep === 1 && !selectedTipo) return;
    if (currentStep === 2) {
      if (!formData.nombres_apellidos || !formData.dni || formData.telefono.length < 9) {
        Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error);
        return;
      }
    }
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
    fadeAnim.setValue(0);
    slideAnim.setValue(20);
    setCurrentStep(prev => prev + 1);
  };

  const handleBackStep = () => {
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
    fadeAnim.setValue(0);
    slideAnim.setValue(-20);
    setCurrentStep(prev => prev - 1);
  };

  const handlePurchase = async () => {
    if (!formData.metodo_pago) return;

    try {
      setSubmitting(true);
      Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Medium);

      const request: ComprarBoletoRequest = {
        evento_id: activeEventoId!,
        tipo_boleto_id: selectedTipo!.id,
        comprador_nombres_apellidos: formData.nombres_apellidos,
        comprador_telefono: formData.telefono.replace(/\s/g, ''),
        comprador_dni: formData.dni,
        cantidad: cantidad,
        metodo_pago: formData.metodo_pago as any,
        usuario_id: userId || undefined,
      };

      const response = await boletosService.comprarBoleto(request);

      if (response.success) {
        const boletoId = response.data?.id || response.data?.boleto_id;

        // Si hay comprobante seleccionado, subirlo
        if (boletoId && comprobante) {
          setUploading(true);
          try {
            await boletosService.subirComprobante(boletoId, comprobante);
          } catch (uploadErr) {
            console.error('Error al subir comprobante, pero la compra se registro:', uploadErr);
          } finally {
            setUploading(false);
          }
        }

        // Guardar en AsyncStorage para usuarios anónimos
        const boletoData = {
          id: boletoId,
          pdf_url: response.data?.pdf_url,
          total: (Number(selectedTipo?.precio || 0) * cantidad).toFixed(2),
          cantidad: cantidad,
          tipo_boleto: selectedTipo?.nombre,
          comprador: formData.nombres_apellidos,
          metodo_pago: formData.metodo_pago,
          fecha_compra: new Date().toISOString(),
        };

        try {
          const existing = await AsyncStorage.getItem('anonymous_tickets');
          const tickets = existing ? JSON.parse(existing) : [];
          // Evitar duplicados
          if (!tickets.find((t: any) => t.id === boletoId)) {
            tickets.unshift(boletoData); // Agregar al inicio
            await AsyncStorage.setItem('anonymous_tickets', JSON.stringify(tickets));
          }
        } catch (e) {
          console.error('Error saving ticket locally', e);
        }

        // Navegar a la pantalla de éxito
        navigation.navigate('PurchaseSuccess' as never, { purchaseData: boletoData } as never);
        // setPurchaseResponse(response);
        // setShowSuccess(true);
        setFormData({ ...formData, nombres_apellidos: '', dni: '', telefono: '', metodo_pago: '' });
        setComprobante(null);
        setCurrentStep(1);
      } else {
        throw new Error(response.message || 'No se pudo procesar la compra.');
      }
    } catch (error: any) {
      console.error('Error en compra:', error);
      const msg = error.response?.data?.error || error.response?.data?.message || error.message || 'Error inesperado';
      setErrorMessage(msg);
      setShowError(true);
      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error);
    } finally {
      setSubmitting(false);
    }
  };

  const openManagerWhatsApp = () => {
    if (!managerGeneral) return;
    const phone = managerGeneral.telefono_whatsapp.replace(/\D/g, '');
    const phoneWithCode = phone.startsWith('51') ? phone : `51${phone}`;
    const msg = managerGeneral.mensaje_base || `Hola, necesito ayuda con la compra de boletos para el evento ${eventoNombre || ''}.`;
    Linking.openURL(`https://wa.me/${phoneWithCode}?text=${encodeURIComponent(msg)}`);
  };

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color={COLORS.primary} />
        <Text style={styles.loadingText}>Preparando taquilla...</Text>
      </View>
    );
  }

  if (!activeEventoId && !loading) {
    return (
      <SafeAreaView style={styles.container}>
        <ScreenHeader title="COMPRAR ENTRADAS" showBackButton={true} onBackPress={() => navigation.goBack()} />
        <View style={styles.errorContainer}>
          <Ionicons name="alert-circle" size={80} color={COLORS.error} />
          <Text style={styles.errorTitle}>Evento no encontrado</Text>
          <Text style={styles.errorSubtitle}>No se especifico un evento valido para comprar entradas.</Text>
          <TouchableOpacity style={styles.nextButton} onPress={() => navigation.goBack()}>
            <Text style={styles.nextButtonText}>VOLVER</Text>
          </TouchableOpacity>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor="#000" />
      <ScreenHeader
        title="COMPRAR ENTRADAS"
        showBackButton={true}
        onBackPress={() => {
          if (currentStep > 1) handleBackStep();
          else navigation.goBack();
        }}
      />

      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        style={{ flex: 1 }}
      >
        <ScrollView
          contentContainerStyle={styles.scrollContent}
          keyboardShouldPersistTaps="handled"
        >
          {/* Step Indicator */}
          <View style={styles.stepIndicator}>
            {[1, 2, 3].map(step => (
              <React.Fragment key={step}>
                <View style={[styles.stepCircle, currentStep >= step && styles.stepCircleActive]}>
                  <Text style={[styles.stepText, currentStep >= step && styles.stepTextActive]}>{step}</Text>
                </View>
                {step < 3 && <View style={[styles.stepLine, currentStep > step && styles.stepLineActive]} />}
              </React.Fragment>
            ))}
          </View>

          <Animated.View style={{ opacity: fadeAnim, transform: [{ translateY: slideAnim }] }}>
            {currentStep === 1 && (
              <View style={styles.stepContainer}>
                <Text style={styles.stepTitle}>Selecciona tu entrada</Text>
                <Text style={styles.stepSubtitle}>Elige el tipo de boleto y la cantidad</Text>

                {tiposBoleto.map(tipo => (
                  <TouchableOpacity
                    key={tipo.id}
                    style={[
                      styles.tipoCard,
                      selectedTipo?.id === tipo.id && { borderColor: tipo.color_hex || COLORS.primary, borderWidth: 2 }
                    ]}
                    onPress={() => {
                      setSelectedTipo(tipo);
                      Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
                    }}
                  >
                    <View style={[styles.tipoColorBar, { backgroundColor: tipo.color_hex || COLORS.primary }]} />
                    <View style={styles.tipoInfo}>
                      <Text style={styles.tipoNombre}>{tipo.nombre}</Text>
                      {tipo.descripcion && <Text style={styles.tipoDesc}>{tipo.descripcion}</Text>}
                      <Text style={styles.tipoPrecio}>S/ {Number(tipo.precio).toFixed(2)}</Text>
                    </View>
                    {selectedTipo?.id === tipo.id && (
                      <Ionicons name="checkmark-circle" size={24} color={tipo.color_hex || COLORS.primary} />
                    )}
                  </TouchableOpacity>
                ))}

                <View style={styles.cantidadContainer}>
                  <Text style={styles.label}>Cantidad</Text>
                  <View style={styles.cantidadControls}>
                    <TouchableOpacity
                      style={styles.cantidadBtn}
                      onPress={() => {
                        if (cantidad > 1) setCantidad(cantidad - 1);
                        Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
                      }}
                    >
                      <Ionicons name="remove" size={24} color="#fff" />
                    </TouchableOpacity>
                    <Text style={styles.cantidadValue}>{cantidad}</Text>
                    <TouchableOpacity
                      style={styles.cantidadBtn}
                      onPress={() => {
                        setCantidad(cantidad + 1);
                        Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
                      }}
                    >
                      <Ionicons name="add" size={24} color="#fff" />
                    </TouchableOpacity>
                  </View>
                </View>

                <TouchableOpacity style={styles.nextButton} onPress={handleNextStep}>
                  <Text style={styles.nextButtonText}>CONTINUAR</Text>
                  <Ionicons name="arrow-forward" size={20} color="#000" />
                </TouchableOpacity>
              </View>
            )}

            {currentStep === 2 && (
              <View style={styles.stepContainer}>
                <Text style={styles.stepTitle}>Tus datos</Text>
                <Text style={styles.stepSubtitle}>Ingresa la informacion del comprador</Text>

                <View style={styles.inputGroup}>
                  <Text style={styles.inputLabel}>Nombres y Apellidos</Text>
                  <TextInput
                    style={styles.input}
                    value={formData.nombres_apellidos}
                    onChangeText={text => setFormData({ ...formData, nombres_apellidos: text })}
                    placeholder="Ej: Juan Perez"
                    placeholderTextColor="#666"
                  />
                </View>

                <View style={styles.inputGroup}>
                  <Text style={styles.inputLabel}>DNI</Text>
                  <TextInput
                    style={styles.input}
                    value={formData.dni}
                    onChangeText={text => setFormData({ ...formData, dni: text })}
                    placeholder="8 digitos"
                    keyboardType="number-pad"
                    maxLength={8}
                    placeholderTextColor="#666"
                  />
                </View>

                <View style={styles.inputGroup}>
                  <Text style={styles.inputLabel}>WhatsApp / Telefono</Text>
                  <TextInput
                    style={styles.input}
                    value={formData.telefono}
                    onChangeText={text => setFormData({ ...formData, telefono: text })}
                    placeholder="Ej: 987654321"
                    keyboardType="phone-pad"
                    maxLength={9}
                    placeholderTextColor="#666"
                  />
                </View>

                <View style={styles.buttonRow}>
                  <TouchableOpacity style={styles.backStepButton} onPress={handleBackStep}>
                    <Ionicons name="arrow-back" size={20} color="#fff" />
                    <Text style={styles.backStepButtonText}>VOLVER</Text>
                  </TouchableOpacity>
                  <TouchableOpacity style={[styles.nextButton, { flex: 2 }]} onPress={handleNextStep}>
                    <Text style={styles.nextButtonText}>CONTINUAR</Text>
                    <Ionicons name="arrow-forward" size={20} color="#000" />
                  </TouchableOpacity>
                </View>
              </View>
            )}

            {currentStep === 3 && (
              <View style={styles.stepContainer}>
                <Text style={styles.stepTitle}>Pago y Confirmacion</Text>
                <Text style={styles.stepSubtitle}>Resumen de tu pedido</Text>

                <View style={styles.resumenCard}>
                  <View style={styles.resumenRow}>
                    <Text style={styles.resumenLabel}>Entrada</Text>
                    <Text style={styles.resumenValue}>{selectedTipo?.nombre} x {cantidad}</Text>
                  </View>
                  <View style={styles.resumenRow}>
                    <Text style={styles.resumenLabel}>Total a pagar</Text>
                    <Text style={styles.resumenTotal}>S/ {(Number(selectedTipo?.precio || 0) * cantidad).toFixed(2)}</Text>
                  </View>
                </View>

                <Text style={styles.sectionLabel}>Elige tu metodo de pago</Text>
                <View style={styles.metodosGrid}>
                  {metodosPago.map(metodo => (
                    <TouchableOpacity
                      key={metodo.id}
                      style={[
                        styles.metodoItem,
                        formData.metodo_pago === metodo.codigo && styles.metodoItemSelected
                      ]}
                      onPress={() => {
                        setFormData({ ...formData, metodo_pago: metodo.codigo });
                        Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
                      }}
                    >
                      <Ionicons
                        name={metodo.codigo === 'yape' || metodo.codigo === 'plin' ? 'phone-portrait-outline' : 'card-outline'}
                        size={32}
                        color={formData.metodo_pago === metodo.codigo ? COLORS.primary : '#888'}
                      />
                      <Text style={[
                        styles.metodoNombre,
                        formData.metodo_pago === metodo.codigo && { color: COLORS.primary, fontWeight: '700' }
                      ]}>
                        {metodo.nombre}
                      </Text>
                    </TouchableOpacity>
                  ))}
                </View>

                {formData.metodo_pago !== '' && (
                  <View style={styles.instruccionesPago}>
                    {metodosPago.find(m => m.codigo === formData.metodo_pago)?.qr_imagen_url && (
                      <View style={styles.qrContainer}>
                        <Image
                          source={{ uri: `${Config.BASE_URL}/${metodosPago.find(m => m.codigo === formData.metodo_pago)?.qr_imagen_url}` }}
                          style={styles.qrImage}
                          resizeMode="contain"
                        />
                        <Text style={styles.qrHint}>Escanea para pagar</Text>
                      </View>
                    )}
                    <View style={styles.pagoInfo}>
                      <Text style={styles.pagoDestinatario}>
                        A nombre de: {metodosPago.find(m => m.codigo === formData.metodo_pago)?.nombre_receptor || 'Empresa'}
                      </Text>
                      <Text style={styles.pagoTelefono}>
                        Telefono/Cuenta: {metodosPago.find(m => m.codigo === formData.metodo_pago)?.telefono_receptor || '-'}
                      </Text>

                      {/* Carga de Comprobante */}
                      {formData.metodo_pago !== 'efectivo' && (
                        <View style={styles.uploadSection}>
                          <Text style={styles.uploadLabel}>
                            Sube tu voucher de pago {requiresComprobante(formData.metodo_pago) ? '*' : '(opcional)'}
                          </Text>
                          {comprobante ? (
                            <View style={styles.previewContainer}>
                              <Image source={{ uri: comprobante.uri }} style={styles.previewImage} />
                              <TouchableOpacity style={styles.removePreview} onPress={() => setComprobante(null)}>
                                <Ionicons name="close-circle" size={24} color={COLORS.error} />
                              </TouchableOpacity>
                              <Text style={styles.previewStatus}>Voucher seleccionado</Text>
                            </View>
                          ) : (
                            <TouchableOpacity style={styles.uploadButton} onPress={pickComprobante}>
                              <Ionicons name="camera" size={24} color={COLORS.primary} />
                              <Text style={styles.uploadButtonText}>SUBIR CAPTURA / SCREENSHOT</Text>
                            </TouchableOpacity>
                          )}
                        </View>
                      )}

                      <Text style={styles.pagoDisclaimer}>
                        * Tu compra sera validada por un administrador al recibir el comprobante.
                      </Text>
                    </View>
                  </View>
                )}

                <View style={styles.buttonRow}>
                  <TouchableOpacity style={styles.backStepButton} onPress={handleBackStep} disabled={submitting}>
                    <Ionicons name="arrow-back" size={20} color="#fff" />
                    <Text style={styles.backStepButtonText}>VOLVER</Text>
                  </TouchableOpacity>
                  <TouchableOpacity
                    style={[
                      styles.buyButton,
                      (submitting || (requiresComprobante(formData.metodo_pago) && !comprobante)) && styles.buyButtonDisabled
                    ]}
                    onPress={handlePurchase}
                    disabled={submitting || (requiresComprobante(formData.metodo_pago) && !comprobante)}
                  >
                    {submitting || uploading ? (
                      <ActivityIndicator color="#000" />
                    ) : (
                      <>
                        <Ionicons name="cart" size={22} color="#000" />
                        <Text style={styles.buyButtonText}>FINALIZAR COMPRA</Text>
                      </>
                    )}
                  </TouchableOpacity>
                </View>
              </View>
            )}
          </Animated.View>

          {/* Manager Contact */}
          {managerGeneral && (
            <TouchableOpacity style={styles.managerSupport} onPress={openManagerWhatsApp}>
              <Ionicons name="logo-whatsapp" size={20} color="#25D366" />
              <Text style={styles.managerSupportText}>¿Necesitas ayuda? Contacta a {managerGeneral.nombre_visible}</Text>
            </TouchableOpacity>
          )}
        </ScrollView>
      </KeyboardAvoidingView>

      {/* Modals */}
      <SuccessModal
        visible={showSuccess}
        title="Compra Exitosa"
        message={formData.nombres_apellidos
          ? `¡Gracias ${formData.nombres_apellidos.split(' ')[0]}! Tu pago ha sido enviado y registrado. Tu boleto y comprobante serán verificados por la administración.`
          : 'Tu pago ha sido enviado y registrado. Tu boleto y comprobante serán verificados por la administración.'}
        buttonText={purchaseResponse?.data?.pdf_url ? "Descargar Boleto PDF" : "Ir a mi Perfil"}
        onClose={() => {
          setShowSuccess(false);
          if (purchaseResponse?.data?.pdf_url) {
            Linking.openURL(purchaseResponse.data.pdf_url);
          } else if (formData.dni) {
            navigation.navigate('Profile' as never);
          }
        }}
        secondaryButtonText={purchaseResponse?.data?.pdf_url ? "Ir a mi Perfil" : undefined}
        onSecondaryClose={() => {
          setShowSuccess(false);
          navigation.navigate('Profile' as never);
        }}
      />

      <ErrorModal
        visible={showError}
        title="Ups, algo salio mal"
        message={errorMessage}
        onClose={() => setShowError(false)}
      />
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
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: COLORS.background,
  },
  loadingText: {
    color: '#fff',
    marginTop: SPACING.md,
    fontSize: 16,
    letterSpacing: 1,
  },
  errorContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: SPACING.xl,
  },
  errorTitle: {
    fontSize: 24,
    fontWeight: '800',
    color: '#fff',
    marginTop: SPACING.lg,
    marginBottom: SPACING.sm,
  },
  errorSubtitle: {
    fontSize: 16,
    color: '#888',
    textAlign: 'center',
    marginBottom: SPACING.xl,
  },
  scrollContent: {
    padding: SPACING.lg,
    paddingBottom: 40,
  },
  stepIndicator: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 30,
  },
  stepCircle: {
    width: 32,
    height: 32,
    borderRadius: 16,
    backgroundColor: '#333',
    justifyContent: 'center',
    alignItems: 'center',
  },
  stepCircleActive: {
    backgroundColor: COLORS.primary,
  },
  stepText: {
    color: '#888',
    fontWeight: 'bold',
  },
  stepTextActive: {
    color: '#000',
  },
  stepLine: {
    width: 40,
    height: 2,
    backgroundColor: '#333',
    marginHorizontal: 8,
  },
  stepLineActive: {
    backgroundColor: COLORS.primary,
  },
  stepContainer: {
    minHeight: 400,
  },
  stepTitle: {
    fontSize: 28,
    fontWeight: '800',
    color: '#fff',
    marginBottom: 4,
  },
  stepSubtitle: {
    fontSize: 16,
    color: '#888',
    marginBottom: 24,
  },
  tipoCard: {
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.lg,
    flexDirection: 'row',
    alignItems: 'center',
    padding: 16,
    marginBottom: 12,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.05)',
  },
  tipoColorBar: {
    width: 6,
    height: '100%',
    borderRadius: 3,
    marginRight: 16,
  },
  tipoInfo: {
    flex: 1,
  },
  tipoNombre: {
    fontSize: 18,
    fontWeight: '700',
    color: '#fff',
  },
  tipoDesc: {
    fontSize: 12,
    color: '#888',
    marginTop: 2,
  },
  tipoPrecio: {
    fontSize: 20,
    fontWeight: '800',
    color: COLORS.primary,
    marginTop: 6,
  },
  cantidadContainer: {
    marginTop: 20,
    marginBottom: 30,
  },
  label: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
    marginBottom: 12,
  },
  cantidadControls: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.md,
    alignSelf: 'flex-start',
    padding: 4,
  },
  cantidadBtn: {
    width: 44,
    height: 44,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#444',
    borderRadius: BORDER_RADIUS.sm,
  },
  cantidadValue: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#fff',
    paddingHorizontal: 24,
  },
  nextButton: {
    backgroundColor: COLORS.primary,
    height: 56,
    borderRadius: BORDER_RADIUS.md,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 10,
    ...SHADOWS.md,
  },
  nextButtonText: {
    fontWeight: '800',
    fontSize: 16,
    color: '#000',
    letterSpacing: 1,
  },
  inputGroup: {
    marginBottom: 20,
  },
  inputLabel: {
    color: '#fff',
    fontSize: 14,
    fontWeight: '600',
    marginBottom: 8,
    marginLeft: 4,
  },
  input: {
    backgroundColor: COLORS.surface,
    color: '#fff',
    height: 54,
    borderRadius: BORDER_RADIUS.md,
    paddingHorizontal: 16,
    fontSize: 16,
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.05)',
  },
  buttonRow: {
    flexDirection: 'row',
    gap: 12,
    marginTop: 20,
  },
  backStepButton: {
    backgroundColor: '#333',
    height: 56,
    borderRadius: BORDER_RADIUS.md,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 20,
    gap: 8,
  },
  backStepButtonText: {
    color: '#fff',
    fontWeight: '700',
    fontSize: 14,
  },
  resumenCard: {
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.lg,
    padding: 20,
    marginBottom: 24,
    borderLeftWidth: 4,
    borderLeftColor: COLORS.primary,
  },
  resumenRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  resumenLabel: {
    color: '#888',
    fontSize: 14,
  },
  resumenValue: {
    color: '#fff',
    fontWeight: '600',
    fontSize: 15,
  },
  resumenTotal: {
    color: COLORS.primary,
    fontWeight: '900',
    fontSize: 24,
  },
  sectionLabel: {
    color: '#fff',
    fontSize: 18,
    fontWeight: '800',
    marginBottom: 16,
  },
  metodosGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
    marginBottom: 24,
  },
  metodoItem: {
    width: (SCREEN_WIDTH - 64) / 3,
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.md,
    padding: 16,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: 'transparent',
  },
  metodoItemSelected: {
    borderColor: COLORS.primary,
    backgroundColor: 'rgba(255,215,0,0.05)',
  },
  metodoNombre: {
    color: '#888',
    fontSize: 11,
    marginTop: 8,
    textAlign: 'center',
    textTransform: 'uppercase',
  },
  instruccionesPago: {
    backgroundColor: 'rgba(16,185,129,0.05)',
    borderRadius: BORDER_RADIUS.lg,
    padding: 16,
    borderWidth: 1,
    borderColor: 'rgba(16,185,129,0.1)',
    marginBottom: 24,
  },
  qrContainer: {
    alignItems: 'center',
    marginBottom: 16,
  },
  qrImage: {
    width: 180,
    height: 180,
    borderRadius: 8,
    backgroundColor: '#fff',
    padding: 10,
  },
  qrHint: {
    color: COLORS.success,
    fontSize: 12,
    marginTop: 8,
    fontWeight: '600',
  },
  pagoInfo: {
    gap: 4,
  },
  pagoDestinatario: {
    color: '#eee',
    fontSize: 14,
    fontWeight: '700',
  },
  pagoTelefono: {
    color: COLORS.success,
    fontSize: 16,
    fontWeight: '800',
  },
  pagoDisclaimer: {
    color: '#888',
    fontSize: 11,
    marginTop: 8,
    fontStyle: 'italic',
  },
  buyButton: {
    flex: 1,
    backgroundColor: COLORS.primary,
    height: 56,
    borderRadius: BORDER_RADIUS.md,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 10,
  },
  buyButtonText: {
    color: '#000',
    fontWeight: '900',
    fontSize: 16,
  },
  buyButtonDisabled: {
    opacity: 0.6,
  },
  managerSupport: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 30,
    padding: 16,
    backgroundColor: 'rgba(37,211,102,0.05)',
    borderRadius: BORDER_RADIUS.md,
    gap: 8,
  },
  managerSupportText: {
    color: '#25D366',
    fontSize: 13,
    fontWeight: '600',
  },
  uploadSection: {
    marginTop: 16,
    paddingTop: 16,
    borderTopWidth: 1,
    borderTopColor: 'rgba(255,255,255,0.1)',
  },
  uploadLabel: {
    color: '#fff',
    fontSize: 14,
    fontWeight: '700',
    marginBottom: 12,
  },
  uploadButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: 'rgba(255,255,255,0.05)',
    borderWidth: 1,
    borderColor: COLORS.primary,
    borderStyle: 'dashed',
    borderRadius: BORDER_RADIUS.md,
    padding: 16,
    gap: 10,
  },
  uploadButtonText: {
    color: COLORS.primary,
    fontSize: 13,
    fontWeight: '800',
  },
  previewContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(255,255,255,0.05)',
    borderRadius: BORDER_RADIUS.md,
    padding: 10,
    gap: 12,
  },
  previewImage: {
    width: 60,
    height: 60,
    borderRadius: 8,
  },
  previewStatus: {
    color: COLORS.success,
    fontSize: 14,
    fontWeight: '600',
    flex: 1,
  },
  removePreview: {
    position: 'absolute',
    top: -10,
    right: -10,
    zIndex: 10,
  },
});
