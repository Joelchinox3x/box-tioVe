import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  SafeAreaView,
  StatusBar,
  TouchableOpacity,
  TextInput,
  Alert,
  ActivityIndicator,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import * as Haptics from 'expo-haptics';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS } from '../constants/theme';
import { ScreenHeader } from '../components/common/ScreenHeader';
import boletosService from '../services/boletosService';
import type { TipoBoleto, ComprarBoletoRequest } from '../types';

interface BuyTicketsScreenProps {
  navigation: any;
  route: any;
}

export default function BuyTicketsScreen({ navigation, route }: BuyTicketsScreenProps) {
  const eventoId = route.params?.eventoId || 1; // ID del evento

  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [tiposBoleto, setTiposBoleto] = useState<TipoBoleto[]>([]);
  const [selectedTipo, setSelectedTipo] = useState<TipoBoleto | null>(null);
  const [cantidad, setCantidad] = useState(1);

  const [formData, setFormData] = useState({
    nombres_apellidos: '',
    telefono: '',
    dni: '',
    metodo_pago: 'yape' as 'yape' | 'transferencia' | 'efectivo',
  });

  const [errors, setErrors] = useState<{ [key: string]: string }>({});

  useEffect(() => {
    loadTiposBoleto();
  }, [eventoId]);

  const loadTiposBoleto = async () => {
    try {
      const tipos = await boletosService.getTiposDisponibles(eventoId);
      setTiposBoleto(tipos);
      if (tipos.length > 0) {
        setSelectedTipo(tipos[0]);
      }
    } catch (error) {
      console.error('Error cargando tipos de boleto:', error);
      Alert.alert('Error', 'No se pudieron cargar los tipos de boleto');
    } finally {
      setLoading(false);
    }
  };

  const handleIncrease = () => {
    if (!selectedTipo) return;
    if (cantidad < selectedTipo.cantidad_disponible && cantidad < 10) {
      Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
      setCantidad(cantidad + 1);
    }
  };

  const handleDecrease = () => {
    if (cantidad > 1) {
      Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
      setCantidad(cantidad - 1);
    }
  };

  const validateForm = (): boolean => {
    const newErrors: { [key: string]: string } = {};

    if (!formData.nombres_apellidos.trim()) {
      newErrors.nombres_apellidos = 'El nombre completo es requerido';
    }

    if (!formData.telefono.trim()) {
      newErrors.telefono = 'El teléfono es requerido';
    } else if (!/^9\d{8}$/.test(formData.telefono.replace(/\s/g, ''))) {
      newErrors.telefono = 'Teléfono debe empezar con 9 y tener 9 dígitos';
    }

    if (!formData.dni.trim()) {
      newErrors.dni = 'El DNI es requerido';
    } else if (!/^\d{8}$/.test(formData.dni)) {
      newErrors.dni = 'DNI debe tener 8 dígitos';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handlePurchase = async () => {
    if (!selectedTipo) {
      Alert.alert('Error', 'Selecciona un tipo de boleto');
      return;
    }

    if (!validateForm()) {
      Alert.alert('Error', 'Por favor completa todos los campos correctamente');
      return;
    }

    setSubmitting(true);

    try {
      const request: ComprarBoletoRequest = {
        tipo_boleto_id: selectedTipo.id,
        comprador_nombres_apellidos: formData.nombres_apellidos,
        comprador_telefono: formData.telefono.replace(/\s/g, ''),
        comprador_dni: formData.dni,
        cantidad: cantidad,
        metodo_pago: formData.metodo_pago,
      };

      const response = await boletosService.comprarBoleto(request);

      Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);

      Alert.alert(
        '¡Compra Exitosa!',
        response.message || 'Tu boleto está pendiente de validación de pago',
        [
          {
            text: 'Ver mis boletos',
            onPress: () => navigation.navigate('Profile'),
          },
          { text: 'OK' },
        ]
      );

      // Limpiar formulario
      setFormData({
        nombres_apellidos: '',
        telefono: '',
        dni: '',
        metodo_pago: 'yape',
      });
      setCantidad(1);
      loadTiposBoleto(); // Recargar para actualizar disponibilidad

    } catch (error: any) {
      console.error('Error al comprar boleto:', error);
      Alert.alert('Error', error.response?.data?.message || 'No se pudo procesar la compra');
    } finally {
      setSubmitting(false);
    }
  };

  const calculateTotal = () => {
    return selectedTipo ? selectedTipo.precio * cantidad : 0;
  };

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color={COLORS.primary} />
        <Text style={styles.loadingText}>Cargando boletos...</Text>
      </View>
    );
  }

  if (tiposBoleto.length === 0) {
    return (
      <SafeAreaView style={styles.container}>
        <ScreenHeader
          title="Comprar Boletos"
          onBack={() => navigation.goBack()}
        />
        <View style={styles.emptyContainer}>
          <Ionicons name="ticket-outline" size={80} color={COLORS.text.tertiary} />
          <Text style={styles.emptyText}>No hay boletos disponibles</Text>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="light-content" />
      <ScreenHeader
        title="Comprar Boletos"
        onBack={() => navigation.goBack()}
      />

      <ScrollView style={styles.scrollView} showsVerticalScrollIndicator={false}>
        {/* Selector de tipo de boleto */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Tipo de Boleto</Text>
          {tiposBoleto.map((tipo) => (
            <TouchableOpacity
              key={tipo.id}
              style={[
                styles.ticketCard,
                selectedTipo?.id === tipo.id && styles.ticketCardSelected,
              ]}
              onPress={() => {
                setSelectedTipo(tipo);
                Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
              }}
            >
              <View style={styles.ticketHeader}>
                <View style={[styles.colorDot, { backgroundColor: tipo.color_hex }]} />
                <Text style={styles.ticketName}>{tipo.nombre}</Text>
                {selectedTipo?.id === tipo.id && (
                  <Ionicons name="checkmark-circle" size={24} color={COLORS.success} />
                )}
              </View>
              <Text style={styles.ticketPrice}>S/ {tipo.precio.toFixed(2)}</Text>
              {tipo.descripcion && (
                <Text style={styles.ticketDescription}>{tipo.descripcion}</Text>
              )}
              <Text style={styles.ticketAvailability}>
                {tipo.cantidad_disponible} disponibles
              </Text>
            </TouchableOpacity>
          ))}
        </View>

        {/* Cantidad */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Cantidad</Text>
          <View style={styles.quantityControl}>
            <TouchableOpacity
              style={styles.quantityButton}
              onPress={handleDecrease}
              disabled={cantidad <= 1}
            >
              <Ionicons name="remove" size={24} color={COLORS.text.primary} />
            </TouchableOpacity>
            <Text style={styles.quantityText}>{cantidad}</Text>
            <TouchableOpacity
              style={styles.quantityButton}
              onPress={handleIncrease}
              disabled={!selectedTipo || cantidad >= selectedTipo.cantidad_disponible}
            >
              <Ionicons name="add" size={24} color={COLORS.text.primary} />
            </TouchableOpacity>
          </View>
        </View>

        {/* Formulario */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Datos del Comprador</Text>

          <View style={styles.inputGroup}>
            <Text style={styles.label}>Nombres y Apellidos</Text>
            <TextInput
              style={[styles.input, errors.nombres_apellidos && styles.inputError]}
              value={formData.nombres_apellidos}
              onChangeText={(text) => {
                setFormData({ ...formData, nombres_apellidos: text });
                setErrors({ ...errors, nombres_apellidos: '' });
              }}
              placeholder="Ej: Juan Pérez López"
              placeholderTextColor={COLORS.text.tertiary}
            />
            {errors.nombres_apellidos && (
              <Text style={styles.errorText}>{errors.nombres_apellidos}</Text>
            )}
          </View>

          <View style={styles.inputGroup}>
            <Text style={styles.label}>DNI</Text>
            <TextInput
              style={[styles.input, errors.dni && styles.inputError]}
              value={formData.dni}
              onChangeText={(text) => {
                setFormData({ ...formData, dni: text.replace(/\D/g, '') });
                setErrors({ ...errors, dni: '' });
              }}
              placeholder="8 dígitos"
              keyboardType="numeric"
              maxLength={8}
              placeholderTextColor={COLORS.text.tertiary}
            />
            {errors.dni && <Text style={styles.errorText}>{errors.dni}</Text>}
          </View>

          <View style={styles.inputGroup}>
            <Text style={styles.label}>Teléfono</Text>
            <TextInput
              style={[styles.input, errors.telefono && styles.inputError]}
              value={formData.telefono}
              onChangeText={(text) => {
                setFormData({ ...formData, telefono: text.replace(/\D/g, '') });
                setErrors({ ...errors, telefono: '' });
              }}
              placeholder="9XXXXXXXX"
              keyboardType="phone-pad"
              maxLength={9}
              placeholderTextColor={COLORS.text.tertiary}
            />
            {errors.telefono && <Text style={styles.errorText}>{errors.telefono}</Text>}
          </View>

          <View style={styles.inputGroup}>
            <Text style={styles.label}>Método de Pago</Text>
            <View style={styles.paymentMethods}>
              {(['yape', 'transferencia', 'efectivo'] as const).map((method) => (
                <TouchableOpacity
                  key={method}
                  style={[
                    styles.paymentMethod,
                    formData.metodo_pago === method && styles.paymentMethodSelected,
                  ]}
                  onPress={() => {
                    setFormData({ ...formData, metodo_pago: method });
                    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
                  }}
                >
                  <Text
                    style={[
                      styles.paymentMethodText,
                      formData.metodo_pago === method && styles.paymentMethodTextSelected,
                    ]}
                  >
                    {method.charAt(0).toUpperCase() + method.slice(1)}
                  </Text>
                </TouchableOpacity>
              ))}
            </View>
          </View>
        </View>

        {/* Resumen */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Resumen</Text>
          <View style={styles.summaryCard}>
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>Boleto:</Text>
              <Text style={styles.summaryValue}>{selectedTipo?.nombre}</Text>
            </View>
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>Cantidad:</Text>
              <Text style={styles.summaryValue}>{cantidad}</Text>
            </View>
            <View style={styles.summaryRow}>
              <Text style={styles.summaryLabel}>Precio unitario:</Text>
              <Text style={styles.summaryValue}>S/ {selectedTipo?.precio.toFixed(2)}</Text>
            </View>
            <View style={[styles.summaryRow, styles.summaryTotal]}>
              <Text style={styles.totalLabel}>Total:</Text>
              <Text style={styles.totalValue}>S/ {calculateTotal().toFixed(2)}</Text>
            </View>
          </View>
        </View>
      </ScrollView>

      {/* Botón de compra */}
      <View style={styles.footer}>
        <TouchableOpacity
          style={[styles.purchaseButton, submitting && styles.purchaseButtonDisabled]}
          onPress={handlePurchase}
          disabled={submitting}
        >
          <LinearGradient
            colors={[COLORS.primary, '#FFA500']}
            style={styles.purchaseGradient}
          >
            {submitting ? (
              <ActivityIndicator color="#000" />
            ) : (
              <>
                <Ionicons name="cart" size={24} color="#000" />
                <Text style={styles.purchaseButtonText}>
                  Comprar por S/ {calculateTotal().toFixed(2)}
                </Text>
              </>
            )}
          </LinearGradient>
        </TouchableOpacity>
      </View>
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
  loadingText: {
    marginTop: SPACING.md,
    fontSize: TYPOGRAPHY.fontSize.md,
    color: COLORS.text.secondary,
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: SPACING.xl,
  },
  emptyText: {
    marginTop: SPACING.lg,
    fontSize: TYPOGRAPHY.fontSize.lg,
    color: COLORS.text.secondary,
    textAlign: 'center',
  },
  scrollView: {
    flex: 1,
  },
  section: {
    padding: SPACING.lg,
  },
  sectionTitle: {
    fontSize: TYPOGRAPHY.fontSize.lg,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
    marginBottom: SPACING.md,
  },
  ticketCard: {
    backgroundColor: COLORS.surface,
    padding: SPACING.lg,
    borderRadius: BORDER_RADIUS.lg,
    marginBottom: SPACING.md,
    borderWidth: 2,
    borderColor: 'transparent',
  },
  ticketCardSelected: {
    borderColor: COLORS.primary,
  },
  ticketHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: SPACING.sm,
  },
  colorDot: {
    width: 12,
    height: 12,
    borderRadius: 6,
    marginRight: SPACING.sm,
  },
  ticketName: {
    flex: 1,
    fontSize: TYPOGRAPHY.fontSize.lg,
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
    color: COLORS.text.primary,
  },
  ticketPrice: {
    fontSize: TYPOGRAPHY.fontSize.xxl,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.primary,
    marginBottom: SPACING.xs,
  },
  ticketDescription: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.text.secondary,
    marginBottom: SPACING.xs,
  },
  ticketAvailability: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    color: COLORS.text.tertiary,
  },
  quantityControl: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.lg,
    padding: SPACING.md,
  },
  quantityButton: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: COLORS.background,
    justifyContent: 'center',
    alignItems: 'center',
  },
  quantityText: {
    fontSize: TYPOGRAPHY.fontSize.xxxl,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
    marginHorizontal: SPACING.xl,
    minWidth: 60,
    textAlign: 'center',
  },
  inputGroup: {
    marginBottom: SPACING.lg,
  },
  label: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
    color: COLORS.text.secondary,
    marginBottom: SPACING.xs,
  },
  input: {
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.md,
    padding: SPACING.md,
    fontSize: TYPOGRAPHY.fontSize.md,
    color: COLORS.text.primary,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
  },
  inputError: {
    borderColor: COLORS.error,
  },
  errorText: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    color: COLORS.error,
    marginTop: SPACING.xs,
  },
  paymentMethods: {
    flexDirection: 'row',
    gap: SPACING.sm,
  },
  paymentMethod: {
    flex: 1,
    padding: SPACING.md,
    borderRadius: BORDER_RADIUS.md,
    backgroundColor: COLORS.surface,
    borderWidth: 2,
    borderColor: COLORS.border.primary,
    alignItems: 'center',
  },
  paymentMethodSelected: {
    borderColor: COLORS.primary,
    backgroundColor: COLORS.primary + '20',
  },
  paymentMethodText: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.text.secondary,
  },
  paymentMethodTextSelected: {
    color: COLORS.primary,
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
  },
  summaryCard: {
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.lg,
    padding: SPACING.lg,
  },
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: SPACING.sm,
  },
  summaryLabel: {
    fontSize: TYPOGRAPHY.fontSize.md,
    color: COLORS.text.secondary,
  },
  summaryValue: {
    fontSize: TYPOGRAPHY.fontSize.md,
    color: COLORS.text.primary,
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
  },
  summaryTotal: {
    marginTop: SPACING.md,
    paddingTop: SPACING.md,
    borderTopWidth: 1,
    borderTopColor: COLORS.border.primary,
    marginBottom: 0,
  },
  totalLabel: {
    fontSize: TYPOGRAPHY.fontSize.lg,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
  },
  totalValue: {
    fontSize: TYPOGRAPHY.fontSize.xxl,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.primary,
  },
  footer: {
    padding: SPACING.lg,
    backgroundColor: COLORS.background,
    borderTopWidth: 1,
    borderTopColor: COLORS.border.primary,
  },
  purchaseButton: {
    borderRadius: BORDER_RADIUS.xl,
    overflow: 'hidden',
  },
  purchaseButtonDisabled: {
    opacity: 0.6,
  },
  purchaseGradient: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    padding: SPACING.lg,
    gap: SPACING.sm,
  },
  purchaseButtonText: {
    fontSize: TYPOGRAPHY.fontSize.lg,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: '#000',
  },
});
