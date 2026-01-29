import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Image, ActivityIndicator, Alert, ScrollView, Platform, Modal, FlatList } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import * as ImagePicker from 'expo-image-picker';
import { AdminService } from '../../services/AdminService';
import { COLORS, SPACING, BORDER_RADIUS, SHADOWS, TYPOGRAPHY } from '../../constants/theme';

interface Logo {
    id: number;
    nombre_archivo: string;
    tipo: 'card' | 'pdf' | 'header';
    url: string;
    etiqueta?: string;
    activo: boolean;
    dimensiones?: string;
    fecha_subida: string;
}

type LogoType = 'card' | 'pdf' | 'header';

export default function AdminBrandingScreen() {
    const [activeLogos, setActiveLogos] = useState<Record<string, any>>({});
    const [loading, setLoading] = useState(true);
    const [historyModalVisible, setHistoryModalVisible] = useState(false);
    const [selectedType, setSelectedType] = useState<LogoType>('card');
    const [history, setHistory] = useState<Logo[]>([]);
    const [loadingHistory, setLoadingHistory] = useState(false);
    const [pendingImages, setPendingImages] = useState<Record<string, string | null>>({});
    const [uploading, setUploading] = useState<string | null>(null);
    const [activeTab, setActiveTab] = useState<LogoType>('card');

    useEffect(() => {
        loadData();
    }, []);

    const loadData = async () => {
        try {
            setLoading(true);
            const data = await AdminService.getActiveLogos();
            if (data.success) {
                setActiveLogos(data.logos);
            }
        } catch (error) {
            console.error("Error loading logos:", error);
        } finally {
            setLoading(false);
        }
    };

    const openHistory = async (type: LogoType) => {
        setSelectedType(type);
        setHistoryModalVisible(true);
        setLoadingHistory(true);
        try {
            const data = await AdminService.getLogosHistory(type);
            if (data.success) {
                setHistory(data.logos);
            }
        } catch (error) {
            Alert.alert("Error", "No se pudo cargar el historial.");
        } finally {
            setLoadingHistory(false);
        }
    };

    const handlePickImage = async (type: LogoType) => {
        const result = await ImagePicker.launchImageLibraryAsync({
            mediaTypes: ImagePicker.MediaTypeOptions.Images,
            allowsEditing: true,
            aspect: type === 'header' ? [3, 1] : [1, 1],
            quality: 1,
        });

        if (!result.canceled) {
            setPendingImages(prev => ({ ...prev, [type]: result.assets[0].uri }));
        }
    };

    const handleSave = async (type: LogoType) => {
        const uri = pendingImages[type];
        if (!uri) return;

        try {
            setUploading(type);
            const formData = new FormData();
            const filename = uri.split('/').pop() || 'logo.jpg';
            const match = /\.(\w+)$/.exec(filename);
            const mimeType = match ? `image/${match[1]}` : 'image/jpeg';

            if (Platform.OS === 'web') {
                // En web necesitamos convertir la URI a Blob
                const response = await fetch(uri);
                const blob = await response.blob();
                formData.append('logo', blob, filename);
            } else {
                // @ts-ignore
                formData.append('logo', {
                    uri,
                    name: filename,
                    type: mimeType,
                });
            }

            formData.append('tipo', type);
            formData.append('etiqueta', `Logo ${type.toUpperCase()} ${new Date().toLocaleDateString()}`);

            const data = await AdminService.uploadLogo(formData);
            if (data.success) {
                setActiveLogos(data.logos);
                setPendingImages(prev => ({ ...prev, [type]: null }));
                Alert.alert("Éxito", "Logo actualizado correctamente.");
            } else {
                Alert.alert("Error", data.message || "No se pudo subir el logo.");
            }
        } catch (error) {
            console.error("Error uploading logo:", error);
            Alert.alert("Error", "Ocurrió un error al procesar la subida.");
        } finally {
            setUploading(null);
        }
    };

    const handleActivate = async (id: number) => {
        try {
            const data = await AdminService.setActiveLogo(id);
            if (data.success) {
                setActiveLogos(data.logos);
                setHistoryModalVisible(false);
                Alert.alert("Éxito", "Logo cambiado correctamente.");
            }
        } catch (error) {
            Alert.alert("Error", "No se pudo activar el logo.");
        }
    };

    const handleDiscard = (type: LogoType) => {
        setPendingImages(prev => ({ ...prev, [type]: null }));
    };

    const renderTabs = () => (
        <View style={styles.tabBar}>
            {(['card', 'pdf', 'header'] as LogoType[]).map((type) => (
                <TouchableOpacity
                    key={type}
                    style={[styles.tabItem, activeTab === type && styles.activeTabItem]}
                    onPress={() => setActiveTab(type)}
                >
                    <Ionicons
                        name={type === 'card' ? 'id-card-outline' : type === 'pdf' ? 'document-text-outline' : 'browsers-outline'}
                        size={18}
                        color={activeTab === type ? COLORS.primary : '#888'}
                    />
                    <Text style={[styles.tabText, activeTab === type && styles.activeTabText]}>
                        {type.toUpperCase()}
                    </Text>
                    {activeTab === type && <View style={styles.activeIndicator} />}
                </TouchableOpacity>
            ))}
        </View>
    );

    const renderLogoSection = (type: LogoType, title: string, desc: string) => {
        const logo = activeLogos[type];
        const pendingUri = pendingImages[type];
        const isUploading = uploading === type;
        const displayUri = pendingUri || (logo ? logo.url : null);

        return (
            <View style={styles.sectionCard}>
                <View style={styles.sectionHeader}>
                    <View style={{ flex: 1 }}>
                        <Text style={styles.sectionTitle}>{title}</Text>
                        <Text style={styles.sectionDesc}>{desc}</Text>
                    </View>
                    <TouchableOpacity style={styles.historyBtn} onPress={() => openHistory(type)}>
                        <Ionicons name="images-outline" size={20} color={COLORS.primary} />
                        <Text style={styles.historyBtnText}>Galería</Text>
                    </TouchableOpacity>
                </View>

                {pendingUri && (
                    <View style={styles.previewBadge}>
                        <Text style={styles.previewBadgeText}>VISTA PREVIA LOCAL</Text>
                    </View>
                )}

                <View style={styles.previewBox}>
                    <View style={styles.transparencyBg}>
                        {/* Repetición manual de patrón para compatibilidad máxima */}
                        {[...Array(6)].map((_, i) => (
                            <View key={i} style={{ flexDirection: 'row' }}>
                                {[...Array(10)].map((_, j) => (
                                    <View key={j} style={[styles.tile, (i + j) % 2 === 0 ? styles.tileLight : styles.tileDark]} />
                                ))}
                            </View>
                        ))}
                    </View>

                    {displayUri ? (
                        <Image source={{ uri: displayUri }} style={styles.logoImg} resizeMode="contain" />
                    ) : (
                        <View style={styles.noLogo}>
                            <Ionicons name="image-outline" size={40} color="#444" />
                            <Text style={styles.noLogoText}>Sin logo activo</Text>
                        </View>
                    )}
                </View>

                <View style={styles.buttonRow}>
                    {!pendingUri ? (
                        <TouchableOpacity
                            style={styles.pickButtonFull}
                            onPress={() => handlePickImage(type)}
                            disabled={!!uploading}
                        >
                            <Ionicons name="cloud-upload-outline" size={20} color="#FFF" />
                            <Text style={styles.buttonText}>{logo ? "Cambiar Logo" : "Seleccionar Logo"}</Text>
                        </TouchableOpacity>
                    ) : (
                        <>
                            <TouchableOpacity
                                style={styles.discardButton}
                                onPress={() => handleDiscard(type)}
                                disabled={!!uploading}
                            >
                                <Ionicons name="trash-outline" size={20} color="#FF4444" />
                            </TouchableOpacity>

                            <TouchableOpacity
                                style={styles.saveButton}
                                onPress={() => handleSave(type)}
                                disabled={!!uploading}
                            >
                                {isUploading ? (
                                    <ActivityIndicator size="small" color="#000" />
                                ) : (
                                    <>
                                        <Ionicons name="checkmark-circle-outline" size={20} color="#000" />
                                        <Text style={[styles.buttonText, { color: '#000' }]}>Subir y Aplicar</Text>
                                    </>
                                )}
                            </TouchableOpacity>
                        </>
                    )}
                </View>
            </View>
        );
    };

    if (loading) {
        return (
            <View style={styles.loadingContainer}>
                <ActivityIndicator size="large" color={COLORS.primary} />
            </View>
        );
    }

    return (
        <View style={styles.container}>
            <ScrollView contentContainerStyle={styles.scrollContent}>
                <Text style={styles.mainTitle}>Identidad Visual</Text>
                <Text style={styles.mainSubtitle}>Configura los logos para diferentes partes de la app</Text>

                {renderTabs()}

                <View style={{ marginTop: 10 }}>
                    {activeTab === 'card' && renderLogoSection(
                        'card',
                        'Credencial (Fighter Card)',
                        'Logo que aparece en el diseño de las tarjetas de los peleadores.'
                    )}

                    {activeTab === 'pdf' && renderLogoSection(
                        'pdf',
                        'Documentos (PDF)',
                        'Utilizado en reportes, boletas y documentos oficiales.'
                    )}

                    {activeTab === 'header' && renderLogoSection(
                        'header',
                        'Encabezado (App)',
                        'Logo principal que aparece en el header de la aplicación.'
                    )}
                </View>
            </ScrollView>

            <Modal
                visible={historyModalVisible}
                animationType="slide"
                transparent={true}
                onRequestClose={() => setHistoryModalVisible(false)}
            >
                <View style={styles.modalOverlay}>
                    <View style={styles.modalContent}>
                        <View style={styles.modalHeader}>
                            <Text style={styles.modalTitle}>Historial: {selectedType.toUpperCase()}</Text>
                            <TouchableOpacity onPress={() => setHistoryModalVisible(false)}>
                                <Ionicons name="close" size={24} color="#FFF" />
                            </TouchableOpacity>
                        </View>

                        {loadingHistory ? (
                            <ActivityIndicator style={{ margin: 50 }} color={COLORS.primary} />
                        ) : (
                            <FlatList
                                data={history}
                                keyExtractor={(item) => item.id.toString()}
                                contentContainerStyle={{ padding: 15 }}
                                numColumns={2}
                                renderItem={({ item }) => (
                                    <TouchableOpacity
                                        style={[styles.gridItem, item.activo && styles.activeGridItem]}
                                        onPress={() => !item.activo && handleActivate(item.id)}
                                    >
                                        <View style={styles.gridThumbContainer}>
                                            {/* Checkerboard para el historial también */}
                                            <View style={styles.gridTransparencyBg}>
                                                {[...Array(4)].map((_, i) => (
                                                    <View key={i} style={{ flexDirection: 'row' }}>
                                                        {[...Array(4)].map((_, j) => (
                                                            <View key={j} style={[styles.miniTile, (i + j) % 2 === 0 ? styles.tileLight : styles.tileDark]} />
                                                        ))}
                                                    </View>
                                                ))}
                                            </View>
                                            <Image source={{ uri: item.url }} style={styles.gridThumb} resizeMode="contain" />
                                        </View>
                                        <View style={styles.gridInfo}>
                                            <Text style={styles.gridLabel} numberOfLines={1}>{item.etiqueta || 'Sin etiqueta'}</Text>
                                            <Text style={styles.gridDate}>{new Date(item.fecha_subida).toLocaleDateString()}</Text>
                                        </View>
                                        {item.activo && (
                                            <View style={styles.gridActiveBadge}>
                                                <Ionicons name="checkmark-circle" size={16} color="#000" />
                                            </View>
                                        )}
                                    </TouchableOpacity>
                                )}
                                ListEmptyComponent={
                                    <Text style={{ textAlign: 'center', color: '#666', marginTop: 20 }}>No hay historial para este tipo.</Text>
                                }
                            />
                        )}
                    </View>
                </View>
            </Modal>
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: COLORS.background,
    },
    scrollContent: {
        padding: 20,
        paddingBottom: 40,
    },
    loadingContainer: {
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
        backgroundColor: COLORS.background,
    },
    mainTitle: {
        fontSize: 28,
        fontWeight: '900',
        color: '#FFF',
        marginBottom: 5,
    },
    mainSubtitle: {
        fontSize: 14,
        color: '#888',
        marginBottom: 20,
    },
    tabBar: {
        flexDirection: 'row',
        backgroundColor: 'rgba(255,255,255,0.03)',
        borderRadius: 15,
        padding: 5,
        marginBottom: 20,
        borderWidth: 1,
        borderColor: '#222',
    },
    tabItem: {
        flex: 1,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        paddingVertical: 12,
        borderRadius: 12,
        gap: 8,
        position: 'relative',
    },
    activeTabItem: {
        backgroundColor: '#1A1A1A',
    },
    tabText: {
        color: '#666',
        fontSize: 11,
        fontWeight: '900',
        letterSpacing: 0.5,
    },
    activeTabText: {
        color: COLORS.primary,
    },
    activeIndicator: {
        position: 'absolute',
        bottom: 8,
        width: 4,
        height: 4,
        borderRadius: 2,
        backgroundColor: COLORS.primary,
    },
    sectionCard: {
        backgroundColor: COLORS.surface,
        borderRadius: 20,
        padding: 20,
        marginBottom: 20,
        ...SHADOWS.md,
    },
    sectionHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'flex-start',
        marginBottom: 15,
    },
    sectionTitle: {
        fontSize: 18,
        fontWeight: 'bold',
        color: '#FFF',
    },
    sectionDesc: {
        fontSize: 12,
        color: '#AAA',
        maxWidth: '70%',
        marginTop: 2,
    },
    historyBtn: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: 'rgba(212, 175, 55, 0.1)',
        paddingHorizontal: 10,
        paddingVertical: 6,
        borderRadius: 10,
        gap: 5,
    },
    historyBtnText: {
        color: COLORS.primary,
        fontSize: 12,
        fontWeight: 'bold',
    },
    previewBox: {
        width: '100%',
        height: 180,
        backgroundColor: '#222',
        borderRadius: 20,
        borderWidth: 1,
        borderColor: '#333',
        justifyContent: 'center',
        alignItems: 'center',
        marginBottom: 15,
        overflow: 'hidden',
        position: 'relative',
    },
    transparencyBg: {
        position: 'absolute',
        top: 0,
        left: 0,
        right: 0,
        bottom: 0,
        opacity: 0.15,
    },
    tile: {
        width: 40,
        height: 40,
    },
    tileLight: {
        backgroundColor: '#CCC',
    },
    tileDark: {
        backgroundColor: '#FFF',
    },
    logoImg: {
        width: '85%',
        height: '85%',
        zIndex: 1,
    },
    noLogo: {
        alignItems: 'center',
        zIndex: 1,
    },
    noLogoText: {
        color: '#666',
        marginTop: 8,
        fontSize: 12,
        fontWeight: 'bold',
    },
    previewBadge: {
        position: 'absolute',
        top: 60,
        right: 30,
        backgroundColor: COLORS.primary,
        paddingHorizontal: 8,
        paddingVertical: 4,
        borderRadius: 6,
        zIndex: 10,
        ...SHADOWS.sm,
    },
    previewBadgeText: {
        color: '#000',
        fontSize: 10,
        fontWeight: '900',
    },
    buttonRow: {
        flexDirection: 'row',
        gap: 12,
    },
    pickButtonFull: {
        flex: 1,
        flexDirection: 'row',
        backgroundColor: '#333',
        paddingVertical: 14,
        borderRadius: 15,
        alignItems: 'center',
        justifyContent: 'center',
        gap: 10,
        borderWidth: 1,
        borderColor: '#444',
    },
    discardButton: {
        width: 55,
        backgroundColor: 'rgba(255, 68, 68, 0.1)',
        borderRadius: 15,
        alignItems: 'center',
        justifyContent: 'center',
        borderWidth: 1,
        borderColor: 'rgba(255, 68, 68, 0.2)',
    },
    saveButton: {
        flex: 1,
        flexDirection: 'row',
        backgroundColor: COLORS.primary,
        paddingVertical: 14,
        borderRadius: 15,
        alignItems: 'center',
        justifyContent: 'center',
        gap: 10,
        ...SHADOWS.md,
    },
    buttonText: {
        color: '#FFF',
        fontWeight: '900',
        fontSize: 14,
        letterSpacing: 0.5,
    },
    modalOverlay: {
        flex: 1,
        backgroundColor: 'rgba(0,0,0,0.9)',
        justifyContent: 'flex-end',
    },
    modalContent: {
        backgroundColor: '#111',
        borderTopLeftRadius: 35,
        borderTopRightRadius: 35,
        height: '85%',
        ...SHADOWS.lg,
    },
    modalHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        padding: 25,
        borderBottomWidth: 1,
        borderBottomColor: '#222',
    },
    modalTitle: {
        color: '#FFF',
        fontSize: 20,
        fontWeight: '900',
        textTransform: 'uppercase',
        letterSpacing: 1,
    },
    gridItem: {
        flex: 1,
        maxWidth: '48%',
        backgroundColor: '#1A1A1A',
        borderRadius: 20,
        margin: '1%',
        padding: 10,
        borderWidth: 1,
        borderColor: '#222',
    },
    activeGridItem: {
        borderColor: COLORS.primary,
        backgroundColor: 'rgba(212, 175, 55, 0.05)',
    },
    gridThumbContainer: {
        width: '100%',
        height: 120,
        backgroundColor: '#000',
        borderRadius: 15,
        overflow: 'hidden',
        justifyContent: 'center',
        alignItems: 'center',
        position: 'relative',
    },
    gridTransparencyBg: {
        position: 'absolute',
        top: 0,
        left: 0,
        right: 0,
        bottom: 0,
        opacity: 0.1,
    },
    miniTile: {
        width: 30,
        height: 30,
    },
    gridThumb: {
        width: '80%',
        height: '80%',
        zIndex: 1,
    },
    gridInfo: {
        marginTop: 10,
        alignItems: 'center',
    },
    gridLabel: {
        color: '#FFF',
        fontWeight: 'bold',
        fontSize: 12,
    },
    gridDate: {
        color: '#666',
        fontSize: 10,
        marginTop: 2,
    },
    gridActiveBadge: {
        position: 'absolute',
        top: 10,
        right: 10,
        backgroundColor: COLORS.primary,
        borderRadius: 10,
        padding: 2,
    }
});
