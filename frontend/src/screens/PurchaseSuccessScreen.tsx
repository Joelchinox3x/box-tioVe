import React from 'react';
import {
    View,
    Text,
    StyleSheet,
    SafeAreaView,
    StatusBar,
    TouchableOpacity,
    ScrollView,
    Image,
    Linking,
    Platform,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation, useRoute } from '@react-navigation/native';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS, SHADOWS } from '../constants/theme';
import { createShadow } from '../utils/shadows';

export default function PurchaseSuccessScreen() {
    const navigation = useNavigation();
    const route = useRoute();
    const { purchaseData } = route.params as { purchaseData: any } || {};

    // purchaseData debe contener:
    // - id (boleto_id)
    // - pdf_url
    // - total
    // - cantidad
    // - tipo_boleto
    // - comprador
    // - metodo_pago

    const handleDownload = () => {
        if (purchaseData?.pdf_url) {
            // Append timestamp to avoid caching issues, similar to ProfileScreen
            const separator = purchaseData.pdf_url.includes('?') ? '&' : '?';
            const url = `${purchaseData.pdf_url}${separator}_ts=${Date.now()}`;
            Linking.openURL(url);
        }
    };

    const handleHome = () => {
        navigation.reset({
            index: 0,
            routes: [{ name: 'Home' as never }],
        });
    };

    if (!purchaseData) {
        return (
            <SafeAreaView style={styles.container}>
                <View style={styles.content}>
                    <Text style={styles.errorText}>No hay información de la compra.</Text>
                    <TouchableOpacity style={styles.homeButton} onPress={handleHome}>
                        <Text style={styles.homeButtonText}>Volver al Inicio</Text>
                    </TouchableOpacity>
                </View>
            </SafeAreaView>
        );
    }

    return (
        <SafeAreaView style={styles.container}>
            <StatusBar barStyle="light-content" backgroundColor={COLORS.success} />

            <ScrollView contentContainerStyle={styles.scrollContent}>

                <View style={styles.successIconContainer}>
                    <View style={styles.iconCircle}>
                        <Ionicons name="checkmark" size={60} color={COLORS.success} />
                    </View>
                </View>

                <Text style={styles.title}>¡Compra Exitosa!</Text>
                <Text style={styles.subtitle}>
                    Tu pedido ha sido procesado correctamente.
                </Text>

                {/* Ticket Card Preview */}
                <View style={styles.ticketCard}>
                    <View style={styles.ticketHeader}>
                        <View style={styles.ticketHoleLeft} />
                        <View style={styles.ticketHoleRight} />
                        <Text style={styles.ticketEvent}>EVENTO BOXEO</Text>
                        <Text style={styles.ticketType}>{purchaseData.tipo_boleto || 'ENTRADA'}</Text>
                    </View>

                    <View style={styles.ticketBody}>
                        <View style={styles.row}>
                            <Text style={styles.label}>Titular:</Text>
                            <Text style={styles.value}>{purchaseData.comprador}</Text>
                        </View>
                        <View style={styles.row}>
                            <Text style={styles.label}>Cantidad:</Text>
                            <Text style={styles.value}>{purchaseData.cantidad || 1}</Text>
                        </View>
                        <View style={styles.row}>
                            <Text style={styles.label}>Total Pagado:</Text>
                            <Text style={styles.totalValue}>S/ {Number(purchaseData.total).toFixed(2)}</Text>
                        </View>
                    </View>

                    <View style={styles.ticketFooter}>
                        <Text style={styles.ticketId}>ID: #{purchaseData.id?.toString().padStart(6, '0')}</Text>
                    </View>
                </View>

                <View style={styles.actionsContainer}>
                    <Text style={styles.instructionText}>
                        Descarga tus entradas ahora. También se han guardado en este dispositivo.
                    </Text>

                    <TouchableOpacity style={styles.downloadButton} onPress={handleDownload}>
                        <Ionicons name="download-outline" size={24} color="#fff" />
                        <Text style={styles.downloadButtonText}>DESCARGAR ENTRADAS (PDF)</Text>
                    </TouchableOpacity>

                    <TouchableOpacity style={styles.homeButton} onPress={handleHome}>
                        <Text style={styles.homeButtonText}>VOLVER AL INICIO</Text>
                    </TouchableOpacity>
                </View>

            </ScrollView>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: COLORS.background,
    },
    scrollContent: {
        padding: SPACING.xl,
        alignItems: 'center',
        paddingTop: 60,
    },
    content: {
        padding: SPACING.xl,
        alignItems: 'center',
        justifyContent: 'center',
        flex: 1,
    },
    successIconContainer: {
        marginBottom: SPACING.lg,
    },
    iconCircle: {
        width: 100,
        height: 100,
        borderRadius: 50,
        backgroundColor: 'rgba(16, 185, 129, 0.2)',
        justifyContent: 'center',
        alignItems: 'center',
        borderWidth: 2,
        borderColor: COLORS.success,
        ...createShadow(COLORS.success, 0, 0, 0.4, 20, 10),
    },
    title: {
        fontSize: 28,
        fontWeight: '800',
        color: '#fff',
        marginBottom: SPACING.xs,
        textAlign: 'center',
    },
    subtitle: {
        fontSize: 16,
        color: COLORS.text.secondary,
        marginBottom: SPACING.xxl,
        textAlign: 'center',
    },
    ticketCard: {
        width: '100%',
        backgroundColor: COLORS.surface,
        borderRadius: BORDER_RADIUS.lg,
        overflow: 'hidden',
        marginBottom: SPACING.xxl,
        ...Platform.select({
            ios: {
                shadowColor: '#000',
                shadowOffset: { width: 0, height: 4 },
                shadowOpacity: 0.3,
                shadowRadius: 8,
            },
            android: {
                elevation: 8,
            },
        }),
    },
    ticketHeader: {
        backgroundColor: COLORS.primary,
        padding: SPACING.md,
        alignItems: 'center',
        position: 'relative',
        borderBottomWidth: 2,
        borderBottomColor: 'rgba(0,0,0,0.1)',
        borderStyle: 'dashed',
    },
    ticketHoleLeft: {
        position: 'absolute',
        bottom: -10,
        left: -10,
        width: 20,
        height: 20,
        borderRadius: 10,
        backgroundColor: COLORS.background,
    },
    ticketHoleRight: {
        position: 'absolute',
        bottom: -10,
        right: -10,
        width: 20,
        height: 20,
        borderRadius: 10,
        backgroundColor: COLORS.background,
    },
    ticketEvent: {
        color: '#000',
        fontWeight: '900',
        fontSize: 14,
        opacity: 0.7,
        letterSpacing: 2,
    },
    ticketType: {
        color: '#000',
        fontWeight: '900',
        fontSize: 24,
        marginTop: 4,
    },
    ticketBody: {
        padding: SPACING.lg,
        gap: SPACING.md,
    },
    row: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
    },
    label: {
        color: COLORS.text.tertiary,
        fontSize: 14,
    },
    value: {
        color: '#fff',
        fontSize: 16,
        fontWeight: '600',
    },
    totalValue: {
        color: COLORS.success,
        fontSize: 20,
        fontWeight: '800',
    },
    ticketFooter: {
        backgroundColor: 'rgba(0,0,0,0.2)',
        padding: SPACING.sm,
        alignItems: 'center',
    },
    ticketId: {
        color: COLORS.text.tertiary,
        fontSize: 12,
        fontFamily: Platform.OS === 'ios' ? 'Courier' : 'monospace',
    },
    actionsContainer: {
        width: '100%',
        alignItems: 'center',
        gap: SPACING.md,
    },
    instructionText: {
        color: COLORS.text.secondary,
        textAlign: 'center',
        marginBottom: SPACING.sm,
        fontSize: 14,
    },
    downloadButton: {
        width: '100%',
        backgroundColor: COLORS.primary,
        paddingVertical: 16,
        borderRadius: BORDER_RADIUS.md,
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        gap: 10,
        ...SHADOWS.md,
    },
    downloadButtonText: {
        color: '#000',
        fontWeight: '800',
        fontSize: 16,
    },
    homeButton: {
        paddingVertical: 16,
    },
    homeButtonText: {
        color: COLORS.text.tertiary,
        fontWeight: '600',
        fontSize: 14,
    },
    errorText: {
        color: '#fff',
        fontSize: 18,
        marginBottom: 20,
    }
});
