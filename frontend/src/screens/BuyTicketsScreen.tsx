import React, { useState } from 'react';
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
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import * as Haptics from 'expo-haptics';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS } from '../constants/theme';
import { ScreenHeader } from '../components/common/ScreenHeader';
import { TicketCard, TicketType, OrderSummary } from '../components/tickets';

interface BuyTicketsScreenProps {
  navigation: any;
}

export default function BuyTicketsScreen({ navigation }: BuyTicketsScreenProps) {
  // Tipos de entradas disponibles
  const ticketTypes: TicketType[] = [
    {
      id: 'general',
      name: 'Entrada General',
      price: 10.00,
      description: 'Acceso al evento completo',
      benefits: [
        'Acceso a todas las peleas',
        'Asiento en zona general',
        'Ingreso desde 6:00 PM',
        'Certificado digital de asistencia',
      ],
      isPopular: true,
      color: COLORS.primary,
      gradientColors: ['#2a2a2a', '#1a1a1a'],
      icon: 'ticket',
    },
    {
      id: 'vip',
      name: 'Entrada VIP',
      price: 150.00,
      description: 'Experiencia premium exclusiva',
      benefits: [
        'Acceso prioritario al evento',
        'Asientos preferenciales en ringside',
        'Meet & Greet con peleadores',
        'Bebida de cortesía',
        'Acceso a zona VIP lounge',
        'Foto oficial del evento',
      ],
      color: '#FFD700',
      gradientColors: [COLORS.primary, '#FFA500'],
      icon: 'star',
    },
  ];

  // Estados
  const [quantities, setQuantities] = useState<{ [key: string]: number }>({
    general: 0,
    vip: 0,
  });

  const [formData, setFormData] = useState({
    nombre: '',
    email: '',
    telefono: '',
    dni: '',
  });

  const [errors, setErrors] = useState<{ [key: string]: string }>({});

  // Handlers de cantidad
  const handleIncrease = (ticketId: string) => {
    Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
    setQuantities(prev => ({
      ...prev,
      [ticketId]: (prev[ticketId] || 0) + 1,
    }));
  };

  const handleDecrease = (ticketId: string) => {
    if (quantities[ticketId] > 0) {
      Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
      setQuantities(prev => ({
        ...prev,
        [ticketId]: prev[ticketId] - 1,
      }));
    }
  };

  // Validación
  const validateForm = (): boolean => {
    const newErrors: { [key: string]: string } = {};

    if (!formData.nombre.trim()) {
      newErrors.nombre = 'El nombre es requerido';
    }

    if (!formData.email.trim()) {
      newErrors.email = 'El email es requerido';
    } else if (!/\S+@\S+\.\S+/.test(formData.email)) {
      newErrors.email = 'Email inválido';
    }

    if (!formData.telefono.trim()) {
      newErrors.telefono = 'El teléfono es requerido';
    } else if (!/^\d{9}$/.test(formData.telefono.replace(/\s/g, ''))) {
      newErrors.telefono = 'Teléfono debe tener 9 dígitos';
    }

    if (!formData.dni.trim()) {
      newErrors.dni = 'El DNI es requerido';
    } else if (!/^\d{8}$/.test(formData.dni)) {
      newErrors.dni = 'DNI debe tener 8 dígitos';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  // Procesar compra
  const handlePurchase = () => {
    const totalTickets = Object.values(quantities).reduce((sum, q) => sum + q, 0);

    if (totalTickets === 0) {
      Alert.alert('Atención', 'Debes seleccionar al menos una entrada');
      return;
    }

    if (!validateForm()) {
      Alert.alert('Error', 'Por favor completa todos los campos correctamente');
      return;
    }

    Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);

    Alert.alert(
      'Compra Exitosa',
      `Se ha registrado tu compra de ${totalTickets} entrada${totalTickets > 1 ? 's' : ''}. Recibirás la confirmación por email.`,
      [
        {
          text: 'Ver mis entradas',
          onPress: () => navigation.navigate('Tickets'),
        },
        {
          text: 'OK',
          onPress: () => navigation.navigate('Home'),
        },
      ]
    );
  };

  // Calcular resumen
  const orderItems = ticketTypes
    .filter(ticket => quantities[ticket.id] > 0)
    .map(ticket => ({
      name: ticket.name,
      quantity: quantities[ticket.id],
      price: ticket.price,
    }));

  const totalTickets = Object.values(quantities).reduce((sum, q) => sum + q, 0);

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor="#000" />
      <ScreenHeader title="COMPRAR ENTRADAS" />

      <ScrollView
        style={styles.scrollView}
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
      >
        {/* Información del evento */}
        <View style={styles.eventInfo}>
          <View style={styles.eventBadge}>
            <Ionicons name="calendar" size={18} color={COLORS.primary} />
            <Text style={styles.eventText}>Box TioVE - El Jab Dorado</Text>
          </View>
          <Text style={styles.eventSubtext}>Sábado 22 de Febrero • 9:00 AM</Text>
        </View>

        {/* Tarjetas de entradas */}
        <View style={styles.ticketsSection}>
          <Text style={styles.sectionTitle}>Selecciona tus entradas</Text>
          {ticketTypes.map((ticket) => (
            <TicketCard
              key={ticket.id}
              ticket={ticket}
              quantity={quantities[ticket.id] || 0}
              onIncrease={() => handleIncrease(ticket.id)}
              onDecrease={() => handleDecrease(ticket.id)}
            />
          ))}
        </View>

        {/* Resumen de orden */}
        <OrderSummary items={orderItems} serviceFee={5.00} />

        {/* Formulario de datos */}
        {totalTickets > 0 && (
          <View style={styles.formSection}>
            <Text style={styles.sectionTitle}>Datos del comprador</Text>

            <View style={styles.formGroup}>
              <Text style={styles.label}>Nombre completo *</Text>
              <TextInput
                style={[styles.input, errors.nombre && styles.inputError]}
                placeholder="Ej: Juan Pérez"
                placeholderTextColor={COLORS.text.tertiary}
                value={formData.nombre}
                onChangeText={(text) => {
                  setFormData({ ...formData, nombre: text });
                  setErrors({ ...errors, nombre: '' });
                }}
              />
              {errors.nombre && <Text style={styles.errorText}>{errors.nombre}</Text>}
            </View>

            <View style={styles.formGroup}>
              <Text style={styles.label}>Email *</Text>
              <TextInput
                style={[styles.input, errors.email && styles.inputError]}
                placeholder="tu@email.com"
                placeholderTextColor={COLORS.text.tertiary}
                value={formData.email}
                onChangeText={(text) => {
                  setFormData({ ...formData, email: text });
                  setErrors({ ...errors, email: '' });
                }}
                keyboardType="email-address"
                autoCapitalize="none"
              />
              {errors.email && <Text style={styles.errorText}>{errors.email}</Text>}
            </View>

            <View style={styles.formRow}>
              <View style={[styles.formGroup, styles.formGroupHalf]}>
                <Text style={styles.label}>Teléfono *</Text>
                <TextInput
                  style={[styles.input, errors.telefono && styles.inputError]}
                  placeholder="999 999 999"
                  placeholderTextColor={COLORS.text.tertiary}
                  value={formData.telefono}
                  onChangeText={(text) => {
                    setFormData({ ...formData, telefono: text });
                    setErrors({ ...errors, telefono: '' });
                  }}
                  keyboardType="phone-pad"
                  maxLength={9}
                />
                {errors.telefono && <Text style={styles.errorText}>{errors.telefono}</Text>}
              </View>

              <View style={[styles.formGroup, styles.formGroupHalf]}>
                <Text style={styles.label}>DNI *</Text>
                <TextInput
                  style={[styles.input, errors.dni && styles.inputError]}
                  placeholder="12345678"
                  placeholderTextColor={COLORS.text.tertiary}
                  value={formData.dni}
                  onChangeText={(text) => {
                    setFormData({ ...formData, dni: text });
                    setErrors({ ...errors, dni: '' });
                  }}
                  keyboardType="number-pad"
                  maxLength={8}
                />
                {errors.dni && <Text style={styles.errorText}>{errors.dni}</Text>}
              </View>
            </View>
          </View>
        )}

        {/* Botón de compra */}
        {totalTickets > 0 && (
          <TouchableOpacity
            style={styles.purchaseButtonContainer}
            onPress={handlePurchase}
            activeOpacity={0.9}
          >
            <LinearGradient
              colors={[COLORS.primary, '#FFA500']}
              style={styles.purchaseButton}
              start={{ x: 0, y: 0 }}
              end={{ x: 1, y: 1 }}
            >
              <Ionicons name="card" size={24} color={COLORS.text.inverse} />
              <Text style={styles.purchaseButtonText}>
                Proceder al Pago
              </Text>
              <View style={styles.purchaseButtonBadge}>
                <Text style={styles.purchaseButtonBadgeText}>{totalTickets}</Text>
              </View>
            </LinearGradient>
          </TouchableOpacity>
        )}

        {/* Espacio inferior */}
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
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: SPACING.lg,
    paddingVertical: SPACING.md,
    borderBottomWidth: 1,
    borderBottomColor: COLORS.border.primary,
  },
  backButton: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: COLORS.surface,
    justifyContent: 'center',
    alignItems: 'center',
  },
  headerTitle: {
    fontSize: TYPOGRAPHY.fontSize.xl,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
  },
  placeholder: {
    width: 40,
  },
  scrollView: {
    flex: 1,
  },
  scrollContent: {
    paddingHorizontal: SPACING.lg,
    paddingBottom: SPACING.xxl, // Padding extra para evitar que el navbar tape el contenido
  },
  eventInfo: {
    marginTop: SPACING.lg,
    marginBottom: SPACING.xl,
    padding: SPACING.lg,
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.lg,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
  },
  eventBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.sm,
    marginBottom: SPACING.xs,
  },
  eventText: {
    fontSize: TYPOGRAPHY.fontSize.lg,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
  },
  eventSubtext: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.text.tertiary,
    marginLeft: 26,
  },
  ticketsSection: {
    marginBottom: SPACING.lg,
  },
  sectionTitle: {
    fontSize: TYPOGRAPHY.fontSize.xl,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
    marginBottom: SPACING.lg,
  },
  formSection: {
    marginTop: SPACING.xl,
  },
  formGroup: {
    marginBottom: SPACING.lg,
  },
  formRow: {
    flexDirection: 'row',
    gap: SPACING.md,
  },
  formGroupHalf: {
    flex: 1,
  },
  label: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
    color: COLORS.text.secondary,
    marginBottom: SPACING.sm,
  },
  input: {
    backgroundColor: COLORS.surface,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
    borderRadius: BORDER_RADIUS.lg,
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.md,
    fontSize: TYPOGRAPHY.fontSize.md,
    color: COLORS.text.primary,
  },
  inputError: {
    borderColor: COLORS.error,
  },
  errorText: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    color: COLORS.error,
    marginTop: SPACING.xs,
  },
  purchaseButtonContainer: {
    marginTop: SPACING.xl,
    marginBottom: SPACING.lg,
  },
  purchaseButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: SPACING.lg,
    borderRadius: BORDER_RADIUS.lg,
    gap: SPACING.sm,
    position: 'relative',
  },
  purchaseButtonText: {
    fontSize: TYPOGRAPHY.fontSize.lg,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.inverse,
    letterSpacing: 0.5,
  },
  purchaseButtonBadge: {
    position: 'absolute',
    top: -8,
    right: 20,
    backgroundColor: COLORS.error,
    width: 28,
    height: 28,
    borderRadius: 14,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 2,
    borderColor: COLORS.background,
  },
  purchaseButtonBadgeText: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.inverse,
  },
  bottomSpacer: {
    height: 150, // Espacio extra para el navbar inferior
  },
});
