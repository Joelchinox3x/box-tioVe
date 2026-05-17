import React, { useState, useEffect, useRef } from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  ActivityIndicator,
  SafeAreaView,
  StatusBar,
  Animated,
  Dimensions,
  Platform,
} from 'react-native';
import { CameraView, useCameraPermissions } from 'expo-camera';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS } from '../constants/theme';
import api from '../services/api';

const { width: SCREEN_WIDTH } = Dimensions.get('window');
const SCAN_AREA_SIZE = SCREEN_WIDTH * 0.7;

interface VerificationData {
  peleador: string;
  evento: string;
  monto: string;
  estado: string;
  fecha_generacion: string;
  checkin: boolean;
  fecha_checkin: string | null;
}

interface VerificationResult {
  valid: boolean;
  data?: VerificationData;
  message?: string;
}

interface CheckinResult {
  success: boolean;
  duplicate: boolean;
  message: string;
  data?: VerificationData;
}

type ScanState = 'scanning' | 'loading' | 'result';

export default function StaffQRScannerScreen() {
  const navigation = useNavigation();
  const [permission, requestPermission] = useCameraPermissions();
  const [scanState, setScanState] = useState<ScanState>('scanning');
  const [result, setResult] = useState<VerificationResult | null>(null);
  const [scannedToken, setScannedToken] = useState<string | null>(null);
  const [permissionError, setPermissionError] = useState<string | null>(null);
  const [checkinLoading, setCheckinLoading] = useState(false);
  const [checkinResult, setCheckinResult] = useState<CheckinResult | null>(null);

  // Animación del borde de escaneo
  const pulseAnim = useRef(new Animated.Value(1)).current;

  useEffect(() => {
    const pulse = Animated.loop(
      Animated.sequence([
        Animated.timing(pulseAnim, { toValue: 1.03, duration: 1000, useNativeDriver: true }),
        Animated.timing(pulseAnim, { toValue: 1, duration: 1000, useNativeDriver: true }),
      ])
    );
    pulse.start();
    return () => pulse.stop();
  }, []);

  const extractTokenFromQR = (data: string): string | null => {
    // El QR contiene: https://boxtiove.com/api/verificar-pago/{token}
    const match = data.match(/verificar-pago\/([a-f0-9]+)/);
    return match ? match[1] : null;
  };

  const handleBarcodeScanned = async ({ data }: { data: string }) => {
    if (scanState !== 'scanning') return;

    const token = extractTokenFromQR(data);
    if (!token) {
      setResult({ valid: false, message: 'QR no válido. No es un comprobante de BoxTiove.' });
      setScanState('result');
      return;
    }

    setScannedToken(token);
    setScanState('loading');

    try {
      const response = await api.get(`/verificar-pago/${token}`, {
        params: { format: 'json' },
      });

      if (response.data.success && response.data.valid) {
        setResult({ valid: true, data: response.data.data });
      } else {
        setResult({ valid: false, message: response.data.message || 'Comprobante no encontrado' });
      }
    } catch (error: any) {
      setResult({ valid: false, message: 'Error al verificar. Intenta de nuevo.' });
    }

    setScanState('result');
  };

  const handleCheckin = async () => {
    if (!scannedToken || checkinLoading) return;

    setCheckinLoading(true);
    try {
      const response = await api.post(`/checkin/${scannedToken}`);
      setCheckinResult(response.data);
      // Actualizar datos del result con la info actualizada
      if (response.data.data) {
        setResult(prev => prev ? { ...prev, data: response.data.data } : prev);
      }
    } catch (error: any) {
      setCheckinResult({
        success: false,
        duplicate: false,
        message: 'Error al registrar entrada. Intenta de nuevo.',
      });
    }
    setCheckinLoading(false);
  };

  const resetScanner = () => {
    setResult(null);
    setScannedToken(null);
    setCheckinResult(null);
    setCheckinLoading(false);
    setScanState('scanning');
  };

  // Solicitar permiso de cámara
  const handleRequestPermission = async () => {
    console.log('[QR Scanner] Solicitando permiso. Platform:', Platform.OS);
    setPermissionError(null);

    try {
      if (Platform.OS === 'web') {
        // En web, la cámara requiere HTTPS. En desarrollo HTTP no funciona.
        if (typeof window !== 'undefined' && window.location.protocol === 'http:' && window.location.hostname !== 'localhost') {
          setPermissionError('La cámara requiere HTTPS. En versión web, usa la verificación por página QR (escanea con la cámara del celular).');
          return;
        }
      }
      const result = await requestPermission();
      console.log('[QR Scanner] requestPermission result:', JSON.stringify(result));
      if (!result.granted) {
        setPermissionError('Permiso de cámara denegado. Actívalo en la configuración del navegador/dispositivo.');
      }
    } catch (error: any) {
      console.error('[QR Scanner] Error:', error.message);
      setPermissionError(error.message || 'No se pudo acceder a la cámara');
    }
  };

  // Determinar si ya hizo check-in (desde verificación o desde respuesta de checkin)
  const alreadyCheckedIn = checkinResult?.duplicate || result?.data?.checkin;
  const checkinTime = checkinResult?.data?.fecha_checkin || result?.data?.fecha_checkin;

  // Permisos de cámara
  if (!permission) {
    return (
      <View style={styles.container}>
        <ActivityIndicator size="large" color={COLORS.primary} />
      </View>
    );
  }

  if (!permission.granted) {
    return (
      <SafeAreaView style={styles.container}>
        <StatusBar barStyle="light-content" />
        <View style={styles.permissionContainer}>
          <Ionicons name="camera-outline" size={64} color={COLORS.primary} />
          <Text style={styles.permissionTitle}>Acceso a Cámara</Text>
          <Text style={styles.permissionText}>
            Necesitamos acceso a la cámara para escanear códigos QR de comprobantes.
          </Text>
          {permissionError && (
            <Text style={styles.permissionErrorText}>{permissionError}</Text>
          )}
          <TouchableOpacity style={styles.permissionButton} onPress={handleRequestPermission}>
            <Text style={styles.permissionButtonText}>Permitir Cámara</Text>
          </TouchableOpacity>
          <TouchableOpacity style={styles.backButton} onPress={() => navigation.goBack()}>
            <Text style={styles.backButtonText}>Volver</Text>
          </TouchableOpacity>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <StatusBar barStyle="light-content" />

      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.headerBack}>
          <Ionicons name="arrow-back" size={24} color="#fff" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Verificar QR</Text>
        <View style={{ width: 40 }} />
      </View>

      {scanState === 'scanning' || scanState === 'loading' ? (
        <View style={styles.cameraContainer}>
          <CameraView
            style={StyleSheet.absoluteFillObject}
            barcodeScannerSettings={{ barcodeTypes: ['qr'] }}
            onBarcodeScanned={scanState === 'scanning' ? handleBarcodeScanned : undefined}
          />

          {/* Overlay oscuro con hueco */}
          <View style={styles.overlay}>
            <View style={styles.overlayTop} />
            <View style={styles.overlayMiddle}>
              <View style={styles.overlaySide} />
              <Animated.View style={[styles.scanArea, { transform: [{ scale: pulseAnim }] }]}>
                {/* Esquinas del scanner */}
                <View style={[styles.corner, styles.cornerTL]} />
                <View style={[styles.corner, styles.cornerTR]} />
                <View style={[styles.corner, styles.cornerBL]} />
                <View style={[styles.corner, styles.cornerBR]} />
              </Animated.View>
              <View style={styles.overlaySide} />
            </View>
            <View style={styles.overlayBottom}>
              {scanState === 'loading' ? (
                <View style={styles.loadingBadge}>
                  <ActivityIndicator size="small" color="#fff" />
                  <Text style={styles.loadingText}>Verificando...</Text>
                </View>
              ) : (
                <Text style={styles.scanHint}>Apunta la cámara al código QR del comprobante</Text>
              )}
            </View>
          </View>
        </View>
      ) : (
        /* Resultado */
        <View style={styles.resultContainer}>
          {result?.valid ? (
            <>
              {/* Icono según estado de check-in */}
              {checkinResult ? (
                checkinResult.duplicate ? (
                  // Ya había ingresado antes
                  <>
                    <View style={styles.resultIconWarning}>
                      <Ionicons name="alert-circle" size={80} color={COLORS.warning} />
                    </View>
                    <Text style={styles.resultTitleWarning}>YA INGRESÓ</Text>
                    <Text style={styles.resultSubtitle}>
                      Entrada registrada el {checkinTime}
                    </Text>
                  </>
                ) : checkinResult.success ? (
                  // Check-in exitoso
                  <>
                    <View style={styles.resultIconValid}>
                      <Ionicons name="checkmark-circle" size={80} color={COLORS.success} />
                    </View>
                    <Text style={styles.resultTitleValid}>ENTRADA REGISTRADA</Text>
                    <Text style={styles.resultSubtitle}>
                      Check-in: {checkinResult.data?.fecha_checkin}
                    </Text>
                  </>
                ) : (
                  // Error en check-in
                  <>
                    <View style={styles.resultIconInvalid}>
                      <Ionicons name="close-circle" size={80} color={COLORS.error} />
                    </View>
                    <Text style={styles.resultTitleInvalid}>Error</Text>
                    <Text style={styles.resultSubtitle}>{checkinResult.message}</Text>
                  </>
                )
              ) : alreadyCheckedIn ? (
                // Verificación muestra que ya hizo check-in
                <>
                  <View style={styles.resultIconWarning}>
                    <Ionicons name="alert-circle" size={80} color={COLORS.warning} />
                  </View>
                  <Text style={styles.resultTitleWarning}>YA INGRESÓ</Text>
                  <Text style={styles.resultSubtitle}>
                    Entrada registrada el {checkinTime}
                  </Text>
                </>
              ) : (
                // Verificación OK, aún no hizo check-in
                <>
                  <View style={styles.resultIconValid}>
                    <Ionicons name="checkmark-circle" size={80} color={COLORS.success} />
                  </View>
                  <Text style={styles.resultTitleValid}>Pago Verificado</Text>
                  <Text style={styles.resultSubtitle}>Comprobante auténtico y válido</Text>
                </>
              )}

              {/* Monto */}
              <View style={styles.montoCard}>
                <Text style={styles.montoLabel}>MONTO PAGADO</Text>
                <Text style={styles.montoValue}>S/ {result.data?.monto}</Text>
              </View>

              {/* Info Cards */}
              <View style={styles.infoCard}>
                <View style={styles.infoRow}>
                  <Ionicons name="person" size={18} color={COLORS.primary} />
                  <Text style={styles.infoLabel}>Peleador</Text>
                  <Text style={styles.infoValue}>{result.data?.peleador}</Text>
                </View>
                <View style={styles.divider} />
                <View style={styles.infoRow}>
                  <Ionicons name="calendar" size={18} color={COLORS.primary} />
                  <Text style={styles.infoLabel}>Evento</Text>
                  <Text style={styles.infoValue}>{result.data?.evento}</Text>
                </View>
                <View style={styles.divider} />
                <View style={styles.infoRow}>
                  <Ionicons name="shield-checkmark" size={18} color={COLORS.success} />
                  <Text style={styles.infoLabel}>Estado</Text>
                  <View style={[styles.badge, result.data?.estado === 'PAGADO' ? styles.badgePagado : (result.data?.estado === 'PENDIENTE' ? styles.badgePendiente : styles.badgeInscrito)]}>
                    <Text style={[styles.badgeText, result.data?.estado === 'PAGADO' ? styles.badgeTextPagado : (result.data?.estado === 'PENDIENTE' ? styles.badgeTextPendiente : styles.badgeTextInscrito)]}>
                      {result.data?.estado}
                    </Text>
                  </View>
                </View>
                {result.data?.checkin && (
                  <>
                    <View style={styles.divider} />
                    <View style={styles.infoRow}>
                      <Ionicons name="log-in" size={18} color={COLORS.warning} />
                      <Text style={styles.infoLabel}>Entrada</Text>
                      <Text style={[styles.infoValue, { color: COLORS.warning }]}>{result.data.fecha_checkin}</Text>
                    </View>
                  </>
                )}
              </View>

              {/* Botón de Check-in (solo si no hizo check-in aún y no hay resultado de checkin) */}
              {!alreadyCheckedIn && !checkinResult && (
                <TouchableOpacity
                  style={styles.checkinButton}
                  onPress={handleCheckin}
                  disabled={checkinLoading}
                >
                  {checkinLoading ? (
                    <ActivityIndicator size="small" color="#000" />
                  ) : (
                    <>
                      <Ionicons name="log-in" size={22} color="#000" />
                      <Text style={styles.checkinButtonText}>REGISTRAR ENTRADA</Text>
                    </>
                  )}
                </TouchableOpacity>
              )}
            </>
          ) : (
            <>
              {/* Pago No Válido */}
              <View style={styles.resultIconInvalid}>
                <Ionicons name="close-circle" size={80} color={COLORS.error} />
              </View>
              <Text style={styles.resultTitleInvalid}>No Válido</Text>
              <Text style={styles.resultSubtitle}>{result?.message || 'Comprobante no encontrado'}</Text>
            </>
          )}

          {/* Botón escanear otro */}
          <TouchableOpacity style={styles.scanAgainButton} onPress={resetScanner}>
            <Ionicons name="scan" size={22} color="#000" />
            <Text style={styles.scanAgainText}>Escanear Otro QR</Text>
          </TouchableOpacity>
        </View>
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: COLORS.background,
  },

  // Header
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: SPACING.md,
    paddingVertical: SPACING.sm,
    backgroundColor: 'rgba(0,0,0,0.8)',
    zIndex: 10,
  },
  headerBack: {
    width: 40,
    height: 40,
    alignItems: 'center',
    justifyContent: 'center',
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: COLORS.primary,
    letterSpacing: 1,
  },

  // Camera
  cameraContainer: {
    flex: 1,
  },

  // Overlay
  overlay: {
    ...StyleSheet.absoluteFillObject,
  },
  overlayTop: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.6)',
  },
  overlayMiddle: {
    flexDirection: 'row',
    height: SCAN_AREA_SIZE,
  },
  overlaySide: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.6)',
  },
  overlayBottom: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.6)',
    alignItems: 'center',
    paddingTop: 30,
  },
  scanArea: {
    width: SCAN_AREA_SIZE,
    height: SCAN_AREA_SIZE,
  },
  scanHint: {
    color: '#ccc',
    fontSize: 14,
    textAlign: 'center',
    paddingHorizontal: 40,
  },

  // Corners del scanner
  corner: {
    position: 'absolute',
    width: 30,
    height: 30,
    borderColor: COLORS.primary,
  },
  cornerTL: {
    top: 0, left: 0,
    borderTopWidth: 3, borderLeftWidth: 3,
    borderTopLeftRadius: 8,
  },
  cornerTR: {
    top: 0, right: 0,
    borderTopWidth: 3, borderRightWidth: 3,
    borderTopRightRadius: 8,
  },
  cornerBL: {
    bottom: 0, left: 0,
    borderBottomWidth: 3, borderLeftWidth: 3,
    borderBottomLeftRadius: 8,
  },
  cornerBR: {
    bottom: 0, right: 0,
    borderBottomWidth: 3, borderRightWidth: 3,
    borderBottomRightRadius: 8,
  },

  // Loading
  loadingBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(255,215,0,0.15)',
    paddingHorizontal: 20,
    paddingVertical: 10,
    borderRadius: 20,
    gap: 10,
  },
  loadingText: {
    color: COLORS.primary,
    fontSize: 15,
    fontWeight: '600',
  },

  // Permisos
  permissionContainer: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: SPACING.xl,
  },
  permissionTitle: {
    fontSize: 22,
    fontWeight: '700',
    color: '#fff',
    marginTop: SPACING.md,
    marginBottom: SPACING.sm,
  },
  permissionText: {
    fontSize: 14,
    color: '#999',
    textAlign: 'center',
    marginBottom: SPACING.lg,
    lineHeight: 22,
  },
  permissionErrorText: {
    fontSize: 13,
    color: COLORS.error,
    textAlign: 'center',
    marginBottom: SPACING.md,
    backgroundColor: 'rgba(239,68,68,0.1)',
    padding: SPACING.sm,
    borderRadius: BORDER_RADIUS.sm,
    overflow: 'hidden',
  },
  permissionButton: {
    backgroundColor: COLORS.primary,
    paddingHorizontal: 30,
    paddingVertical: 14,
    borderRadius: BORDER_RADIUS.md,
    marginBottom: SPACING.md,
  },
  permissionButtonText: {
    color: '#000',
    fontSize: 16,
    fontWeight: '700',
  },
  backButton: {
    paddingVertical: 10,
  },
  backButtonText: {
    color: '#888',
    fontSize: 14,
  },

  // Resultado
  resultContainer: {
    flex: 1,
    alignItems: 'center',
    paddingHorizontal: SPACING.lg,
    paddingTop: SPACING.xl,
  },
  resultIconValid: {
    marginBottom: SPACING.sm,
  },
  resultIconInvalid: {
    marginBottom: SPACING.sm,
    marginTop: SPACING.xl,
  },
  resultIconWarning: {
    marginBottom: SPACING.sm,
  },
  resultTitleValid: {
    fontSize: 24,
    fontWeight: '800',
    color: COLORS.success,
    marginBottom: 4,
  },
  resultTitleInvalid: {
    fontSize: 24,
    fontWeight: '800',
    color: COLORS.error,
    marginBottom: 4,
  },
  resultTitleWarning: {
    fontSize: 24,
    fontWeight: '800',
    color: COLORS.warning,
    marginBottom: 4,
  },
  resultSubtitle: {
    fontSize: 14,
    color: '#888',
    marginBottom: SPACING.lg,
    textAlign: 'center',
  },

  // Monto
  montoCard: {
    backgroundColor: 'rgba(16,185,129,0.1)',
    borderWidth: 1,
    borderColor: 'rgba(16,185,129,0.2)',
    borderRadius: BORDER_RADIUS.lg,
    padding: SPACING.md,
    alignItems: 'center',
    width: '100%',
    marginBottom: SPACING.md,
  },
  montoLabel: {
    fontSize: 11,
    fontWeight: '700',
    letterSpacing: 1.5,
    color: '#888',
    marginBottom: 4,
  },
  montoValue: {
    fontSize: 36,
    fontWeight: '800',
    color: COLORS.success,
  },

  // Info Card
  infoCard: {
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.lg,
    padding: SPACING.md,
    width: '100%',
    marginBottom: SPACING.lg,
  },
  infoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 10,
    gap: 10,
  },
  infoLabel: {
    fontSize: 13,
    color: '#888',
    flex: 1,
  },
  infoValue: {
    fontSize: 14,
    fontWeight: '600',
    color: '#eee',
    textAlign: 'right',
    maxWidth: '55%',
  },
  divider: {
    height: 1,
    backgroundColor: 'rgba(255,255,255,0.05)',
  },

  // Badge
  badge: {
    paddingHorizontal: 12,
    paddingVertical: 4,
    borderRadius: 12,
  },
  badgePagado: {
    backgroundColor: 'rgba(16,185,129,0.15)',
  },
  badgePendiente: {
    backgroundColor: 'rgba(245,158,11,0.15)',
  },
  badgeInscrito: {
    backgroundColor: 'rgba(52,152,219,0.15)',
  },
  badgeText: {
    fontSize: 12,
    fontWeight: '700',
  },
  badgeTextPagado: {
    color: COLORS.success,
  },
  badgeTextPendiente: {
    color: COLORS.warning,
  },
  badgeTextInscrito: {
    color: '#3498db',
  },

  // Check-in Button
  checkinButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.success,
    paddingHorizontal: 28,
    paddingVertical: 14,
    borderRadius: BORDER_RADIUS.md,
    gap: 10,
    marginBottom: SPACING.md,
    width: '100%',
    justifyContent: 'center',
  },
  checkinButtonText: {
    color: '#000',
    fontSize: 16,
    fontWeight: '700',
    letterSpacing: 1,
  },

  // Scan Again
  scanAgainButton: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: COLORS.primary,
    paddingHorizontal: 28,
    paddingVertical: 14,
    borderRadius: BORDER_RADIUS.md,
    gap: 10,
  },
  scanAgainText: {
    color: '#000',
    fontSize: 16,
    fontWeight: '700',
  },
});
