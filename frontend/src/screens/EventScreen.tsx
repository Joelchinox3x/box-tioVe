import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  SafeAreaView,
  StatusBar,
  ActivityIndicator,
  TouchableOpacity,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS } from '../constants/theme';
import api from '../services/api';
import type { EventData } from '../types';

interface EventScreenProps {
  navigation: any;
}

export default function EventScreen({ navigation }: EventScreenProps) {
  const [loading, setLoading] = useState(true);
  const [eventData, setEventData] = useState<EventData | null>(null);

  useEffect(() => {
    loadEventData();
  }, []);

  const loadEventData = async () => {
    try {
      const response = await api.get('/eventos');
      setEventData(response.data);
    } catch (error) {
      console.error('Error al cargar evento:', error);
      setEventData({
        evento: null,
        peleadores_destacados: [],
        peleas_pactadas: [],
      });
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color={COLORS.primary} />
      </View>
    );
  }

  const evento = eventData?.evento;

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor={COLORS.background} />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => navigation.goBack()}
        >
          <Ionicons name="arrow-back" size={24} color={COLORS.text.primary} />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Detalles del Evento</Text>
        <View style={styles.placeholder} />
      </View>

      <ScrollView style={styles.scrollView} showsVerticalScrollIndicator={false}>
        {evento ? (
          <>
            {/* Card principal del evento */}
            <View style={styles.eventCard}>
              <View style={styles.liveIndicator}>
                <View style={styles.liveDot} />
                <Text style={styles.liveText}>EVENTO ACTIVO</Text>
              </View>

              <Text style={styles.eventTitle}>{evento.titulo}</Text>

              {evento.descripcion && (
                <Text style={styles.eventDescription}>{evento.descripcion}</Text>
              )}

              {/* Información del evento */}
              <View style={styles.infoSection}>
                <View style={styles.infoRow}>
                  <Ionicons name="calendar" size={20} color={COLORS.primary} />
                  <View style={styles.infoTextContainer}>
                    <Text style={styles.infoLabel}>Fecha</Text>
                    <Text style={styles.infoValue}>
                      {new Date(evento.fecha_evento).toLocaleDateString('es-ES', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                      })}
                    </Text>
                  </View>
                </View>

                <View style={styles.infoRow}>
                  <Ionicons name="location" size={20} color={COLORS.primary} />
                  <View style={styles.infoTextContainer}>
                    <Text style={styles.infoLabel}>Lugar</Text>
                    <Text style={styles.infoValue}>{evento.lugar}</Text>
                    {evento.direccion && (
                      <Text style={styles.infoSubtext}>{evento.direccion}</Text>
                    )}
                  </View>
                </View>

                {evento.precio_entrada && (
                  <View style={styles.infoRow}>
                    <Ionicons name="cash" size={20} color={COLORS.primary} />
                    <View style={styles.infoTextContainer}>
                      <Text style={styles.infoLabel}>Precio de Entrada</Text>
                      <Text style={styles.priceValue}>
                        S/ {parseFloat(evento.precio_entrada.toString()).toFixed(2)}
                      </Text>
                    </View>
                  </View>
                )}

                <View style={styles.infoRow}>
                  <Ionicons name="trophy" size={20} color={COLORS.primary} />
                  <View style={styles.infoTextContainer}>
                    <Text style={styles.infoLabel}>Estado</Text>
                    <Text style={[styles.statusBadge, styles[`status_${evento.estado}`]]}>
                      {evento.estado.toUpperCase()}
                    </Text>
                  </View>
                </View>
              </View>
            </View>

            {/* Estadísticas */}
            <View style={styles.statsContainer}>
              <View style={styles.statCard}>
                <Text style={styles.statNumber}>
                  {eventData.peleas_pactadas?.length || 0}
                </Text>
                <Text style={styles.statLabel}>Peleas</Text>
              </View>
              <View style={styles.statCard}>
                <Text style={styles.statNumber}>
                  {eventData.peleadores_destacados?.length || 0}
                </Text>
                <Text style={styles.statLabel}>Peleadores</Text>
              </View>
            </View>

            {/* Botón de compra */}
            <TouchableOpacity
              style={styles.buyButton}
              onPress={() => navigation.navigate('Register')}
            >
              <Ionicons name="ticket" size={24} color={COLORS.text.inverse} />
              <Text style={styles.buyButtonText}>COMPRAR ENTRADAS</Text>
            </TouchableOpacity>
          </>
        ) : (
          <View style={styles.emptyContainer}>
            <Ionicons name="calendar-outline" size={64} color={COLORS.text.tertiary} />
            <Text style={styles.emptyText}>No hay evento activo</Text>
          </View>
        )}
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },
  loadingContainer: {
    flex: 1,
    backgroundColor: COLORS.background,
    justifyContent: 'center',
    alignItems: 'center',
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
  eventCard: {
    margin: SPACING.lg,
    padding: SPACING.xl,
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.xl,
    borderWidth: 1,
    borderColor: COLORS.border.primary,
  },
  liveIndicator: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: SPACING.md,
  },
  liveDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: COLORS.error,
    marginRight: SPACING.sm,
  },
  liveText: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.secondary,
    letterSpacing: 1,
  },
  eventTitle: {
    fontSize: TYPOGRAPHY.fontSize.xxxl,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.primary,
    marginBottom: SPACING.md,
  },
  eventDescription: {
    fontSize: TYPOGRAPHY.fontSize.md,
    color: COLORS.text.secondary,
    lineHeight: 22,
    marginBottom: SPACING.lg,
  },
  infoSection: {
    gap: SPACING.lg,
  },
  infoRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: SPACING.md,
  },
  infoTextContainer: {
    flex: 1,
  },
  infoLabel: {
    fontSize: TYPOGRAPHY.fontSize.xs,
    color: COLORS.text.tertiary,
    marginBottom: 4,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  infoValue: {
    fontSize: TYPOGRAPHY.fontSize.md,
    fontWeight: TYPOGRAPHY.fontWeight.semibold,
    color: COLORS.text.primary,
    textTransform: 'capitalize',
  },
  infoSubtext: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.text.secondary,
    marginTop: 2,
  },
  priceValue: {
    fontSize: TYPOGRAPHY.fontSize.xxl,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.primary,
  },
  statusBadge: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.xs,
    borderRadius: BORDER_RADIUS.md,
    alignSelf: 'flex-start',
    marginTop: 4,
  },
  status_activo: {
    backgroundColor: COLORS.success,
    color: COLORS.text.inverse,
  },
  status_finalizado: {
    backgroundColor: COLORS.text.tertiary,
    color: COLORS.text.inverse,
  },
  status_cancelado: {
    backgroundColor: COLORS.error,
    color: COLORS.text.inverse,
  },
  statsContainer: {
    flexDirection: 'row',
    paddingHorizontal: SPACING.lg,
    gap: SPACING.md,
    marginBottom: SPACING.lg,
  },
  statCard: {
    flex: 1,
    padding: SPACING.lg,
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.lg,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: COLORS.border.primary,
  },
  statNumber: {
    fontSize: 32,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.primary,
    marginBottom: SPACING.xs,
  },
  statLabel: {
    fontSize: TYPOGRAPHY.fontSize.sm,
    color: COLORS.text.secondary,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  buyButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginHorizontal: SPACING.lg,
    marginBottom: SPACING.xl,
    backgroundColor: COLORS.primary,
    paddingVertical: SPACING.lg,
    borderRadius: BORDER_RADIUS.lg,
    gap: SPACING.sm,
  },
  buyButtonText: {
    fontSize: TYPOGRAPHY.fontSize.lg,
    fontWeight: TYPOGRAPHY.fontWeight.bold,
    color: COLORS.text.inverse,
    letterSpacing: 1,
  },
  emptyContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: SPACING.xxl,
    marginTop: 100,
  },
  emptyText: {
    fontSize: TYPOGRAPHY.fontSize.lg,
    color: COLORS.text.tertiary,
    marginTop: SPACING.lg,
  },
});
