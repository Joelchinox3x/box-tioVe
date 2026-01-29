import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, Image, Alert, ActivityIndicator, Platform, ScrollView } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import * as ImagePicker from 'expo-image-picker';
import { ConfirmModal } from '../../components/ConfirmModal';

// import * as ImageManipulator from 'expo-image-manipulator'; // Comentado por error 500
import { bannerService, Banner } from '../../services/bannerService';
import { COLORS, SPACING, BORDER_RADIUS, SHADOWS } from '../../constants/theme';
import { useNavigation } from '@react-navigation/native';

export default function AdminBannerScreen() {
    const [banners, setBanners] = useState<Banner[]>([]);
    const [loading, setLoading] = useState(true);
    const [uploading, setUploading] = useState(false);
    const navigation = useNavigation();

    const [expandedId, setExpandedId] = useState<number | null>(null);

    useEffect(() => {
        loadBanners();
    }, []);

    const loadBanners = async () => {
        setLoading(true);
        try {
            const data = await bannerService.getAll(true); // true = traer todos (inactivos también)
            setBanners(data);
        } catch (error) {
            Alert.alert("Error", "No se pudieron cargar los banners");
        } finally {
            setLoading(false);
        }
    };

    const handleUpload = async () => {
        // Solicitar permisos
        const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
        if (status !== 'granted') {
            Alert.alert("Permiso denegado", "Necesitamos acceso a la galería para subir banners.");
            return;
        }

        const result = await ImagePicker.launchImageLibraryAsync({
            mediaTypes: ImagePicker.MediaTypeOptions.Images,
            allowsEditing: true, // Permitir recorte básico
            aspect: [22, 10], // Aspect ratio 2.2:1 (el del banner)
            quality: 1,
        });

        if (!result.canceled && result.assets[0]) {
            const asset = result.assets[0];
            setUploading(true);
            try {
                // Nombre archivo limpio
                const fileName = asset.fileName || `banner_${Date.now()}.jpg`;
                const fileType = asset.mimeType || 'image/jpeg';

                const response = await bannerService.upload(asset.uri, fileName, fileType);
                if (response.success) {
                    Alert.alert("Éxito", "Banner subido correctamente");
                    loadBanners(); // Recargar lista
                } else {
                    throw new Error(response.message);
                }
            } catch (error: any) {
                Alert.alert("Error al subir", error.message || "Inténtalo de nuevo");
            } finally {
                setUploading(false);
            }
        }
    };

    const handleToggle = async (id: number, currentStatus: boolean) => {
        try {
            // Optimistic update
            setBanners(prev => prev.map(b => b.id === id ? { ...b, active: !currentStatus } : b));

            await bannerService.toggleActive(id, !currentStatus);
        } catch (error) {
            Alert.alert("Error", "No se pudo actualizar el estado");
            loadBanners(); // Revertir en error
        }
    };

    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [bannerToDelete, setBannerToDelete] = useState<number | null>(null);

    const performDelete = async (id: number) => {
        try {
            const response = await bannerService.delete(id);
            if (response.success) {
                setBanners(prev => prev.filter(b => b.id !== id));
            } else {
                throw new Error(response.message || "Error desconocido");
            }
        } catch (error) {
            const msg = error instanceof Error ? error.message : "No se pudo eliminar";
            Alert.alert("Error", msg);
        }
    };

    const handleDelete = (id: number) => {
        setBannerToDelete(id);
        setShowDeleteModal(true);
    };

    const confirmDelete = () => {
        if (bannerToDelete) {
            performDelete(bannerToDelete);
        }
        setShowDeleteModal(false);
        setBannerToDelete(null);
    };

    const toggleExpand = (id: number) => {
        setExpandedId(prev => prev === id ? null : id);
    };

    const renderItem = ({ item }: { item: Banner }) => {
        const isExpanded = expandedId === item.id;

        return (
            <View style={styles.card}>
                <TouchableOpacity
                    style={styles.cardHeader}
                    onPress={() => toggleExpand(item.id)}
                    activeOpacity={0.7}
                >
                    <View style={styles.headerInfo}>
                        <Ionicons name={isExpanded ? "chevron-down" : "chevron-forward"} size={20} color={COLORS.text.secondary} />
                        <Text style={styles.fileName} numberOfLines={1}>{item.original_name || item.filename}</Text>
                    </View>

                    <View style={[styles.statusBadge, { backgroundColor: item.active ? COLORS.success : COLORS.text.tertiary }]}>
                        <Text style={styles.statusText}>{item.active ? 'ACTIVO' : 'INACTIVO'}</Text>
                    </View>
                </TouchableOpacity>

                {isExpanded && (
                    <View style={styles.cardBody}>
                        <Image source={{ uri: item.url }} style={styles.fullImage} resizeMode="contain" />

                        <View style={styles.cardActions}>
                            <TouchableOpacity
                                style={[styles.actionButton, styles.toggleButton, { borderColor: item.active ? COLORS.warning : COLORS.success }]}
                                onPress={() => handleToggle(item.id, item.active)}
                            >
                                <Ionicons name={item.active ? "eye-off" : "eye"} size={20} color={item.active ? COLORS.warning : COLORS.success} />
                                <Text style={[styles.actionText, { color: item.active ? COLORS.warning : COLORS.success }]}>
                                    {item.active ? 'Ocultar' : 'Activar'}
                                </Text>
                            </TouchableOpacity>

                            <TouchableOpacity
                                style={[styles.actionButton, styles.deleteButton]}
                                onPress={() => handleDelete(item.id)}
                            >
                                <Ionicons name="trash" size={20} color={COLORS.error} />
                                <Text style={[styles.actionText, { color: COLORS.error }]}>Eliminar</Text>
                            </TouchableOpacity>
                        </View>
                    </View>
                )}
            </View>
        );
    };

    return (
        <View style={styles.container}>
            <View style={styles.header}>
                <Text style={styles.title}>Gestor de Banners</Text>
                <TouchableOpacity onPress={loadBanners} style={styles.refreshButton}>
                    <Ionicons name="refresh" size={24} color={COLORS.primary} />
                </TouchableOpacity>
            </View>

            <TouchableOpacity
                style={[styles.uploadButton, uploading && { opacity: 0.7 }]}
                onPress={handleUpload}
                disabled={uploading}
            >
                {uploading ? (
                    <ActivityIndicator color="#000" />
                ) : (
                    <>
                        <Ionicons name="cloud-upload" size={24} color="#000" />
                        <Text style={styles.uploadButtonText}>SUBIR NUEVO BANNER</Text>
                    </>
                )}
            </TouchableOpacity>

            {loading ? (
                <ActivityIndicator size="large" color={COLORS.primary} style={{ marginTop: 20 }} />
            ) : (
                <FlatList
                    data={banners}
                    renderItem={renderItem}
                    keyExtractor={item => item.id.toString()}
                    contentContainerStyle={styles.listContent}
                    ListEmptyComponent={
                        <Text style={styles.emptyText}>No hay banners subidos aún.</Text>
                    }
                />
            )}

            <ConfirmModal
                visible={showDeleteModal}
                title="Eliminar Banner"
                message="¿Estás seguro de que quieres eliminar este banner? Esta acción no se puede deshacer."
                confirmText="Eliminar"
                cancelText="Cancelar"
                confirmColor={COLORS.error}
                onConfirm={confirmDelete}
                onCancel={() => setShowDeleteModal(false)}
            />
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: COLORS.background,
        padding: SPACING.md,
    },
    header: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: SPACING.lg,
    },
    title: {
        fontSize: 24,
        fontWeight: 'bold',
        color: COLORS.text.primary,
    },
    refreshButton: {
        padding: 8,
    },
    uploadButton: {
        flexDirection: 'row',
        backgroundColor: COLORS.primary,
        padding: SPACING.md,
        borderRadius: BORDER_RADIUS.lg,
        alignItems: 'center',
        justifyContent: 'center',
        gap: 10,
        marginBottom: SPACING.lg,
        ...SHADOWS.md,
    },
    uploadButtonText: {
        fontWeight: 'bold',
        color: '#000',
        fontSize: 16,
    },
    listContent: {
        gap: SPACING.md,
        paddingBottom: 100,
    },
    card: {
        backgroundColor: COLORS.surface,
        borderRadius: BORDER_RADIUS.md,
        overflow: 'hidden',
        borderWidth: 1,
        borderColor: COLORS.border.primary,
        marginBottom: SPACING.sm,
    },
    cardHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        padding: SPACING.md,
        backgroundColor: COLORS.surface,
    },
    headerInfo: {
        flexDirection: 'row',
        alignItems: 'center',
        flex: 1,
        gap: SPACING.sm,
    },
    fileName: {
        color: COLORS.text.primary,
        fontWeight: 'bold',
        fontSize: 14,
        flex: 1,
        marginRight: 10,
    },
    statusBadge: {
        paddingHorizontal: 8,
        paddingVertical: 4,
        borderRadius: 4,
    },
    statusText: {
        fontSize: 10,
        fontWeight: 'bold',
        color: '#FFF',
    },
    cardBody: {
        borderTopWidth: 1,
        borderTopColor: COLORS.border.secondary,
        padding: SPACING.md,
        backgroundColor: '#000', // Fondo oscuro para resaltar la imagen
    },
    fullImage: {
        width: '100%',
        aspectRatio: 2.2, // Mantener proporción del banner
        borderRadius: BORDER_RADIUS.sm,
        marginBottom: SPACING.md,
        backgroundColor: '#1a1a1a',
    },
    cardActions: {
        flexDirection: 'row',
        gap: SPACING.sm,
    },
    actionButton: {
        flex: 1,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        padding: 10,
        borderRadius: BORDER_RADIUS.sm,
        borderWidth: 1,
        gap: 6,
    },
    toggleButton: {
        // Estilos dinámicos
        backgroundColor: COLORS.surface,
    },
    deleteButton: {
        borderColor: COLORS.error,
        backgroundColor: 'rgba(239, 68, 68, 0.1)',
    },
    actionText: {
        fontSize: 12,
        fontWeight: '600',
    },
    emptyText: {
        color: COLORS.text.secondary,
        textAlign: 'center',
        marginTop: 40,
    },
});
