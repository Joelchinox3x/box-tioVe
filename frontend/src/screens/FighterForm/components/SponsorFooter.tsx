import React, { useEffect, useRef, useState } from 'react';
import { View, Text, StyleSheet, FlatList, Dimensions, Animated, Platform } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS } from '../../../constants/theme';

const { width: SCREEN_WIDTH } = Dimensions.get('window');
const CARD_WIDTH = SCREEN_WIDTH * 0.48;
const SPACING_ITEM = SPACING.sm;

const COLLABORATORS = [
  {
    id: 'antigravity',
    name: 'Antigravity',
    tagline: 'by Google DeepMind',
    badge: '🚀 NEXT GEN AI',
    icon: 'hardware-chip' as const,
    colors: ['rgba(255, 215, 0, 0.2)', 'rgba(0, 0, 0, 0.8)'] as const,
    textColor: COLORS.primary,
  },
  {
    id: 'joel',
    name: 'Joel Quispe',
    tagline: 'Senior Programmer',
    badge: '💻 CORE DEV',
    icon: 'code-slash' as const,
    colors: ['rgba(0, 150, 255, 0.2)', 'rgba(0, 0, 0, 0.8)'] as const,
    textColor: '#00B4FF',
  },
  {
    id: 'claude',
    name: 'Claude',
    tagline: 'by Anthropic',
    badge: '🧠 AI POWERED',
    icon: 'infinite' as const,
    colors: ['rgba(217, 119, 87, 0.2)', 'rgba(0, 0, 0, 0.8)'] as const,
    textColor: '#D97757',
  },
];

export const SponsorFooter: React.FC = () => {
  const flatListRef = useRef<FlatList>(null);
  const [activeIndex, setActiveIndex] = useState(0);

  useEffect(() => {
    const interval = setInterval(() => {
      if (COLLABORATORS.length > 0) {
        const nextIndex = (activeIndex + 1) % COLLABORATORS.length;
        setActiveIndex(nextIndex);
        flatListRef.current?.scrollToIndex({
          index: nextIndex,
          animated: true,
          viewPosition: 0.5
        });
      }
    }, 3000);

    return () => clearInterval(interval);
  }, [activeIndex]);

  const renderItem = ({ item }: { item: typeof COLLABORATORS[0] }) => (
    <LinearGradient
      colors={item.colors}
      style={styles.sponsorCard}
    >
      <View style={styles.iconContainer}>
        <Ionicons name={item.icon} size={24} color={item.textColor} />
      </View>
      <Text style={[styles.badge, { color: item.textColor }]}>{item.badge}</Text>
      <Text style={[styles.name, { color: item.textColor }]}>{item.name}</Text>
      <Text style={styles.tagline}>{item.tagline}</Text>
    </LinearGradient>
  );

  return (
    <View style={styles.footer}>
      <View style={styles.divider} />
      <Text style={styles.label}>COLABORADORES Y ECOSISTEMA</Text>

      <FlatList
        ref={flatListRef}
        data={COLLABORATORS}
        renderItem={renderItem}
        keyExtractor={(item) => item.id}
        horizontal
        showsHorizontalScrollIndicator={false}
        contentContainerStyle={styles.carouselContainer}
        snapToInterval={CARD_WIDTH + SPACING_ITEM}
        decelerationRate="fast"
        getItemLayout={(_, index) => ({
          length: CARD_WIDTH + SPACING_ITEM,
          offset: (CARD_WIDTH + SPACING_ITEM) * index,
          index,
        })}
        onMomentumScrollEnd={(event) => {
          const index = Math.round(event.nativeEvent.contentOffset.x / (CARD_WIDTH + SPACING_ITEM));
          setActiveIndex(index);
        }}
      />

      <View style={styles.indicatorContainer}>
        {COLLABORATORS.map((_, index) => (
          <View
            key={index}
            style={[
              styles.indicator,
              activeIndex === index && styles.indicatorActive
            ]}
          />
        ))}
      </View>

      <Text style={styles.text}>
        Innovación y alto rendimiento en cada línea de código
      </Text>
    </View>
  );
};

const styles = StyleSheet.create({
  footer: {
    marginTop: SPACING.xxl,
    paddingTop: SPACING.xl,
    paddingBottom: SPACING.xl,
  },
  divider: {
    height: 1,
    backgroundColor: COLORS.border.primary,
    marginBottom: SPACING.xl,
    marginHorizontal: SPACING.xl,
    opacity: 0.3,
  },
  label: {
    fontSize: 9,
    fontWeight: '900',
    color: COLORS.text.tertiary,
    textAlign: 'center',
    letterSpacing: 2,
    marginBottom: SPACING.lg,
    opacity: 0.6,
  },
  carouselContainer: {
    paddingHorizontal: (SCREEN_WIDTH - CARD_WIDTH) / 2,
    paddingBottom: SPACING.md,
  },
  sponsorCard: {
    width: CARD_WIDTH,
    marginRight: SPACING_ITEM,
    paddingVertical: SPACING.lg,
    paddingHorizontal: SPACING.md,
    borderRadius: BORDER_RADIUS.lg,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.08)',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: 'rgba(255, 255, 255, 0.02)',
  },
  iconContainer: {
    marginBottom: SPACING.xs,
    backgroundColor: 'rgba(255, 255, 255, 0.03)',
    padding: SPACING.sm,
    borderRadius: BORDER_RADIUS.full,
  },
  badge: {
    fontSize: 8,
    fontWeight: '900',
    letterSpacing: 1,
    marginBottom: 4,
    textTransform: 'uppercase',
  },
  name: {
    fontSize: 16,
    fontWeight: '900',
    letterSpacing: 0.2,
    textAlign: 'center',
  },
  tagline: {
    fontSize: 10,
    color: '#888',
    marginTop: 2,
    fontWeight: '600',
  },
  indicatorContainer: {
    flexDirection: 'row',
    justifyContent: 'center',
    marginTop: SPACING.md,
    gap: 6,
  },
  indicator: {
    width: 4,
    height: 4,
    borderRadius: 2,
    backgroundColor: 'rgba(255, 255, 255, 0.1)',
  },
  indicatorActive: {
    backgroundColor: COLORS.primary,
    width: 12,
  },
  text: {
    fontSize: 10,
    color: COLORS.text.tertiary,
    textAlign: 'center',
    marginTop: SPACING.lg,
    fontStyle: 'italic',
    opacity: 0.5,
  },
});
