import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS, SHADOWS } from '../../constants/theme';

export interface TicketType {
  id: string;
  name: string;
  price: number;
  description: string;
  benefits: string[];
  isPopular?: boolean;
  color: string;
  gradientColors: string[];
  icon: keyof typeof Ionicons.glyphMap;
}

interface TicketCardProps {
  ticket: TicketType;
  quantity: number;
  onIncrease: () => void;
  onDecrease: () => void;
}

export const TicketCard: React.FC<TicketCardProps> = ({
  ticket,
  quantity,
  onIncrease,
  onDecrease,
}) => {
  return (
    <View style={styles.container}>
      {ticket.isPopular && (
        <View style={styles.popularBadge}>
          <Text style={styles.popularText}>MÁS POPULAR</Text>
        </View>
      )}

      <LinearGradient
        colors={ticket.gradientColors as [string, string, ...string[]]}
        style={styles.gradient}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
      >
        {/* Header */}
        <View style={styles.header}>
          <View style={styles.iconContainer}>
            <Ionicons name={ticket.icon} size={32} color={COLORS.text.inverse} />
          </View>
          <View style={styles.headerInfo}>
            <Text style={styles.ticketName}>{ticket.name}</Text>
            <Text style={styles.ticketDescription}>{ticket.description}</Text>
          </View>
        </View>

        {/* Precio */}
        <View style={styles.priceContainer}>
          <Text style={styles.currency}>S/</Text>
          <Text style={styles.price}>{ticket.price.toFixed(2)}</Text>
          <Text style={styles.perPerson}>por persona</Text>
        </View>

        {/* Beneficios */}
        <View style={styles.benefitsContainer}>
          {ticket.benefits.map((benefit, index) => (
            <View key={index} style={styles.benefitRow}>
              <Ionicons name="checkmark-circle" size={18} color={COLORS.success} />
              <Text style={styles.benefitText}>{benefit}</Text>
            </View>
          ))}
        </View>

        {/* Separador decorativo */}
        <View style={styles.separator}>
          <View style={styles.separatorDot} />
          <View style={styles.separatorLine} />
          <View style={styles.separatorDot} />
        </View>

        {/* Contador de cantidad */}
        <View style={styles.quantityContainer}>
          <Text style={styles.quantityLabel}>Cantidad</Text>
          <View style={styles.quantityControls}>
            <TouchableOpacity
              onPress={onDecrease}
              style={[styles.quantityButton, quantity === 0 && styles.quantityButtonDisabled]}
              disabled={quantity === 0}
              activeOpacity={0.7}
            >
              <Ionicons name="remove" size={24} color={quantity === 0 ? COLORS.text.tertiary : COLORS.text.inverse} />
            </TouchableOpacity>

            <View style={styles.quantityDisplay}>
              <Text style={styles.quantityNumber}>{quantity}</Text>
            </View>

            <TouchableOpacity
              onPress={onIncrease}
              style={styles.quantityButton}
              activeOpacity={0.7}
            >
              <Ionicons name="add" size={24} color={COLORS.text.inverse} />
            </TouchableOpacity>
          </View>
        </View>

        {/* Total parcial si hay cantidad */}
        {quantity > 0 && (
          <View style={styles.subtotalContainer}>
            <Text style={styles.subtotalLabel}>Subtotal:</Text>
            <Text style={styles.subtotalAmount}>S/ {(ticket.price * quantity).toFixed(2)}</Text>
          </View>
        )}
      </LinearGradient>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    marginBottom: SPACING.lg,
    position: 'relative',
  },
  popularBadge: {
    position: 'absolute',
    top: -8,
    right: 20,
    backgroundColor: COLORS.error,
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.xs,
    borderRadius: BORDER_RADIUS.full,
    zIndex: 10,
    ...SHADOWS.md,
  },
  popularText: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.inverse,
    letterSpacing: 0.5,
  },
  gradient: {
    borderRadius: BORDER_RADIUS.xl,
    padding: SPACING.xl,
    borderWidth: 2,
    borderColor: 'rgba(255, 255, 255, 0.1)',
    ...SHADOWS.lg,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: SPACING.lg,
  },
  iconContainer: {
    width: 56,
    height: 56,
    borderRadius: BORDER_RADIUS.lg,
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: SPACING.md,
  },
  headerInfo: {
    flex: 1,
  },
  ticketName: {
    fontSize: TYPOGRAPHY.fontSize.xxl,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.inverse,
    marginBottom: 4,
  },
  ticketDescription: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: 'rgba(255, 255, 255, 0.9)',
    lineHeight: 18,
  },
  priceContainer: {
    flexDirection: 'row',
    alignItems: 'baseline',
    marginBottom: SPACING.lg,
  },
  currency: {
    fontSize: TYPOGRAPHY.fontSize.lg,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.inverse,
    marginRight: 4,
  },
  price: {
    fontSize: 42,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.inverse,
    marginRight: SPACING.sm,
  },
  perPerson: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: 'rgba(255, 255, 255, 0.8)',
    alignSelf: 'flex-end',
    marginBottom: 8,
  },
  benefitsContainer: {
    marginBottom: SPACING.lg,
    gap: SPACING.sm,
  },
  benefitRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.sm,
  },
  benefitText: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.text.inverse,
    flex: 1,
  },
  separator: {
    flexDirection: 'row',
    alignItems: 'center',
    marginVertical: SPACING.lg,
  },
  separatorDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: 'rgba(255, 255, 255, 0.5)',
  },
  separatorLine: {
    flex: 1,
    height: 1,
    backgroundColor: 'rgba(255, 255, 255, 0.3)',
    marginHorizontal: SPACING.sm,
  },
  quantityContainer: {
    marginBottom: SPACING.md,
  },
  quantityLabel: {
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
    color: COLORS.text.inverse,
    marginBottom: SPACING.sm,
  },
  quantityControls: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.md,
  },
  quantityButton: {
    width: 44,
    height: 44,
    borderRadius: BORDER_RADIUS.lg,
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.3)',
  },
  quantityButtonDisabled: {
    opacity: 0.4,
  },
  quantityDisplay: {
    flex: 1,
    height: 44,
    backgroundColor: 'rgba(255, 255, 255, 0.15)',
    borderRadius: BORDER_RADIUS.lg,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.3)',
  },
  quantityNumber: {
    fontSize: TYPOGRAPHY.fontSize.xl,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.inverse,
  },
  subtotalContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    backgroundColor: 'rgba(255, 255, 255, 0.15)',
    padding: SPACING.md,
    borderRadius: BORDER_RADIUS.lg,
    marginTop: SPACING.sm,
  },
  subtotalLabel: {
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
    color: COLORS.text.inverse,
  },
  subtotalAmount: {
    fontSize: TYPOGRAPHY.fontSize.xl,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.inverse,
  },
});
