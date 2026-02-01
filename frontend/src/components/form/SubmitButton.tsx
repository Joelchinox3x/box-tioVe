import React from 'react';
import { Text, StyleSheet, TouchableOpacity, ActivityIndicator, Platform, Pressable } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS, SHADOWS } from '../../constants/theme';

interface SubmitButtonProps {
  onPress: () => void;
  isLoading: boolean;
  disabled?: boolean;
  title?: string;
  icon?: string;
  style?: any;
}

export const SubmitButton: React.FC<SubmitButtonProps> = ({
  onPress,
  isLoading,
  disabled = false,
  title = 'INSCRIBIRSE AHORA',
  icon = '🥊',
  style,
}) => {
  const handlePress = () => {
    console.log('⚡ Button pressed in SubmitButton!');
    console.log('🔍 isLoading:', isLoading, 'disabled:', disabled);
    if (!isLoading && !disabled) {
      console.log('✅ Calling onPress handler');
      onPress();
    } else {
      console.log('❌ Button is disabled or loading, not calling onPress');
    }
  };

  // Use Pressable for better web support
  const ButtonComponent = Platform.OS === 'web' ? Pressable : TouchableOpacity;

  return (
    <ButtonComponent
      onPress={handlePress}
      disabled={isLoading || disabled}
      style={({ pressed }) => [
        styles.container,
        style,
        Platform.OS === 'web' && ({
          opacity: pressed ? 0.8 : 1,
          cursor: (isLoading || disabled ? 'not-allowed' : 'pointer') as any,
        } as any)
      ]}
    >
      <LinearGradient
        colors={isLoading
          ? ['#999', '#777', '#999']
          : [COLORS.primary, '#FFC700', COLORS.primary]
        }
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 0 }}
        style={styles.button}
      >
        {isLoading ? (
          <>
            <ActivityIndicator color={COLORS.text.inverse} size="small" />
            <Text style={styles.text}>PROCESANDO...</Text>
          </>
        ) : (
          <>
            <Text style={styles.text}>{title}</Text>
            <Text style={styles.icon}>{icon}</Text>
          </>
        )}
      </LinearGradient>
    </ButtonComponent>
  );
};

const styles = StyleSheet.create({
  container: {
    marginTop: SPACING.xl,
  },
  button: {
    paddingVertical: SPACING.lg,
    borderRadius: BORDER_RADIUS.lg,
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    gap: SPACING.md,
    ...Platform.select({
      ios: SHADOWS.lg,
      android: { ...SHADOWS.lg, elevation: 8 },
      web: {
        boxShadow: '0 4px 8px rgba(255, 215, 0, 0.3)',
        userSelect: 'none',
        WebkitUserSelect: 'none',
        pointerEvents: 'auto',
        zIndex: 1,
      } as any,
    }),
  },
  text: {
    fontSize: TYPOGRAPHY.fontSize.xl,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.inverse,
    letterSpacing: 1.5,
  },
  icon: {
    fontSize: 24,
  },
});
