import React from 'react';
import { View, Text, StyleSheet, Platform } from 'react-native';
import { Picker } from '@react-native-picker/picker';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS } from '../../constants/theme';

interface Option {
  label: string;
  value: string | number;
}

interface FormSelectProps {
  label: string;
  value: string | number;
  onValueChange: (value: string | number) => void;
  options: Option[];
  placeholder?: string;
  error?: string;
  icon?: string;
}

export const FormSelect: React.FC<FormSelectProps> = ({
  label,
  value,
  onValueChange,
  options,
  placeholder = 'Seleccione una opción',
  error,
  icon,
}) => {
  // Asegurar que options siempre sea un array
  const safeOptions = options || [];

  return (
    <View style={styles.container}>
      <Text style={styles.label}>{label}</Text>
      <View style={[
        styles.wrapper,
        value && styles.wrapperFilled,
        error && styles.wrapperError,
      ]}>
        {icon && <Text style={styles.icon}>{icon}</Text>}
        <Picker
          selectedValue={value}
          onValueChange={onValueChange}
          style={styles.picker}
          itemStyle={styles.pickerItem}
          dropdownIconColor={COLORS.primary}
        >
          <Picker.Item
            label={placeholder}
            value=""
            color={Platform.OS === 'android' ? '#666666' : '#999999'}
          />
          {safeOptions.map((option) => (
            <Picker.Item
              key={option.value}
              label={option.label}
              value={option.value}
              color={Platform.OS === 'android' ? '#000000' : '#FFFFFF'}
            />
          ))}
        </Picker>
      </View>
      {error && (
        <View style={styles.errorContainer}>
          <Text style={styles.errorIcon}>⚠️</Text>
          <Text style={styles.errorText}>{error}</Text>
        </View>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    marginBottom: SPACING.lg,
  },
  label: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
    color: COLORS.text.secondary,
    marginBottom: SPACING.sm,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  wrapper: {
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.md,
    borderWidth: 2,
    borderColor: COLORS.border.primary,
    flexDirection: 'row',
    alignItems: 'center',
    overflow: 'hidden',
    ...Platform.select({
      web: {
        minHeight: 50,
      },
    }),
  },
  wrapperFilled: {
    borderColor: COLORS.border.light,
  },
  wrapperError: {
    borderColor: COLORS.error,
  },
  icon: {
    fontSize: 20,
    marginLeft: SPACING.md,
  },
  picker: {
    flex: 1,
    color: '#FFFFFF',
    backgroundColor: 'transparent',
    ...Platform.select({
      web: {
        backgroundColor: 'transparent',
        border: 'none',
        outline: 'none',
        cursor: 'pointer',
        color: '#FFFFFF',
      },
      android: {
        color: '#FFFFFF',
        backgroundColor: 'transparent',
        height: 50,
        marginLeft: -8,
      },
      ios: {
        color: '#FFFFFF',
        height: 50,
      },
    }),
  },
  pickerItem: {
    fontSize: TYPOGRAPHY.fontSize.lg,
    color: COLORS.text.primary,
  },
  errorContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: SPACING.xs,
    paddingHorizontal: SPACING.xs,
  },
  errorIcon: {
    fontSize: 14,
    marginRight: SPACING.xs,
  },
  errorText: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.error,
    flex: 1,
  },
});
