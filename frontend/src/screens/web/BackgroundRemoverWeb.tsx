import React, { useEffect, useState } from 'react';
import { View, Text, ActivityIndicator, StyleSheet, Platform } from 'react-native';
import { removeBackground } from "@imgly/background-removal";

export default function BackgroundRemoverWeb() {
    const [status, setStatus] = useState('Esperando imagen...');
    const [progress, setProgress] = useState(0);

    useEffect(() => {
        if (Platform.OS !== 'web') return;

        // Escuchar mensajes de la App Nativa
        // Formato esperado: { type: 'REMOVE_BG', payload: 'data:image/jpeg;base64,...' }
        const handleMessage = async (event: any) => {
            try {
                // Validación de seguridad básica (opcional, dependiendo del origen)

                let data = event.data;
                // Si viene como string JSON (común en postMessage de RNWebView)
                if (typeof data === 'string') {
                    try { data = JSON.parse(data); } catch (e) { return; }
                }

                if (data?.type === 'REMOVE_BG') {
                    const imageSrc = data.payload;
                    processImage(imageSrc);
                }
            } catch (err) {
                console.error("Error handling message", err);
                sendToNative('ERROR', "Error al recibir mensaje");
            }
        };

        window.addEventListener('message', handleMessage);
        // Notificar a la App que estamos listos
        sendToNative('READY', null);

        return () => window.removeEventListener('message', handleMessage);
    }, []);

    const sendToNative = (type: string, payload: any) => {
        const message = JSON.stringify({ type, payload });
        // @ts-ignore
        if (window.ReactNativeWebView) {
            // @ts-ignore
            window.ReactNativeWebView.postMessage(message);
        } else {
            console.log("Not in WebView:", message);
        }
    };

    const processImage = async (imageSrc: string) => {
        try {
            setStatus('Procesando IA...');
            setProgress(10);

            // Cargar librería dinámicamente si no está (o usar la importada)
            // Nota: Usamos import dinámico ocdn si falla el import directo en algunos bundlers,
            // pero probaremos el import directo primero.

            // Configuración óptima para móviles
            const config = {
                debug: true,
                model: 'isnet_fp16', // Modelo ligero standard
                output: { format: 'image/png' as const, quality: 0.8 },
                progress: (key: string, current: number, total: number) => {
                    // key: 'fetch' | 'compute'
                    const p = Math.round((current / total) * 100);
                    setProgress(p);
                    setStatus(`IA ${key}: ${p}%`);
                }
            };

            const blob = await removeBackground(imageSrc, config);

            setStatus('Finalizando...');
            // Convertir Blob a Base64 para devolver a la App
            const reader = new FileReader();
            reader.onloadend = () => {
                const base64data = reader.result as string;
                sendToNative('SUCCESS', base64data);
                setStatus('¡Listo!');
            };
            reader.readAsDataURL(blob);

        } catch (error: any) {
            console.error("Processing Error", error);
            setStatus('Error: ' + error.message);
            sendToNative('ERROR', error.message);
        }
    };

    return (
        <View style={styles.container}>
            <ActivityIndicator size="large" color="#FFD700" />
            <Text style={styles.text}>{status}</Text>
            {progress > 0 && <Text style={styles.subtext}>{progress}%</Text>}
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#000000',
        justifyContent: 'center',
        alignItems: 'center',
        padding: 20,
    },
    text: {
        color: '#FFD700',
        fontSize: 18,
        fontWeight: 'bold',
        marginTop: 20,
        textAlign: 'center',
    },
    subtext: {
        color: '#666',
        fontSize: 14,
        marginTop: 10,
    }
});
