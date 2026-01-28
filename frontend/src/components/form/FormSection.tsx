import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS } from '../../constants/theme';

interface FormSectionProps {
  icon: string;
  title: string;
  children: React.ReactNode;
  onLayout?: (event: any) => void;
}

export const FormSection: React.FC<FormSectionProps> = ({ icon, title, children, onLayout }) => {
  return (
    <View style={styles.section} onLayout={onLayout}>
      <View style={styles.header}>
        <View style={styles.iconContainer}>
          <Ionicons name={icon as any} size={24} color={COLORS.primary} />
        </View>
        <Text style={styles.title}>{title}</Text>
      </View>
      {children}
    </View>
  );
};

const styles = StyleSheet.create({
  section: {
    marginBottom: SPACING.xl,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: SPACING.lg,
  },
  iconContainer: {
    width: 40,
    height: 40,
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.md,
    borderWidth: 1,
    borderColor: COLORS.primary,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: SPACING.md,
  },
  icon: {
    fontSize: 20,
  },
  title: {
    fontSize: TYPOGRAPHY.fontSize.lg,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.primary,
    letterSpacing: 1,
  },
});
