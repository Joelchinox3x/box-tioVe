import React, { useRef, useState } from 'react';
import { View, StyleSheet, Modal, ActivityIndicator, Text, Pressable, StatusBar, Platform } from 'react-native';
import { WebView } from 'react-native-webview';
import { Ionicons } from '@expo/vector-icons';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { COLORS } from '../../constants/theme';

interface BackgroundRemoverWebViewProps {
    visible: boolean;
    imageUrl: string | null;
    onClose: () => void;
    onHistoryChange?: (canGoBack: boolean) => void;
    onImageProcessed: (newImageUrl: string) => void;
}

export const BackgroundRemoverWebView: React.FC<BackgroundRemoverWebViewProps> = ({
    visible,
    imageUrl,
    onClose,
    onImageProcessed
}) => {
    const webViewRef = useRef<WebView>(null);
    const insets = useSafeAreaInsets();
    const [loading, setLoading] = useState(true);

    // URL base de tu herramienta web
    // MODIFICA ESTO con la URL real de tu página web que tiene el cropper
    // Se le pasa ?img=... para que pre-cargue la imagen
    const BASE_WEB_URL = "https://boxtiove.com/tools/background-remover";

    const uri = imageUrl ? `${BASE_WEB_URL}?img=${encodeURIComponent(imageUrl)}&mode=embed` : BASE_WEB_URL;

    // Inyectamos script para escuchar mensajes de la web (si la web envía postMessage)
    const INJECTED_JAVASCRIPT = `
      (function() {
        // Escuchar eventos personalizados de tu web si usas window.postMessage
        // Ejemplo: window.parent.postMessage({ type: 'IMAGE_PROCESSED', url: '...' }, '*')
        
        // Opcional: Interceptar clicks o eventos para comunicar a React Native
        window.isReactNative = true;
      })();
    `;

    const handleMessage = (event: any) => {
        try {
            const data = JSON.parse(event.nativeEvent.data);
            if (data.type === 'IMAGE_PROCESSED' && data.url) {
                // La web nos devolvió la imagen lista
                onImageProcessed(data.url);
            }
            if (data.type === 'CLOSE') {
                onClose();
            }
        } catch (e) {
            console.log("Web Message Error:", e);
        }
    };

    if (!visible) return null;

    return (
        <Modal visible={visible} animationType="slide" presentationStyle="pageSheet" onRequestClose={onClose}>
            <View style={[styles.container, { backgroundColor: '#000' }]}>
                {/* Header Simple */}
                <View style={[styles.header, { marginTop: Platform.OS === 'android' ? 0 : insets.top }]}>
                    <Text style={styles.title}>Quitar Fondo (Web)</Text>
                    <Pressable onPress={onClose} style={styles.closeBtn}>
                        <Ionicons name="close" size={26} color="#FFF" />
                    </Pressable>
                </View>

                <View style={styles.webViewContainer}>
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
                        // Soporte para uploads en Android
                        allowFileAccess={true}
                        allowFileAccessFromFileURLs={true}
                        allowingReadAccessToURL={Platform.OS === 'ios' ? '*' : undefined}
                    />

                    {loading && (
                        <View style={styles.loadingOverlay}>
                            <ActivityIndicator size="large" color={COLORS.primary} />
                            <Text style={styles.loadingText}>Cargando herramienta...</Text>
                        </View>
                    )}
                </View>
            </View>
        </Modal>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1 },
    header: {
        height: 60,
        backgroundColor: '#151515',
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        borderBottomWidth: 1,
        borderBottomColor: '#333'
    },
    title: {
        color: '#FFF',
        fontSize: 16,
        fontWeight: 'bold'
    },
    closeBtn: {
        position: 'absolute',
        right: 15,
        height: '100%',
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
    loadingText: {
        color: '#888',
        marginTop: 10,
        fontSize: 12
    }
});
