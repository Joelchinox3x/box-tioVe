import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, ActivityIndicator } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, BORDER_RADIUS } from '../constants/theme';
import { anunciosService } from '../services/anunciosService';
import type { Anuncio } from '../types';

const TIPO_COLORS: Record<string, string> = {
    info: '#3B82F6',
    urgente: '#EF4444',
    promo: '#FFD700',
    contacto: '#10B981',
    reglas: '#F59E0B',
};

interface AnunciosSummaryProps {
    eventoId?: number;
    maxItems?: number;
    onViewAll: () => void;
}

export default function AnunciosSummary({ eventoId, maxItems = 3, onViewAll }: AnunciosSummaryProps) {
    const [anuncios, setAnuncios] = useState<Anuncio[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadAnuncios();
    }, [eventoId]);

    const loadAnuncios = async () => {
        try {
            const data = await anunciosService.getPublicos(eventoId, maxItems);
            setAnuncios(data);
        } catch (error) {
            console.error('Error loading anuncios summary:', error);
        } finally {
            setLoading(false);
        }
    };

    const getTimeAgo = (dateStr: string) => {
        const date = new Date(dateStr);
        const now = new Date();
        const diff = Math.floor((now.getTime() - date.getTime()) / 1000);
        if (diff < 60) return 'ahora';
        if (diff < 3600) return `${Math.floor(diff / 60)}m`;
        if (diff < 86400) return `${Math.floor(diff / 3600)}h`;
        return `${Math.floor(diff / 86400)}d`;
    };

    if (loading) {
        return (
            <View style={styles.container}>
                <ActivityIndicator size="small" color={COLORS.primary} />
            </View>
        );
    }

    if (anuncios.length === 0) return null;

    return (
        <View style={styles.container}>
            <View style={styles.header}>
                <View style={styles.headerLeft}>
                    <Ionicons name="megaphone" size={18} color={COLORS.primary} />
                    <Text style={styles.headerTitle}>ANUNCIOS</Text>
                </View>
                <TouchableOpacity onPress={onViewAll} style={styles.viewAllButton}>
                    <Text style={styles.viewAllText}>Ver todos</Text>
                    <Ionicons name="chevron-forward" size={14} color={COLORS.primary} />
                </TouchableOpacity>
            </View>

            {anuncios.map((anuncio) => {
                const tipoColor = TIPO_COLORS[anuncio.tipo] || '#888';
                return (
                    <TouchableOpacity
                        key={anuncio.id}
                        style={styles.anuncioCard}
                        onPress={onViewAll}
                        activeOpacity={0.7}
                    >
                        <View style={[styles.tipoDot, { backgroundColor: tipoColor }]} />
                        <View style={styles.anuncioContent}>
                            <View style={styles.anuncioHeader}>
                                <Text style={styles.anuncioTitulo} numberOfLines={1}>
                                    {anuncio.titulo}
                                </Text>
                                <Text style={styles.anuncioTime}>{getTimeAgo(anuncio.created_at)}</Text>
                            </View>
                            <Text style={styles.anuncioMensaje} numberOfLines={2}>
                                {anuncio.mensaje}
                            </Text>
                            {anuncio.medio !== 'texto' && (
                                <View style={styles.mediaIndicator}>
                                    <Ionicons
                                        name={anuncio.medio === 'imagen' ? 'image' : anuncio.medio === 'video' ? 'videocam' : 'link'}
                                        size={12}
                                        color={COLORS.text.tertiary}
                                    />
                                    <Text style={styles.mediaText}>
                                        {anuncio.medio === 'imagen' ? 'Imagen' : anuncio.medio === 'video' ? 'Video' : 'Link'}
                                    </Text>
                                </View>
                            )}
                        </View>
                        {anuncio.fijado && (
                            <Ionicons name="pin" size={12} color={COLORS.primary} />
                        )}
                    </TouchableOpacity>
                );
            })}
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        marginHorizontal: SPACING.lg,
        marginBottom: SPACING.lg,
        backgroundColor: COLORS.surface,
        borderRadius: BORDER_RADIUS.lg,
        padding: SPACING.md,
        borderWidth: 1,
        borderColor: COLORS.border.primary,
    },
    header: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: SPACING.md,
    },
    headerLeft: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: SPACING.xs,
    },
    headerTitle: {
        fontSize: 13,
        fontWeight: 'bold',
        color: COLORS.text.primary,
        letterSpacing: 1,
    },
    viewAllButton: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 2,
    },
    viewAllText: {
        fontSize: 12,
        color: COLORS.primary,
        fontWeight: '600',
    },
    anuncioCard: {
        flexDirection: 'row',
        alignItems: 'flex-start',
        paddingVertical: SPACING.sm,
        borderTopWidth: 1,
        borderTopColor: COLORS.border.secondary,
        gap: SPACING.sm,
    },
    tipoDot: {
        width: 8,
        height: 8,
        borderRadius: 4,
        marginTop: 5,
    },
    anuncioContent: {
        flex: 1,
    },
    anuncioHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: 2,
    },
    anuncioTitulo: {
        fontSize: 13,
        fontWeight: 'bold',
        color: COLORS.text.primary,
        flex: 1,
        marginRight: 8,
    },
    anuncioTime: {
        fontSize: 10,
        color: COLORS.text.tertiary,
    },
    anuncioMensaje: {
        fontSize: 12,
        color: COLORS.text.secondary,
        lineHeight: 17,
    },
    mediaIndicator: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 4,
        marginTop: 4,
    },
    mediaText: {
        fontSize: 10,
        color: COLORS.text.tertiary,
    },
});
