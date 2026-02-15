import React, { useEffect, useState } from 'react';
import {
    View, Text, StyleSheet, FlatList, TouchableOpacity, Image,
    SafeAreaView, StatusBar, ActivityIndicator, ScrollView, Linking,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS } from '../constants/theme';
import { anunciosService } from '../services/anunciosService';
import type { Anuncio } from '../types';

const TIPO_COLORS: Record<string, string> = {
    info: '#3B82F6',
    urgente: '#EF4444',
    promo: '#FFD700',
    contacto: '#10B981',
    reglas: '#F59E0B',
};

const TIPO_ICONS: Record<string, string> = {
    info: 'information-circle',
    urgente: 'alert-circle',
    promo: 'megaphone',
    contacto: 'call',
    reglas: 'document-text',
};

const TIPO_LABELS: Record<string, string> = {
    todos: 'TODOS',
    info: 'INFO',
    urgente: 'URGENTE',
    promo: 'PROMO',
    contacto: 'CONTACTO',
    reglas: 'REGLAS',
};

interface AnunciosScreenProps {
    navigation: any;
    route?: any;
}

export default function AnunciosScreen({ navigation, route }: AnunciosScreenProps) {
    const eventoId = route?.params?.eventoId;
    const [anuncios, setAnuncios] = useState<Anuncio[]>([]);
    const [filteredAnuncios, setFilteredAnuncios] = useState<Anuncio[]>([]);
    const [loading, setLoading] = useState(true);
    const [selectedFilter, setSelectedFilter] = useState<string>('todos');

    useEffect(() => {
        loadAnuncios();
    }, []);

    useEffect(() => {
        if (selectedFilter === 'todos') {
            setFilteredAnuncios(anuncios);
        } else {
            setFilteredAnuncios(anuncios.filter(a => a.tipo === selectedFilter));
        }
    }, [selectedFilter, anuncios]);

    const loadAnuncios = async () => {
        setLoading(true);
        try {
            const data = await anunciosService.getPublicos(eventoId);
            setAnuncios(data);
        } catch (error) {
            console.error('Error loading anuncios:', error);
        } finally {
            setLoading(false);
        }
    };

    const getTimeAgo = (dateStr: string) => {
        const date = new Date(dateStr);
        const now = new Date();
        const diff = Math.floor((now.getTime() - date.getTime()) / 1000);
        if (diff < 60) return 'ahora';
        if (diff < 3600) return `hace ${Math.floor(diff / 60)} min`;
        if (diff < 86400) return `hace ${Math.floor(diff / 3600)}h`;
        if (diff < 604800) return `hace ${Math.floor(diff / 86400)} dias`;
        return new Date(dateStr).toLocaleDateString('es-ES', { day: 'numeric', month: 'short' });
    };

    const handleOpenLink = (url: string) => {
        Linking.openURL(url).catch(err => console.error('Error opening link:', err));
    };

    const getYoutubeThumbnail = (url: string): string | null => {
        const match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]+)/);
        if (match) return `https://img.youtube.com/vi/${match[1]}/hqdefault.jpg`;
        return null;
    };

    const renderAnuncio = ({ item }: { item: Anuncio }) => {
        const tipoColor = TIPO_COLORS[item.tipo] || '#888';
        const ytThumb = item.link_tipo === 'youtube' && item.link_url ? getYoutubeThumbnail(item.link_url) : null;

        return (
            <View style={[styles.anuncioCard, { borderLeftColor: tipoColor, borderLeftWidth: 3 }]}>
                {/* Header */}
                <View style={styles.cardHeader}>
                    <View style={styles.cardHeaderLeft}>
                        <Ionicons
                            name={(TIPO_ICONS[item.tipo] || 'information-circle') as any}
                            size={18}
                            color={tipoColor}
                        />
                        <Text style={styles.cardTitulo}>{item.titulo}</Text>
                        {item.fijado && <Ionicons name="pin" size={14} color={COLORS.primary} />}
                    </View>
                    <Text style={styles.timeText}>{getTimeAgo(item.created_at)}</Text>
                </View>

                {/* Mensaje */}
                <Text style={styles.cardMensaje}>{item.mensaje}</Text>

                {/* Imagen */}
                {item.imagen_url && (
                    <Image source={{ uri: item.imagen_url }} style={styles.cardImage} resizeMode="cover" />
                )}

                {/* YouTube thumbnail */}
                {ytThumb && item.link_url && (
                    <TouchableOpacity onPress={() => handleOpenLink(item.link_url!)} activeOpacity={0.8}>
                        <View style={styles.videoContainer}>
                            <Image source={{ uri: ytThumb }} style={styles.cardImage} resizeMode="cover" />
                            <View style={styles.playOverlay}>
                                <Ionicons name="play-circle" size={56} color="rgba(255,255,255,0.9)" />
                            </View>
                        </View>
                    </TouchableOpacity>
                )}

                {/* TikTok / Other links */}
                {item.link_url && !ytThumb && (
                    <TouchableOpacity
                        style={styles.linkButton}
                        onPress={() => handleOpenLink(item.link_url!)}
                    >
                        <Ionicons
                            name={item.link_tipo === 'tiktok' ? 'musical-notes' : 'open'}
                            size={16}
                            color={COLORS.primary}
                        />
                        <Text style={styles.linkButtonText}>
                            {item.link_tipo === 'tiktok' ? 'Ver en TikTok' : 'Abrir enlace'}
                        </Text>
                    </TouchableOpacity>
                )}

                {/* Video uploaded */}
                {item.video_url && (
                    <TouchableOpacity
                        style={styles.linkButton}
                        onPress={() => handleOpenLink(item.video_url!)}
                    >
                        <Ionicons name="videocam" size={16} color={COLORS.primary} />
                        <Text style={styles.linkButtonText}>Ver video</Text>
                    </TouchableOpacity>
                )}

                {/* Footer */}
                <View style={styles.cardFooter}>
                    <View style={[styles.tipoBadge, { backgroundColor: tipoColor }]}>
                        <Text style={styles.tipoBadgeText}>{TIPO_LABELS[item.tipo]}</Text>
                    </View>
                    <Text style={styles.fuenteText}>
                        {item.fuente === 'telegram' ? 'via Telegram' : 'Admin'}
                    </Text>
                </View>
            </View>
        );
    };

    return (
        <SafeAreaView style={styles.container}>
            <StatusBar barStyle="light-content" backgroundColor={COLORS.background} />

            {/* Header */}
            <View style={styles.header}>
                <TouchableOpacity style={styles.backButton} onPress={() => navigation.goBack()}>
                    <Ionicons name="arrow-back" size={24} color={COLORS.text.primary} />
                </TouchableOpacity>
                <Text style={styles.headerTitle}>Anuncios</Text>
                <TouchableOpacity onPress={loadAnuncios} style={styles.refreshBtn}>
                    <Ionicons name="refresh" size={22} color={COLORS.primary} />
                </TouchableOpacity>
            </View>

            {/* Filter pills */}
            <View style={styles.filterWrapper}>
            <ScrollView
                horizontal
                showsHorizontalScrollIndicator={false}
                contentContainerStyle={styles.filterContainer}
            >
                {Object.entries(TIPO_LABELS).map(([key, label]) => (
                    <TouchableOpacity
                        key={key}
                        style={[
                            styles.filterPill,
                            selectedFilter === key && styles.filterPillActive,
                            selectedFilter === key && key !== 'todos' && { backgroundColor: TIPO_COLORS[key] },
                        ]}
                        onPress={() => setSelectedFilter(key)}
                    >
                        <Text style={[
                            styles.filterPillText,
                            selectedFilter === key && styles.filterPillTextActive,
                        ]}>
                            {label}
                        </Text>
                    </TouchableOpacity>
                ))}
            </ScrollView>
            </View>

            {/* Content */}
            {loading ? (
                <View style={styles.loadingContainer}>
                    <ActivityIndicator size="large" color={COLORS.primary} />
                </View>
            ) : (
                <FlatList
                    data={filteredAnuncios}
                    renderItem={renderAnuncio}
                    keyExtractor={item => item.id.toString()}
                    contentContainerStyle={styles.listContent}
                    ListEmptyComponent={
                        <View style={styles.emptyContainer}>
                            <Ionicons name="megaphone-outline" size={56} color={COLORS.text.tertiary} />
                            <Text style={styles.emptyText}>No hay anuncios</Text>
                            <Text style={styles.emptySubtext}>
                                {selectedFilter !== 'todos' ? 'No hay anuncios de este tipo' : 'Aun no se han publicado anuncios'}
                            </Text>
                        </View>
                    }
                />
            )}
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: COLORS.background,
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
    refreshBtn: {
        width: 40,
        height: 40,
        justifyContent: 'center',
        alignItems: 'center',
    },
    filterWrapper: {
        paddingHorizontal: SPACING.md,
        paddingTop: SPACING.sm,
        paddingBottom: SPACING.xs,
    },
    filterContainer: {
        alignItems: 'center',
        flexDirection: 'row',
    },
    filterPill: {
        paddingHorizontal: SPACING.sm,
        paddingVertical: 6,
        borderRadius: BORDER_RADIUS.full,
        borderWidth: 1,
        borderColor: COLORS.text.tertiary,
        marginRight: SPACING.xs,
        alignSelf: 'center',
    },
    filterPillActive: {
        backgroundColor: COLORS.primary,
        borderColor: COLORS.primary,
    },
    filterPillText: {
        color: COLORS.text.secondary,
        fontWeight: 'bold',
        fontSize: 11,
        lineHeight: 14,
    },
    filterPillTextActive: {
        color: '#000',
    },
    loadingContainer: {
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
    },
    listContent: {
        padding: SPACING.lg,
        gap: SPACING.md,
        paddingBottom: 100,
    },
    anuncioCard: {
        backgroundColor: COLORS.surface,
        borderRadius: BORDER_RADIUS.lg,
        padding: SPACING.lg,
        borderWidth: 1,
        borderColor: COLORS.border.primary,
    },
    cardHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: SPACING.sm,
    },
    cardHeaderLeft: {
        flexDirection: 'row',
        alignItems: 'center',
        flex: 1,
        gap: SPACING.xs,
    },
    cardTitulo: {
        fontSize: 15,
        fontWeight: 'bold',
        color: COLORS.text.primary,
        flex: 1,
    },
    timeText: {
        fontSize: 11,
        color: COLORS.text.tertiary,
    },
    cardMensaje: {
        fontSize: 14,
        color: COLORS.text.secondary,
        lineHeight: 21,
        marginBottom: SPACING.sm,
    },
    cardImage: {
        width: '100%',
        height: 200,
        borderRadius: BORDER_RADIUS.md,
        marginBottom: SPACING.sm,
        backgroundColor: '#1a1a1a',
    },
    videoContainer: {
        position: 'relative',
        marginBottom: SPACING.sm,
    },
    playOverlay: {
        ...StyleSheet.absoluteFillObject,
        justifyContent: 'center',
        alignItems: 'center',
        backgroundColor: 'rgba(0,0,0,0.3)',
        borderRadius: BORDER_RADIUS.md,
    },
    linkButton: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: SPACING.xs,
        paddingVertical: SPACING.sm,
        paddingHorizontal: SPACING.md,
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        borderRadius: BORDER_RADIUS.md,
        marginBottom: SPACING.sm,
        alignSelf: 'flex-start',
    },
    linkButtonText: {
        color: COLORS.primary,
        fontWeight: '600',
        fontSize: 13,
    },
    cardFooter: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginTop: SPACING.xs,
    },
    tipoBadge: {
        paddingHorizontal: 8,
        paddingVertical: 3,
        borderRadius: 4,
    },
    tipoBadgeText: {
        fontSize: 9,
        fontWeight: 'bold',
        color: '#FFF',
    },
    fuenteText: {
        fontSize: 10,
        color: COLORS.text.tertiary,
    },
    emptyContainer: {
        alignItems: 'center',
        marginTop: 60,
        gap: SPACING.sm,
    },
    emptyText: {
        fontSize: 18,
        color: COLORS.text.secondary,
        fontWeight: '600',
    },
    emptySubtext: {
        fontSize: 13,
        color: COLORS.text.tertiary,
        textAlign: 'center',
    },
});
