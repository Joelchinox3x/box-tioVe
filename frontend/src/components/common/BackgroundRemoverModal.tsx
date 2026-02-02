import React, { useRef, useState, useEffect } from 'react';
import { View, Modal, StyleSheet, ActivityIndicator, Text, TouchableOpacity, Platform, StatusBar, UIManager, Linking } from 'react-native';
import { WebView } from 'react-native-webview';
import * as FileSystem from 'expo-file-system';
import { Ionicons } from '@expo/vector-icons';
import { COLORS } from '../../constants/theme';

interface Props {
    visible: boolean;
    imageUri: string | null;
    onClose: () => void;
    onSuccess: (resultUri: string) => void;
}

export const BackgroundRemoverModal: React.FC<Props> = ({ visible, imageUri, onClose, onSuccess }) => {
    const webViewRef = useRef<WebView>(null);
    const [status, setStatus] = useState('Iniciando Motor IA...');
    // Detectar entorno: En desarrollo usamos IP local (ajustar según tu red), en prod boxtiove.com
    const WEB_URL = __DEV__
        ? 'http://10.0.2.2:8080/ia/remover' // Android Emulator Host
        : 'https://boxtiove.com/ia/remover';

    // Safety Check for Expo Go (WebView Native Module presence)
    // @ts-ignore
    const isWebViewAvailable = !!(UIManager.getViewManagerConfig('RNCWebView'));

    const handleOpenBrowser = () => {
        Linking.openURL(WEB_URL);
        onClose();
    };

    const handleLoadEnd = async () => {
        if (!imageUri) return;

        try {
            setStatus('Enviando imagen al motor...');
            // Leer imagen local en Base64
            const base64 = await FileSystem.readAsStringAsync(imageUri, { encoding: FileSystem.EncodingType.Base64 });
            const payload = `data:image/jpeg;base64,${base64}`;

            // Enviar a la Web
            const script = `
                window.postMessage(JSON.stringify({ type: 'REMOVE_BG', payload: "${payload}" }));
                true;
            `;
            webViewRef.current?.injectJavaScript(script);
        } catch (e) {
            console.log("Error reading file", e);
            setStatus('Error leyendo archivo local');
        }
    };

    const handleMessage = (event: any) => {
        try {
            const data = JSON.parse(event.nativeEvent.data);

            if (data.type === 'SUCCESS') {
                // data.payload es el base64 sin fondo
                // Guardarlo en caché local para usarlo como URI
                saveProcessedImage(data.payload);
            } else if (data.type === 'ERROR') {
                setStatus('Error del Motor: ' + data.payload);
            } else if (data.type === 'READY') {
                // El motor web ya cargó, podemos enviar (si no lo hicimos en onLoadEnd)
                // handleLoadEnd(); depende de la estrategia
            }
        } catch (e) {
            console.log("Message Error", e);
        }
    };

    const saveProcessedImage = async (base64Data: string) => {
        try {
            const filename = `bg_removed_${Date.now()}.png`;
            const path = `${FileSystem.cacheDirectory}${filename}`;
            // El payload ya viene con prefix data:image/png;base64, ? verificar
            // Si viene con prefix, hay que quitarlo para writeAsStringAsync
            let data = base64Data;
            if (data.includes(',')) {
                data = data.split(',')[1];
            }

            await FileSystem.writeAsStringAsync(path, data, { encoding: FileSystem.EncodingType.Base64 });
            onSuccess(path);
            onClose();
        } catch (e) {
            console.error("Save Error", e);
            setStatus('Error guardando resultado');
        }
    };

    if (!visible) return null;

    return (
        <Modal visible={visible} animationType="slide" presentationStyle="pageSheet" onRequestClose={onClose}>
            <View style={styles.container}>
                <View style={styles.header}>
                    <Text style={styles.title}>MAGIC ERASER PRO</Text>
                    <TouchableOpacity onPress={onClose} style={styles.closeBtn}>
                        <Ionicons name="close" size={24} color="#FFF" />
                    </TouchableOpacity>
                </View>

                {isWebViewAvailable ? (
                    <>
                        <View style={styles.statusContainer}>
                            <Text style={styles.statusText}>{status}</Text>
                            {status.includes('Error') && (
                                <TouchableOpacity onPress={onClose} style={{ marginTop: 10 }}>
                                    <Text style={{ color: COLORS.error }}>Cerrar</Text>
                                </TouchableOpacity>
                            )}
                        </View>

                        <WebView
                            ref={webViewRef}
                            source={{ uri: WEB_URL }}
                            style={{ flex: 1, backgroundColor: '#000' }}
                            containerStyle={{ backgroundColor: '#000' }}
                            onLoadEnd={handleLoadEnd}
                            onMessage={handleMessage}
                            javaScriptEnabled={true}
                            domStorageEnabled={true}
                            startInLoadingState={true}
                            renderLoading={() => <ActivityIndicator size="large" color={COLORS.primary} style={{ position: 'absolute', top: '50%', left: '50%' }} />}
                        />
                    </>
                ) : (
                    <View style={styles.fallbackContainer}>
                        <Ionicons name="alert-circle-outline" size={60} color={COLORS.primary} />
                        <Text style={styles.fallbackTitle}>Expo Go Detectado</Text>
                        <Text style={styles.fallbackText}>
                            La tecnología "In-App WebView" requiere una Build de Desarrollo.
                            En Expo Go, usaremos el navegador externo.
                        </Text>
                        <TouchableOpacity style={styles.fallbackBtn} onPress={handleOpenBrowser}>
                            <Text style={styles.fallbackBtnText}>Abrir en Navegador</Text>
                            <Ionicons name="open-outline" size={20} color="#000" />
                        </TouchableOpacity>
                        <Text style={styles.fallbackNote}>Nota: Deberás guardar la imagen y volver a seleccionarla.</Text>
                    </View>
                )}
            </View>
        </Modal>
    );
};

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#000' },
    header: {
        height: 60,
        backgroundColor: '#111',
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        paddingHorizontal: 20,
        paddingTop: Platform.OS === 'ios' ? 10 : 0
    },
    title: { color: COLORS.primary, fontWeight: 'bold', fontSize: 16, letterSpacing: 1 },
    closeBtn: { position: 'absolute', right: 20 },
    statusContainer: {
        position: 'absolute',
        top: 70,
        left: 0,
        right: 0,
        zIndex: 10,
        alignItems: 'center',
        pointerEvents: 'none'
    },
    statusText: { color: '#FFF', textShadowColor: '#000', textShadowRadius: 3, fontWeight: '600' },
    fallbackContainer: { flex: 1, justifyContent: 'center', alignItems: 'center', padding: 30, gap: 20 },
    fallbackTitle: { color: '#FFF', fontSize: 22, fontWeight: 'bold', marginTop: 10 },
    fallbackText: { color: '#BBB', fontSize: 16, textAlign: 'center', lineHeight: 24 },
    fallbackBtn: { flexDirection: 'row', backgroundColor: COLORS.primary, paddingVertical: 15, paddingHorizontal: 30, borderRadius: 25, alignItems: 'center', gap: 10, marginTop: 20 },
    fallbackBtnText: { color: '#000', fontWeight: 'bold', fontSize: 16 },
    fallbackNote: { color: '#666', fontSize: 12, marginTop: 20, textAlign: 'center' }
});
