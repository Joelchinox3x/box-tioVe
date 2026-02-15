import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, Image, Alert, ActivityIndicator, TextInput, ScrollView, Modal, Platform } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import * as ImagePicker from 'expo-image-picker';
import { ConfirmModal } from '../../components/ConfirmModal';
import { anunciosService } from '../../services/anunciosService';
import { COLORS, SPACING, BORDER_RADIUS, SHADOWS } from '../../constants/theme';
import type { Anuncio } from '../../types';

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
    info: 'INFO',
    urgente: 'URGENTE',
    promo: 'PROMO',
    contacto: 'CONTACTO',
    reglas: 'REGLAS',
};

export default function AdminAnunciosScreen() {
    const [anuncios, setAnuncios] = useState<Anuncio[]>([]);
    const [loading, setLoading] = useState(true);
    const [expandedId, setExpandedId] = useState<number | null>(null);
    const [showCreateModal, setShowCreateModal] = useState(false);
    const [showDeleteModal, setShowDeleteModal] = useState(false);
    const [anuncioToDelete, setAnuncioToDelete] = useState<number | null>(null);
    const [creating, setCreating] = useState(false);

    // Form state
    const [formTitulo, setFormTitulo] = useState('');
    const [formMensaje, setFormMensaje] = useState('');
    const [formTipo, setFormTipo] = useState<Anuncio['tipo']>('info');
    const [formFijado, setFormFijado] = useState(false);
    const [formLinkUrl, setFormLinkUrl] = useState('');
    const [formMediaUri, setFormMediaUri] = useState<string | null>(null);
    const [formMediaType, setFormMediaType] = useState<'imagen' | 'video' | null>(null);

    useEffect(() => {
        loadAnuncios();
    }, []);

    const loadAnuncios = async () => {
        setLoading(true);
        try {
            const data = await anunciosService.getAll();
            setAnuncios(data);
        } catch (error) {
            Alert.alert("Error", "No se pudieron cargar los anuncios");
        } finally {
            setLoading(false);
        }
    };

    const resetForm = () => {
        setFormTitulo('');
        setFormMensaje('');
        setFormTipo('info');
        setFormFijado(false);
        setFormLinkUrl('');
        setFormMediaUri(null);
        setFormMediaType(null);
    };

    const handleCreate = async () => {
        if (!formMensaje.trim()) {
            Alert.alert("Error", "El mensaje es requerido");
            return;
        }

        setCreating(true);
        try {
            let result;

            if (formMediaUri && formMediaType) {
                const fileName = `anuncio_${Date.now()}.${formMediaType === 'imagen' ? 'jpg' : 'mp4'}`;
                const fileType = formMediaType === 'imagen' ? 'image/jpeg' : 'video/mp4';
                result = await anunciosService.crearConMedia(
                    {
                        titulo: formTitulo || formMensaje.substring(0, 100),
                        mensaje: formMensaje,
                        tipo: formTipo,
                        fijado: formFijado,
                    },
                    formMediaUri,
                    formMediaType,
                    fileName,
                    fileType,
                );
            } else {
                result = await anunciosService.crear({
                    titulo: formTitulo || formMensaje.substring(0, 100),
                    mensaje: formMensaje,
                    tipo: formTipo,
                    fijado: formFijado,
                    link_url: formLinkUrl || undefined,
                });
            }

            if (result.success) {
                Alert.alert("Exito", "Anuncio creado correctamente");
                setShowCreateModal(false);
                resetForm();
                loadAnuncios();
            } else {
                throw new Error(result.message);
            }
        } catch (error: any) {
            Alert.alert("Error", error.message || "No se pudo crear el anuncio");
        } finally {
            setCreating(false);
        }
    };

    const handlePickImage = async () => {
        const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
        if (status !== 'granted') {
            Alert.alert("Permiso denegado", "Necesitamos acceso a la galeria");
            return;
        }

        const result = await ImagePicker.launchImageLibraryAsync({
            mediaTypes: ['images'],
            allowsEditing: true,
            quality: 0.8,
        });

        if (!result.canceled && result.assets[0]) {
            setFormMediaUri(result.assets[0].uri);
            setFormMediaType('imagen');
        }
    };

    const handlePickVideo = async () => {
        const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
        if (status !== 'granted') {
            Alert.alert("Permiso denegado", "Necesitamos acceso a la galeria");
            return;
        }

        const result = await ImagePicker.launchImageLibraryAsync({
            mediaTypes: ['videos'],
            allowsEditing: true,
            quality: 0.8,
        });

        if (!result.canceled && result.assets[0]) {
            setFormMediaUri(result.assets[0].uri);
            setFormMediaType('video');
        }
    };

    const handleToggleActive = async (id: number, currentActivo: boolean) => {
        setAnuncios(prev => prev.map(a => a.id === id ? { ...a, activo: !currentActivo } : a));
        try {
            await anunciosService.toggleActive(id, !currentActivo);
        } catch (error) {
            loadAnuncios();
        }
    };

    const handleToggleFijado = async (id: number, currentFijado: boolean) => {
        setAnuncios(prev => prev.map(a => a.id === id ? { ...a, fijado: !currentFijado } : a));
        try {
            await anunciosService.toggleFijado(id, !currentFijado);
        } catch (error) {
            loadAnuncios();
        }
    };

    const handleDelete = (id: number) => {
        setAnuncioToDelete(id);
        setShowDeleteModal(true);
    };

    const confirmDelete = async () => {
        if (anuncioToDelete) {
            try {
                const result = await anunciosService.eliminar(anuncioToDelete);
                if (result.success) {
                    setAnuncios(prev => prev.filter(a => a.id !== anuncioToDelete));
                }
            } catch (error) {
                Alert.alert("Error", "No se pudo eliminar el anuncio");
            }
        }
        setShowDeleteModal(false);
        setAnuncioToDelete(null);
    };

    const getTimeAgo = (dateStr: string) => {
        const date = new Date(dateStr);
        const now = new Date();
        const diff = Math.floor((now.getTime() - date.getTime()) / 1000);
        if (diff < 60) return 'hace un momento';
        if (diff < 3600) return `hace ${Math.floor(diff / 60)}m`;
        if (diff < 86400) return `hace ${Math.floor(diff / 3600)}h`;
        return `hace ${Math.floor(diff / 86400)}d`;
    };

    const renderItem = ({ item }: { item: Anuncio }) => {
        const isExpanded = expandedId === item.id;
        const tipoColor = TIPO_COLORS[item.tipo] || '#888';

        return (
            <View style={[styles.card, { borderLeftColor: tipoColor, borderLeftWidth: 3 }]}>
                <TouchableOpacity
                    style={styles.cardHeader}
                    onPress={() => setExpandedId(prev => prev === item.id ? null : item.id)}
                    activeOpacity={0.7}
                >
                    <View style={styles.headerLeft}>
                        <Ionicons
                            name={isExpanded ? "chevron-down" : "chevron-forward"}
                            size={18}
                            color={COLORS.text.secondary}
                        />
                        <Ionicons
                            name={(TIPO_ICONS[item.tipo] || 'information-circle') as any}
                            size={18}
                            color={tipoColor}
                        />
                        <Text style={styles.cardTitle} numberOfLines={1}>{item.titulo}</Text>
                    </View>

                    <View style={styles.headerRight}>
                        {item.fijado && (
                            <Ionicons name="pin" size={14} color={COLORS.primary} style={{ marginRight: 4 }} />
                        )}
                        <View style={[styles.tipoBadge, { backgroundColor: tipoColor }]}>
                            <Text style={styles.tipoBadgeText}>{TIPO_LABELS[item.tipo]}</Text>
                        </View>
                        <View style={[styles.statusBadge, { backgroundColor: item.activo ? COLORS.success : COLORS.text.tertiary }]}>
                            <Text style={styles.statusText}>{item.activo ? 'ON' : 'OFF'}</Text>
                        </View>
                    </View>
                </TouchableOpacity>

                {isExpanded && (
                    <View style={styles.cardBody}>
                        <Text style={styles.mensajeText}>{item.mensaje}</Text>

                        {item.imagen_url && (
                            <Image source={{ uri: item.imagen_url }} style={styles.mediaImage} resizeMode="cover" />
                        )}

                        {item.link_url && (
                            <View style={styles.linkContainer}>
                                <Ionicons name="link" size={16} color={COLORS.primary} />
                                <Text style={styles.linkText} numberOfLines={1}>{item.link_url}</Text>
                            </View>
                        )}

                        <View style={styles.metaRow}>
                            <Text style={styles.metaText}>
                                {item.fuente === 'telegram' ? 'Telegram' : 'Admin'} - {getTimeAgo(item.created_at)}
                            </Text>
                            {item.medio !== 'texto' && (
                                <View style={styles.medioBadge}>
                                    <Ionicons
                                        name={item.medio === 'imagen' ? 'image' : item.medio === 'video' ? 'videocam' : 'link'}
                                        size={12}
                                        color={COLORS.text.secondary}
                                    />
                                    <Text style={styles.medioText}>{item.medio}</Text>
                                </View>
                            )}
                        </View>

                        <View style={styles.cardActions}>
                            <TouchableOpacity
                                style={[styles.actionButton, { borderColor: item.activo ? '#F59E0B' : COLORS.success }]}
                                onPress={() => handleToggleActive(item.id, item.activo)}
                            >
                                <Ionicons name={item.activo ? "eye-off" : "eye"} size={16} color={item.activo ? '#F59E0B' : COLORS.success} />
                                <Text style={[styles.actionText, { color: item.activo ? '#F59E0B' : COLORS.success }]}>
                                    {item.activo ? 'Ocultar' : 'Activar'}
                                </Text>
                            </TouchableOpacity>

                            <TouchableOpacity
                                style={[styles.actionButton, { borderColor: item.fijado ? COLORS.text.tertiary : COLORS.primary }]}
                                onPress={() => handleToggleFijado(item.id, item.fijado)}
                            >
                                <Ionicons name={item.fijado ? "pin-outline" : "pin"} size={16} color={item.fijado ? COLORS.text.tertiary : COLORS.primary} />
                                <Text style={[styles.actionText, { color: item.fijado ? COLORS.text.tertiary : COLORS.primary }]}>
                                    {item.fijado ? 'Desfijar' : 'Fijar'}
                                </Text>
                            </TouchableOpacity>

                            <TouchableOpacity
                                style={[styles.actionButton, { borderColor: COLORS.error }]}
                                onPress={() => handleDelete(item.id)}
                            >
                                <Ionicons name="trash" size={16} color={COLORS.error} />
                            </TouchableOpacity>
                        </View>
                    </View>
                )}
            </View>
        );
    };

    const renderCreateModal = () => (
        <Modal visible={showCreateModal} animationType="slide" transparent>
            <View style={styles.modalOverlay}>
                <View style={styles.modalContent}>
                    <View style={styles.modalHeader}>
                        <Text style={styles.modalTitle}>Nuevo Anuncio</Text>
                        <TouchableOpacity onPress={() => { setShowCreateModal(false); resetForm(); }}>
                            <Ionicons name="close" size={24} color={COLORS.text.primary} />
                        </TouchableOpacity>
                    </View>

                    <ScrollView style={styles.modalBody} showsVerticalScrollIndicator={false}>
                        <Text style={styles.fieldLabel}>Titulo (opcional)</Text>
                        <TextInput
                            style={styles.textInput}
                            value={formTitulo}
                            onChangeText={setFormTitulo}
                            placeholder="Titulo del anuncio"
                            placeholderTextColor={COLORS.text.tertiary}
                        />

                        <Text style={styles.fieldLabel}>Mensaje *</Text>
                        <TextInput
                            style={[styles.textInput, styles.textArea]}
                            value={formMensaje}
                            onChangeText={setFormMensaje}
                            placeholder="Escribe tu mensaje aqui..."
                            placeholderTextColor={COLORS.text.tertiary}
                            multiline
                            numberOfLines={4}
                        />

                        <Text style={styles.fieldLabel}>Tipo</Text>
                        <View style={styles.tipoPicker}>
                            {(['info', 'urgente', 'promo', 'contacto', 'reglas'] as const).map((t) => (
                                <TouchableOpacity
                                    key={t}
                                    style={[
                                        styles.tipoOption,
                                        { borderColor: TIPO_COLORS[t] },
                                        formTipo === t && { backgroundColor: TIPO_COLORS[t] },
                                    ]}
                                    onPress={() => setFormTipo(t)}
                                >
                                    <Text style={[
                                        styles.tipoOptionText,
                                        formTipo === t && { color: '#000' },
                                    ]}>
                                        {TIPO_LABELS[t]}
                                    </Text>
                                </TouchableOpacity>
                            ))}
                        </View>

                        <Text style={styles.fieldLabel}>Link (YouTube, TikTok, etc.)</Text>
                        <TextInput
                            style={styles.textInput}
                            value={formLinkUrl}
                            onChangeText={setFormLinkUrl}
                            placeholder="https://youtube.com/watch?v=..."
                            placeholderTextColor={COLORS.text.tertiary}
                            autoCapitalize="none"
                        />

                        <Text style={styles.fieldLabel}>Media</Text>
                        <View style={styles.mediaButtons}>
                            <TouchableOpacity style={styles.mediaButton} onPress={handlePickImage}>
                                <Ionicons name="image" size={20} color={COLORS.primary} />
                                <Text style={styles.mediaButtonText}>Imagen</Text>
                            </TouchableOpacity>
                            <TouchableOpacity style={styles.mediaButton} onPress={handlePickVideo}>
                                <Ionicons name="videocam" size={20} color={COLORS.primary} />
                                <Text style={styles.mediaButtonText}>Video</Text>
                            </TouchableOpacity>
                        </View>

                        {formMediaUri && (
                            <View style={styles.mediaPreview}>
                                {formMediaType === 'imagen' && (
                                    <Image source={{ uri: formMediaUri }} style={styles.previewImage} />
                                )}
                                {formMediaType === 'video' && (
                                    <View style={styles.videoPlaceholder}>
                                        <Ionicons name="videocam" size={32} color={COLORS.primary} />
                                        <Text style={styles.videoPlaceholderText}>Video seleccionado</Text>
                                    </View>
                                )}
                                <TouchableOpacity
                                    style={styles.removeMedia}
                                    onPress={() => { setFormMediaUri(null); setFormMediaType(null); }}
                                >
                                    <Ionicons name="close-circle" size={24} color={COLORS.error} />
                                </TouchableOpacity>
                            </View>
                        )}

                        <TouchableOpacity
                            style={styles.fijarToggle}
                            onPress={() => setFormFijado(!formFijado)}
                        >
                            <Ionicons
                                name={formFijado ? "checkbox" : "square-outline"}
                                size={24}
                                color={formFijado ? COLORS.primary : COLORS.text.tertiary}
                            />
                            <Text style={styles.fijarText}>Fijar anuncio (aparece primero)</Text>
                        </TouchableOpacity>
                    </ScrollView>

                    <TouchableOpacity
                        style={[styles.submitButton, creating && { opacity: 0.7 }]}
                        onPress={handleCreate}
                        disabled={creating}
                    >
                        {creating ? (
                            <ActivityIndicator color="#000" />
                        ) : (
                            <>
                                <Ionicons name="send" size={20} color="#000" />
                                <Text style={styles.submitButtonText}>PUBLICAR ANUNCIO</Text>
                            </>
                        )}
                    </TouchableOpacity>
                </View>
            </View>
        </Modal>
    );

    return (
        <View style={styles.container}>
            <View style={styles.header}>
                <Text style={styles.title}>Gestor de Anuncios</Text>
                <TouchableOpacity onPress={loadAnuncios} style={styles.refreshButton}>
                    <Ionicons name="refresh" size={24} color={COLORS.primary} />
                </TouchableOpacity>
            </View>

            <TouchableOpacity
                style={styles.createButton}
                onPress={() => setShowCreateModal(true)}
            >
                <Ionicons name="add-circle" size={24} color="#000" />
                <Text style={styles.createButtonText}>NUEVO ANUNCIO</Text>
            </TouchableOpacity>

            {loading ? (
                <ActivityIndicator size="large" color={COLORS.primary} style={{ marginTop: 20 }} />
            ) : (
                <FlatList
                    data={anuncios}
                    renderItem={renderItem}
                    keyExtractor={item => item.id.toString()}
                    contentContainerStyle={styles.listContent}
                    ListEmptyComponent={
                        <View style={styles.emptyContainer}>
                            <Ionicons name="megaphone-outline" size={48} color={COLORS.text.tertiary} />
                            <Text style={styles.emptyText}>No hay anuncios creados</Text>
                            <Text style={styles.emptySubtext}>Crea uno desde aqui o envia un mensaje desde Telegram</Text>
                        </View>
                    }
                />
            )}

            {renderCreateModal()}

            <ConfirmModal
                visible={showDeleteModal}
                title="Eliminar Anuncio"
                message="Estas seguro de que quieres eliminar este anuncio? Esta accion no se puede deshacer."
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
    createButton: {
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
    createButtonText: {
        fontWeight: 'bold',
        color: '#000',
        fontSize: 16,
    },
    listContent: {
        gap: SPACING.sm,
        paddingBottom: 100,
    },
    card: {
        backgroundColor: COLORS.surface,
        borderRadius: BORDER_RADIUS.md,
        overflow: 'hidden',
        borderWidth: 1,
        borderColor: COLORS.border.primary,
    },
    cardHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        padding: SPACING.md,
    },
    headerLeft: {
        flexDirection: 'row',
        alignItems: 'center',
        flex: 1,
        gap: SPACING.xs,
    },
    headerRight: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 4,
    },
    cardTitle: {
        color: COLORS.text.primary,
        fontWeight: 'bold',
        fontSize: 13,
        flex: 1,
        marginRight: 8,
    },
    tipoBadge: {
        paddingHorizontal: 6,
        paddingVertical: 2,
        borderRadius: 4,
    },
    tipoBadgeText: {
        fontSize: 9,
        fontWeight: 'bold',
        color: '#FFF',
    },
    statusBadge: {
        paddingHorizontal: 6,
        paddingVertical: 2,
        borderRadius: 4,
    },
    statusText: {
        fontSize: 9,
        fontWeight: 'bold',
        color: '#FFF',
    },
    cardBody: {
        borderTopWidth: 1,
        borderTopColor: COLORS.border.secondary,
        padding: SPACING.md,
    },
    mensajeText: {
        color: COLORS.text.secondary,
        fontSize: 13,
        lineHeight: 20,
        marginBottom: SPACING.sm,
    },
    mediaImage: {
        width: '100%',
        height: 180,
        borderRadius: BORDER_RADIUS.sm,
        marginBottom: SPACING.sm,
        backgroundColor: '#1a1a1a',
    },
    linkContainer: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 6,
        paddingVertical: SPACING.xs,
        marginBottom: SPACING.sm,
    },
    linkText: {
        color: COLORS.primary,
        fontSize: 12,
        flex: 1,
    },
    metaRow: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: SPACING.sm,
    },
    metaText: {
        color: COLORS.text.tertiary,
        fontSize: 11,
    },
    medioBadge: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 4,
    },
    medioText: {
        color: COLORS.text.tertiary,
        fontSize: 10,
        textTransform: 'uppercase',
    },
    cardActions: {
        flexDirection: 'row',
        gap: SPACING.xs,
    },
    actionButton: {
        flex: 1,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        padding: 8,
        borderRadius: BORDER_RADIUS.sm,
        borderWidth: 1,
        gap: 4,
        backgroundColor: COLORS.surface,
    },
    actionText: {
        fontSize: 11,
        fontWeight: '600',
    },
    // Modal styles
    modalOverlay: {
        flex: 1,
        backgroundColor: 'rgba(0,0,0,0.8)',
        justifyContent: 'flex-end',
    },
    modalContent: {
        backgroundColor: COLORS.surface,
        borderTopLeftRadius: BORDER_RADIUS.xl,
        borderTopRightRadius: BORDER_RADIUS.xl,
        maxHeight: '90%',
    },
    modalHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        padding: SPACING.lg,
        borderBottomWidth: 1,
        borderBottomColor: COLORS.border.primary,
    },
    modalTitle: {
        fontSize: 20,
        fontWeight: 'bold',
        color: COLORS.text.primary,
    },
    modalBody: {
        padding: SPACING.lg,
    },
    fieldLabel: {
        color: COLORS.text.secondary,
        fontSize: 12,
        fontWeight: 'bold',
        textTransform: 'uppercase',
        letterSpacing: 0.5,
        marginBottom: SPACING.xs,
        marginTop: SPACING.md,
    },
    textInput: {
        backgroundColor: COLORS.background,
        borderRadius: BORDER_RADIUS.md,
        padding: SPACING.md,
        color: COLORS.text.primary,
        fontSize: 14,
        borderWidth: 1,
        borderColor: COLORS.border.primary,
    },
    textArea: {
        minHeight: 100,
        textAlignVertical: 'top',
    },
    tipoPicker: {
        flexDirection: 'row',
        flexWrap: 'wrap',
        gap: SPACING.xs,
    },
    tipoOption: {
        paddingHorizontal: SPACING.md,
        paddingVertical: SPACING.sm,
        borderRadius: BORDER_RADIUS.full,
        borderWidth: 1,
    },
    tipoOptionText: {
        fontSize: 11,
        fontWeight: 'bold',
        color: COLORS.text.secondary,
    },
    mediaButtons: {
        flexDirection: 'row',
        gap: SPACING.sm,
    },
    mediaButton: {
        flex: 1,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        padding: SPACING.md,
        borderRadius: BORDER_RADIUS.md,
        borderWidth: 1,
        borderColor: COLORS.border.primary,
        borderStyle: 'dashed',
        gap: 8,
    },
    mediaButtonText: {
        color: COLORS.primary,
        fontWeight: '600',
        fontSize: 13,
    },
    mediaPreview: {
        marginTop: SPACING.sm,
        position: 'relative',
    },
    previewImage: {
        width: '100%',
        height: 150,
        borderRadius: BORDER_RADIUS.md,
        backgroundColor: '#1a1a1a',
    },
    videoPlaceholder: {
        width: '100%',
        height: 100,
        borderRadius: BORDER_RADIUS.md,
        backgroundColor: '#1a1a1a',
        justifyContent: 'center',
        alignItems: 'center',
        gap: 8,
    },
    videoPlaceholderText: {
        color: COLORS.text.secondary,
        fontSize: 12,
    },
    removeMedia: {
        position: 'absolute',
        top: 8,
        right: 8,
    },
    fijarToggle: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: SPACING.sm,
        marginTop: SPACING.lg,
        paddingVertical: SPACING.sm,
    },
    fijarText: {
        color: COLORS.text.secondary,
        fontSize: 14,
    },
    submitButton: {
        flexDirection: 'row',
        backgroundColor: COLORS.primary,
        padding: SPACING.lg,
        alignItems: 'center',
        justifyContent: 'center',
        gap: 10,
        margin: SPACING.lg,
        borderRadius: BORDER_RADIUS.lg,
        ...SHADOWS.md,
    },
    submitButtonText: {
        fontWeight: 'bold',
        color: '#000',
        fontSize: 16,
    },
    emptyContainer: {
        alignItems: 'center',
        marginTop: 40,
        gap: SPACING.sm,
    },
    emptyText: {
        color: COLORS.text.secondary,
        fontSize: 16,
        fontWeight: '600',
    },
    emptySubtext: {
        color: COLORS.text.tertiary,
        fontSize: 12,
        textAlign: 'center',
    },
});
