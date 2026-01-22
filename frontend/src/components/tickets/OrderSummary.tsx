import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS, SHADOWS } from '../../constants/theme';

interface OrderItem {
  name: string;
  quantity: number;
  price: number;
}

interface OrderSummaryProps {
  items: OrderItem[];
  serviceFee?: number;
}

export const OrderSummary: React.FC<OrderSummaryProps> = ({
  items,
  serviceFee = 0,
}) => {
  const subtotal = items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
  const total = subtotal + serviceFee;
  const totalTickets = items.reduce((sum, item) => sum + item.quantity, 0);

  if (items.length === 0 || totalTickets === 0) {
    return null;
  }

  return (
    <View style={styles.container}>
      <LinearGradient
        colors={['#1a1a1a', '#0a0a0a']}
        style={styles.gradient}
      >
        {/* Header */}
        <View style={styles.header}>
          <Ionicons name="receipt" size={24} color={COLORS.primary} />
          <Text style={styles.title}>Resumen de Compra</Text>
        </View>

        {/* Items */}
        <View style={styles.itemsContainer}>
          {items.map((item, index) => (
            <View key={index} style={styles.itemRow}>
              <View style={styles.itemInfo}>
                <Text style={styles.itemName}>{item.name}</Text>
                <Text style={styles.itemQuantity}>x {item.quantity}</Text>
              </View>
              <Text style={styles.itemPrice}>S/ {(item.price * item.quantity).toFixed(2)}</Text>
            </View>
          ))}
        </View>

        {/* Separator */}
        <View style={styles.separator} />

        {/* Subtotal */}
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>Subtotal</Text>
          <Text style={styles.summaryValue}>S/ {subtotal.toFixed(2)}</Text>
        </View>

        {/* Service fee */}
        {serviceFee > 0 && (
          <View style={styles.summaryRow}>
            <View style={styles.feeInfo}>
              <Text style={styles.summaryLabel}>Cargo por servicio</Text>
              <Ionicons name="information-circle-outline" size={16} color={COLORS.text.tertiary} />
            </View>
            <Text style={styles.summaryValue}>S/ {serviceFee.toFixed(2)}</Text>
          </View>
        )}

        {/* Separator */}
        <View style={styles.separator} />

        {/* Total */}
        <View style={styles.totalRow}>
          <View>
            <Text style={styles.totalLabel}>Total a Pagar</Text>
            <Text style={styles.totalTickets}>{totalTickets} entrada{totalTickets > 1 ? 's' : ''}</Text>
          </View>
          <Text style={styles.totalAmount}>S/ {total.toFixed(2)}</Text>
        </View>

        {/* Info adicional */}
        <View style={styles.infoBox}>
          <Ionicons name="shield-checkmark" size={18} color={COLORS.success} />
          <Text style={styles.infoText}>
            Compra segura • Confirmación inmediata
          </Text>
        </View>
      </LinearGradient>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    marginVertical: SPACING.lg,
  },
  gradient: {
    borderRadius: BORDER_RADIUS.xl,
    padding: SPACING.xl,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
    ...SHADOWS.md,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: SPACING.lg,
    gap: SPACING.sm,
  },
  title: {
    fontSize: TYPOGRAPHY.fontSize.xl,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
  },
  itemsContainer: {
    gap: SPACING.md,
  },
  itemRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  itemInfo: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.sm,
  },
  itemName: {
    fontSize: TYPOGRAPHY.fontSize.md,
    color: COLORS.text.primary,
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
  },
  itemQuantity: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.text.tertiary,
  },
  itemPrice: {
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
    color: COLORS.text.primary,
  },
  separator: {
    height: 1,
    backgroundColor: COLORS.border.primary,
    marginVertical: SPACING.md,
  },
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: SPACING.sm,
  },
  summaryLabel: {
    fontSize: TYPOGRAPHY.fontSize.md,
    color: COLORS.text.secondary,
  },
  summaryValue: {
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
    color: COLORS.text.primary,
  },
  feeInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
  },
  totalRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    backgroundColor: COLORS.surface,
    padding: SPACING.md,
    borderRadius: BORDER_RADIUS.lg,
    marginTop: SPACING.sm,
  },
  totalLabel: {
    fontSize: TYPOGRAPHY.fontSize.lg,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.primary,
    marginBottom: 2,
  },
  totalTickets: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    color: COLORS.text.tertiary,
  },
  totalAmount: {
    fontSize: 28,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.primary,
  },
  infoBox: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.sm,
    marginTop: SPACING.lg,
    padding: SPACING.md,
    backgroundColor: 'rgba(16, 185, 129, 0.1)',
    borderRadius: BORDER_RADIUS.md,
    borderWidth: 1,
    borderColor: 'rgba(16, 185, 129, 0.2)',
  },
  infoText: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.text.secondary,
  },
});
