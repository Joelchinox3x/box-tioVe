import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ImageBackground, Platform } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS, SHADOWS } from '../../constants/theme';
import { createTextShadow } from '../../utils/shadows';

interface HeaderProps {
  eventTitle?: string;
  isLive?: boolean;
  onNotificationPress?: () => void;
  onProfilePress?: () => void;
  userRole?: 'admin' | 'usuario' | 'peleador';
}

export const Header: React.FC<HeaderProps> = ({
  eventTitle = 'El Jab Dorado',
  isLive = true,
  onNotificationPress,
  onProfilePress,
  userRole,
}) => {
  return (
    <View style={styles.outerContainer}>
      <ImageBackground
        source={require('../../../assets/el_jab_dorado_hero_bg.png')}
        style={styles.backgroundImage}
        resizeMode="cover"
      >
        <LinearGradient
          colors={['rgba(0,0,0,0.4)', 'rgba(0,0,0,0.8)']}
          style={styles.container}
        >
          {/* Logo y título del evento */}
          <View style={styles.leftSection}>
            <View style={styles.logoContainer}>
              <Text style={styles.brandText}>Box TioVE</Text>
            </View>
            <View style={styles.titleContainer}>
              {isLive && (
                <View style={styles.liveBadge}>
                  <View style={styles.liveDot} />
                  <Text style={styles.liveText}>EL RING DE LOS CAMPEONES</Text>
                </View>
              )}
              <Text style={styles.eventTitle} numberOfLines={1}>
                {eventTitle}
              </Text>
            </View>
          </View>

          {/* Acciones derecha */}
          <View style={styles.rightSection}>
            <TouchableOpacity
              style={styles.iconButton}
              onPress={onNotificationPress}
              activeOpacity={0.7}
            >
              <Ionicons name="notifications-outline" size={24} color="#FFF" />
              <View style={styles.notificationBadge}>
                <View style={styles.notificationDot} />
              </View>
            </TouchableOpacity>

            <TouchableOpacity
              style={styles.profileButton}
              onPress={onProfilePress}
              activeOpacity={0.7}
            >
              <Ionicons name="person" size={20} color="#000" />
              {userRole === 'admin' && (
                <Text style={styles.roleText}>Admin</Text>
              )}
            </TouchableOpacity>
          </View>
        </LinearGradient>
      </ImageBackground>
    </View>
  );
};

const styles = StyleSheet.create({
  outerContainer: {
    height: 90,
    backgroundColor: '#000',
    overflow: 'hidden',
    borderBottomWidth: 1,
    borderBottomColor: 'rgba(255, 215, 0, 0.3)',
  },
  backgroundImage: {
    flex: 1,
    width: '100%',
  },
  container: {
    flex: 1,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: SPACING.lg,
    paddingTop: Platform.OS === 'ios' ? 10 : 0,
  },
  leftSection: {
    flexDirection: 'row',
    alignItems: 'center',
    flex: 1,
  },
  logoContainer: {
    paddingHorizontal: SPACING.sm,
    paddingVertical: 4,
    borderRadius: BORDER_RADIUS.sm,
    backgroundColor: 'rgba(255, 215, 0, 0.15)',
    borderWidth: 1,
    borderColor: 'rgba(255, 215, 0, 0.3)',
    marginRight: SPACING.md,
  },
  brandText: {
    color: COLORS.primary,
    fontSize: 10,
    fontWeight: '900',
    letterSpacing: 0.5,
  },
  titleContainer: {
    flex: 1,
  },
  liveBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 2,
  },
  liveDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
    backgroundColor: COLORS.error,
    marginRight: 6,
  },
  liveText: {
    fontSize: 8,
    fontWeight: '900',
    color: '#FFF',
    letterSpacing: 1,
  },
  eventTitle: {
    fontSize: 18,
    fontWeight: '900',
    color: '#FFF',
    letterSpacing: 0.5,
    ...createTextShadow('rgba(0, 0, 0, 0.5)', 1, 1, 3),
  },
  rightSection: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: SPACING.sm,
  },
  iconButton: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: 'rgba(255, 255, 255, 0.1)',
    justifyContent: 'center',
    alignItems: 'center',
    position: 'relative',
  },
  notificationBadge: {
    position: 'absolute',
    top: 10,
    right: 10,
  },
  notificationDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: COLORS.error,
    borderWidth: 2,
    borderColor: '#000',
  },
  profileButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.primary,
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.sm,
    borderRadius: BORDER_RADIUS.full,
    gap: SPACING.xs,
  },
  roleText: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: '#000',
  },
});
