import React, { useRef, useState } from 'react';
import { View, StyleSheet, Modal, ActivityIndicator, Text, Pressable, StatusBar, Platform } from 'react-native';
import { WebView } from 'react-native-webview';
import { Ionicons } from '@expo/vector-icons';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { COLORS } from '../../constants/theme';
import { Config } from '../../config/config';

interface BackgroundRemoverWebViewProps {
    visible: boolean;
    imageUrl: string | null;
    onClose: () => void;
    onImageProcessed: (newImageUrl: string) => void;
}

export const BackgroundRemoverWebView: React.FC<BackgroundRemoverWebViewProps> = ({
    visible,
    imageUrl,
    onClose,
    onImageProcessed
}) => {
    // URL via API desde configuración centralizada
    const BASE_WEB_URL = Config.BG_REMOVER_ENDPOINT;
    const uri = imageUrl ? `${BASE_WEB_URL}?img=${encodeURIComponent(imageUrl)}` : BASE_WEB_URL;

    // Estado para controlar el modo de visualización
    const [viewMode, setViewMode] = useState<'loading_config' | 'debug' | 'invisible'>('loading_config');
    const insets = useSafeAreaInsets();
    const webViewRef = useRef<WebView>(null);
    // Aunque no lo usemos en la UI ahora mismo, lo definimos para evitar el error en el WebView prop
    const [loading, setLoading] = useState(true);
    // Estado para logs en tiempo real (Invisible Mode)
    const [progressLog, setProgressLog] = useState("Iniciando...");

    // 1. Obtener configuración del servidor al montar
    React.useEffect(() => {
        if (visible) {
            // Reset logs
            setProgressLog("Iniciando...");
            checkServerSettings();
        }
    }, [visible]);

    const checkServerSettings = async () => {
        try {
            const response = await fetch(Config.SETTINGS_BG_REMOVER_MODE);
            const data = await response.json();

            if (data.success && data.value === 'invisible') {
                setViewMode('invisible');
            } else {
                setViewMode('debug');
            }
        } catch (error) {
            setViewMode('debug');
        }
    };

    const INJECTED_JAVASCRIPT = `
      (function() {
        window.isReactNative = true;
      })();
    `;

    const handleMessage = (event: any) => {
        try {
            const data = JSON.parse(event.nativeEvent.data);
            if (data.type === 'IMAGE_PROCESSED' && data.url) {
                onImageProcessed(data.url);
            }
            if (data.type === 'LOG') {
                setProgressLog(data.payload);
            }
            if (data.type === 'ERROR') {
                // Si falla en invisible, mostramos alerta nativa
                if (viewMode === 'invisible') {
                    // alert('Error IA: ' + data.payload); 
                    // Opcional: Cerrar silenciosamente o avisar
                }
            }
            if (data.type === 'CLOSE') {
                onClose();
            }
        } catch (e) {
            console.log("Web Message Error:", e);
        }
    };

    if (!visible) return null;

    // RENDERIZADO INVISIBLE (Nativo puro)
    if (viewMode === 'invisible') {
        return (
            <Modal visible={visible} transparent animationType="fade" onRequestClose={onClose}>
                <View style={[styles.loadingOverlay, { backgroundColor: 'rgba(0,0,0,0.8)' }]}>
                    <StatusBar barStyle="light-content" />

                    <View style={styles.nativeLoaderBox}>
                        <ActivityIndicator size="large" color={COLORS.primary} />
                        <Text style={styles.nativeLoaderText}>PROCESANDO CON IA...</Text>
                        <Text style={styles.nativeLoaderSub}>{progressLog}</Text>
                    </View>

                    {/* El WebView existe pero está oculto (height 0) */}
                    <View style={{ height: 0, width: 0, overflow: 'hidden' }}>
                        <WebView
                            ref={webViewRef}
                            source={{ uri }}
                            javaScriptEnabled={true}
                            domStorageEnabled={true}
                            injectedJavaScript={INJECTED_JAVASCRIPT}
                            onMessage={handleMessage}
                            // Importante para que corra en background (a veces)
                            androidLayerType="hardware"
                        />
                    </View>
                </View>
            </Modal>
        );
    }

    // RENDERIZADO DEBUG (El de siempre)
    return (
        <Modal visible={visible} animationType="slide" presentationStyle="fullScreen" onRequestClose={onClose}>
            <View style={[styles.container, { backgroundColor: '#000' }]}>
                <StatusBar barStyle="light-content" />
                <View style={[styles.header, { paddingTop: insets.top, height: 60 + insets.top }]}>
                    <Text style={styles.title}>MAGIC ERASER DEBUG</Text>
                    <Pressable onPress={onClose} style={[styles.closeBtn, { top: insets.top }]}>
                        <Ionicons name="close" size={26} color="#FFF" />
                    </Pressable>
                </View>

                <View style={styles.webViewContainer}>
                    {viewMode === 'loading_config' ? (
                        <ActivityIndicator size="small" color="#FFF" style={{ marginTop: 20 }} />
                    ) : (
                        <WebView
                            ref={webViewRef}
                            source={{ uri }}
                            style={{ flex: 1, backgroundColor: '#000' }}
                            javaScriptEnabled={true}
                            domStorageEnabled={true}
                            onLoadStart={() => setLoading(true)}
                            onLoadEnd={() => setLoading(false)}
                            injectedJavaScript={INJECTED_JAVASCRIPT}
                            onMessage={handleMessage}
                        />
                    )}
                </View>
            </View>
        </Modal>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1 },
    header: {
        backgroundColor: '#111',
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        borderBottomWidth: 1,
        borderBottomColor: '#333'
    },
    title: {
        color: COLORS.primary,
        fontSize: 16,
        fontWeight: 'bold',
        letterSpacing: 1
    },
    closeBtn: {
        position: 'absolute',
        right: 15,
        height: 60,
        justifyContent: 'center',
        paddingHorizontal: 10
    },
    webViewContainer: {
        flex: 1,
        position: 'relative'
    },
    loadingOverlay: {
        ...StyleSheet.absoluteFillObject,
        backgroundColor: '#000',
        justifyContent: 'center',
        alignItems: 'center',
        zIndex: 10
    },
    // Estilos nuevos para modo invisible
    nativeLoaderBox: {
        backgroundColor: '#1A1A1A',
        padding: 30,
        borderRadius: 20,
        alignItems: 'center',
        borderWidth: 1,
        borderColor: '#333',
        shadowColor: "#000",
        shadowOffset: { width: 0, height: 10 },
        shadowOpacity: 0.5,
        shadowRadius: 20,
        elevation: 10
    },
    nativeLoaderText: {
        color: COLORS.primary,
        marginTop: 20,
        fontSize: 16,
        fontWeight: 'bold',
        letterSpacing: 1
    },
    nativeLoaderSub: {
        color: '#666',
        marginTop: 8,
        fontSize: 12
    }
});
