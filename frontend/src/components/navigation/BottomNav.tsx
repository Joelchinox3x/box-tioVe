import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Platform } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS, SHADOWS } from '../../constants/theme';

export interface NavItem {
  id: string;
  label: string;
  icon: keyof typeof Ionicons.glyphMap;
  isCenter?: boolean;
}

interface BottomNavProps {
  items: NavItem[];
  activeItem: string;
  onItemPress: (itemId: string) => void;
}

export const BottomNav: React.FC<BottomNavProps> = ({
  items,
  activeItem,
  onItemPress,
}) => {
  return (
    <View style={styles.container}>
      {/* Sombra superior */}
      <View style={styles.topShadow} />

      <View style={styles.navContainer}>
        {items.map((item, index) => {
          const isActive = activeItem === item.id;
          const isCenter = item.isCenter;

          if (isCenter) {
            return (
              <TouchableOpacity
                key={item.id}
                style={styles.centerButtonContainer}
                onPress={() => onItemPress(item.id)}
                activeOpacity={0.8}
              >
                <LinearGradient
                  colors={[COLORS.primary, '#FFA500']}
                  style={styles.centerButton}
                >
                  <View style={styles.centerIconContainer}>
                    <Ionicons
                      name={item.icon}
                      size={32}
                      color={COLORS.text.inverse}
                    />
                  </View>
                </LinearGradient>
                {/* Label debajo del botón */}
                <Text style={styles.centerLabel}>{item.label}</Text>
              </TouchableOpacity>
            );
          }

          return (
            <TouchableOpacity
              key={item.id}
              style={styles.navItem}
              onPress={() => onItemPress(item.id)}
              activeOpacity={0.7}
            >
              <Ionicons
                name={isActive ? item.icon : `${item.icon}-outline` as any}
                size={24}
                color={isActive ? COLORS.primary : COLORS.text.tertiary}
              />
              <Text
                style={[
                  styles.navLabel,
                  isActive && styles.navLabelActive,
                ]}
              >
                {item.label}
              </Text>
            </TouchableOpacity>
          );
        })}
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    position: 'relative',
    backgroundColor: COLORS.background,
    borderTopWidth: 1,
    borderTopColor: COLORS.border.primary,
  },
  topShadow: {
    position: 'absolute',
    top: -10,
    left: 0,
    right: 0,
    height: 10,
    backgroundColor: 'transparent',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: -3 },
    shadowOpacity: 0.3,
    shadowRadius: 4,
    elevation: 8,
  },
  navContainer: {
    flexDirection: 'row',
    alignItems: 'flex-end',
    justifyContent: 'space-around',
    paddingBottom: Platform.OS === 'ios' ? SPACING.lg : SPACING.md,
    paddingTop: SPACING.sm,
    paddingHorizontal: SPACING.md,
    height: 70,
  },
  navItem: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: SPACING.xs,
  },
  navLabel: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    color: COLORS.text.tertiary,
    marginTop: 4,
    fontWeight: TYPOGRAPHY.fontWeight.medium,
  },
  navLabelActive: {
    color: COLORS.primary,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
  },
  centerButtonContainer: {
    alignItems: 'center',
    marginTop: -30,
    marginHorizontal: SPACING.sm,
  },
  centerButton: {
    width: 64,
    height: 64,
    borderRadius: BORDER_RADIUS.full,
    justifyContent: 'center',
    alignItems: 'center',
    ...SHADOWS.lg,
    borderWidth: 4,
    borderColor: COLORS.background,
  },
  centerIconContainer: {
    justifyContent: 'center',
    alignItems: 'center',
  },
  centerLabel: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    color: COLORS.primary,
    marginTop: 6,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
  },
});
