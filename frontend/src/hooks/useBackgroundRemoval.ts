import { useState, useEffect } from 'react';
import { Platform, Linking, Alert } from 'react-native';
import { Config } from '../config/config';

export const useBackgroundRemoval = () => {
    const [isLibReady, setIsLibReady] = useState(false);
    const [isProcessing, setIsProcessing] = useState(false);

    // CDN Injection Logic (Strictly copied from useFighterForm.ts, adapted for hook)
    useEffect(() => {
        if (Platform.OS === 'web') {
            const scriptId = 'imgly-bg-removal-cdn';
            const checkReady = () => {
                if ((window as any).imglyBackgroundRemoval) { setIsLibReady(true); return true; }
                return false;
            };
            if (document.getElementById(scriptId)) { checkReady(); } else {
                const script = document.createElement('script');
                script.id = scriptId;
                script.type = 'module';
                script.innerHTML = `
          import { removeBackground } from 'https://esm.sh/@imgly/background-removal@1.7.0';
          window.imglyBackgroundRemoval = { removeBackground };
          document.dispatchEvent(new Event('imgly-ready'));
        `;
                document.body.appendChild(script);
                document.addEventListener('imgly-ready', () => { console.log("IA Library Loaded via ESM"); checkReady(); }, { once: true });
            }
        }
    }, []);

    const removeBackground = async (uri: string): Promise<string | null> => {
        if (!uri) return null;

        if (Platform.OS !== 'web') {
            // Native Strategy: Open external tool
            Alert.alert(
                "Remover Fondo (Versión Web)",
                "Para eliminar el fondo usando tu propia tecnología Web, abriremos tu aplicación en el navegador. (Nota: Selecciona la foto nuevamente en la web).",
                [
                    { text: "Cancelar", style: "cancel" },
                    {
                        text: "Abrir Mi Web",
                        // TODO: Reemplaza esta URL con la ruta exacta de tu versión web
                        onPress: () => Linking.openURL(Config.BASE_URL)
                    }
                ]
            );
            return null;
        }

        setIsProcessing(true);
        try {
            const imgly = (window as any).imglyBackgroundRemoval;
            if (!imgly) throw new Error("Librería IA no cargada.");

            const imageBlob = await imgly.removeBackground(uri, {
                debug: true,
                model: 'small', // Usamos el modelo ligero para evitar errores de memoria
                publicPath: window.location.origin + '/imgly/dist/',
                onProgress: (status: string, progress: number) => {
                    console.log(`IA [${status}]: ${Math.round(progress * 100)}%`);
                }
            });
            const url = URL.createObjectURL(imageBlob);
            return url;
        } catch (e: any) {
            console.error('Error removing background', e);
            throw e;
        } finally {
            setIsProcessing(false);
        }
    };

    const uploadToTempServer = async (uri: string): Promise<string | null> => {
        try {
            console.log("📤 Subiendo a servidor temporal...");
            const formData = new FormData();

            // Fix para Uri en Android
            const cleanUri = Platform.OS === 'android' && !uri.startsWith('file://') ? `file://${uri}` : uri;
            const filename = cleanUri.split('/').pop() || 'upload.jpg';
            const match = /\.(\w+)$/.exec(filename);
            const type = match ? `image/${match[1]}` : `image/jpeg`;

            formData.append('image', { uri: cleanUri, name: filename, type } as any);

            // POST al endpoint desde configuración centralizada
            const response = await fetch(Config.TEMP_UPLOAD_ENDPOINT, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'multipart/form-data',
                },
            });

            const text = await response.text();
            console.log("📥 Respuesta Temp:", text);

            try {
                const json = JSON.parse(text);
                if (json.success && json.url) {
                    return json.url;
                }
            } catch (e) { console.error("Error parseando PDF", e); }

            return null;
        } catch (error) {
            console.error("Error uploadToTempServer:", error);
            return null;
        }
    };

    return {
        removeBackground,
        uploadToTempServer,
        isLibReady,
        isProcessing
    };
};
