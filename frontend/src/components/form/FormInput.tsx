import React from 'react';
import { View, Text, StyleSheet, TextInput, Platform } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS, SHADOWS } from '../../constants/theme';

interface FormInputProps {
  label: string;
  value: string;
  onChangeText: (text: string) => void;
  placeholder?: string;
  keyboardType?: 'default' | 'numeric' | 'email-address' | 'phone-pad' | 'decimal-pad';
  autoCapitalize?: 'none' | 'sentences' | 'words' | 'characters';
  multiline?: boolean;
  focused?: boolean;
  onFocus?: () => void;
  onBlur?: () => void;
  icon?: string;
  error?: string;
  isValid?: boolean;
  successMessage?: string;
  maxLength?: number;
  onLayout?: (event: any) => void;
}

export const FormInput: React.FC<FormInputProps> = ({
  label,
  value,
  onChangeText,
  placeholder,
  keyboardType = 'default',
  autoCapitalize = 'words',
  multiline = false,
  focused = false,
  onFocus,
  onBlur,
  icon,
  error,
  isValid,
  successMessage,
  maxLength,
  onLayout,
}) => {
  return (
    <View style={styles.container} onLayout={onLayout}>
      <Text style={styles.label}>{label}</Text>
      <View style={[
        styles.wrapper,
        focused && styles.wrapperFocused,
        !!value && styles.wrapperFilled,
        !!error && styles.wrapperError,
      ]}>
        <TextInput
          style={[
            styles.input,
            multiline && styles.inputMultiline,
            (isValid || !!icon) && { paddingRight: 45 }
          ]}
          value={value}
          onChangeText={onChangeText}
          placeholder={placeholder}
          placeholderTextColor={COLORS.text.tertiary}
          keyboardType={keyboardType}
          autoCapitalize={autoCapitalize}
          multiline={multiline}
          numberOfLines={multiline ? 3 : 1}
          onFocus={onFocus}
          onBlur={onBlur}
          maxLength={maxLength}
        />
        {(isValid || icon) && (
          <View style={styles.iconOverlay}>
            {isValid ? (
              <Ionicons name="checkmark-circle" size={22} color="#2563EB" />
            ) : (
              icon && <Ionicons name={icon as any} size={20} color={COLORS.text.tertiary} />
            )}
          </View>
        )}
      </View>
      {error ? (
        <View style={styles.errorContainer}>
          <Text style={styles.errorIcon}>⚠️</Text>
          <Text style={styles.errorText}>{error}</Text>
        </View>
      ) : successMessage && isValid ? (
        <View style={styles.successContainer}>
          <Ionicons name="checkmark-circle" size={14} color="#2563EB" style={styles.successIcon} />
          <Text style={styles.successText}>{successMessage}</Text>
        </View>
      ) : null}
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
  },
  wrapperFocused: {
    borderColor: COLORS.primary,
    ...Platform.select({
      ios: SHADOWS.lg,
      android: { ...SHADOWS.lg, elevation: 6 },
    }),
  },
  wrapperFilled: {
    borderColor: COLORS.border.light,
  },
  wrapperError: {
    borderColor: COLORS.error,
  },
  input: {
    flex: 1,
    paddingVertical: SPACING.md,
    paddingHorizontal: SPACING.md,
    fontSize: TYPOGRAPHY.fontSize.lg,
    color: COLORS.text.primary,
    fontWeight: TYPOGRAPHY.fontWeight.medium,
  },
  inputMultiline: {
    minHeight: 80,
    textAlignVertical: 'top',
  },
  icon: {
    fontSize: 20,
  },
  iconOverlay: {
    position: 'absolute',
    right: SPACING.md,
    height: '100%',
    justifyContent: 'center',
    alignItems: 'center',
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
  successContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: SPACING.xs,
    paddingHorizontal: SPACING.xs,
  },
  successIcon: {
    marginRight: SPACING.xs,
  },
  successText: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: '#2563EB',
    flex: 1,
    fontWeight: '500',
  },
});
