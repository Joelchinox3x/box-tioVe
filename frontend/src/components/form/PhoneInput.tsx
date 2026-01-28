import React, { useState } from 'react';
import { View, Text, StyleSheet, TextInput, Platform, TouchableOpacity, Modal, ScrollView } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS, SHADOWS } from '../../constants/theme';

interface Country {
    code: string;
    name: string;
    dialCode: string;
    flag: string;
    maxLength: number;
    pattern?: RegExp;
}

const COUNTRIES: Country[] = [
    { code: 'PE', name: 'Perú', dialCode: '+51', flag: '🇵🇪', maxLength: 9, pattern: /^9\d{8}$/ },
    { code: 'AR', name: 'Argentina', dialCode: '+54', flag: '🇦🇷', maxLength: 10 },
    { code: 'CL', name: 'Chile', dialCode: '+56', flag: '🇨🇱', maxLength: 9 },
    { code: 'CO', name: 'Colombia', dialCode: '+57', flag: '🇨🇴', maxLength: 10 },
    { code: 'MX', name: 'México', dialCode: '+52', flag: '🇲🇽', maxLength: 10 },
    { code: 'EC', name: 'Ecuador', dialCode: '+593', flag: '🇪🇨', maxLength: 9 },
    { code: 'BO', name: 'Bolivia', dialCode: '+591', flag: '🇧🇴', maxLength: 8 },
    { code: 'VE', name: 'Venezuela', dialCode: '+58', flag: '🇻🇪', maxLength: 10 },
    { code: 'US', name: 'Estados Unidos', dialCode: '+1', flag: '🇺🇸', maxLength: 10 },
    { code: 'ES', name: 'España', dialCode: '+34', flag: '🇪🇸', maxLength: 9 },
];

interface PhoneInputProps {
    label: string;
    value: string;
    onChangeText: (text: string) => void;
    countryCode: string;
    onCountryChange: (countryCode: string) => void;
    placeholder?: string;
    focused?: boolean;
    onFocus?: () => void;
    onBlur?: () => void;
    error?: string;
    isValid?: boolean;
    successMessage?: string;
}

export const PhoneInput: React.FC<PhoneInputProps> = ({
    label,
    value,
    onChangeText,
    countryCode,
    onCountryChange,
    placeholder,
    focused = false,
    onFocus,
    onBlur,
    error,
    isValid,
    successMessage,
}) => {
    const [modalVisible, setModalVisible] = useState(false);

    const selectedCountry = COUNTRIES.find(c => c.code === countryCode) || COUNTRIES[0];

    const handleCountrySelect = (code: string) => {
        onCountryChange(code);
        setModalVisible(false);
    };

    const formatPhoneNumber = (text: string) => {
        // Solo permitir números
        const cleaned = text.replace(/\D/g, '');

        // Limitar al máximo de dígitos según el país
        const maxLength = selectedCountry.maxLength;
        return cleaned.slice(0, maxLength);
    };

    const handleTextChange = (text: string) => {
        const formatted = formatPhoneNumber(text);
        onChangeText(formatted);
    };

    return (
        <View style={styles.container}>
            <Text style={styles.label}>{label}</Text>
            <View style={[
                styles.wrapper,
                focused && styles.wrapperFocused,
                !!value && styles.wrapperFilled,
                !!error && styles.wrapperError,
            ]}>
                {/* Selector de país */}
                <TouchableOpacity
                    style={styles.countrySelector}
                    onPress={() => setModalVisible(true)}
                    activeOpacity={0.7}
                >
                    <Text style={styles.flag}>{selectedCountry.flag}</Text>
                    <Text style={styles.dialCode}>{selectedCountry.dialCode}</Text>
                    <Text style={styles.dropdownIcon}>▼</Text>
                </TouchableOpacity>

                {/* Separador */}
                <View style={styles.separator} />

                {/* Input de teléfono */}
                <TextInput
                    style={[styles.input, isValid && { paddingRight: 45 }]}
                    value={value}
                    onChangeText={handleTextChange}
                    placeholder={placeholder || `${selectedCountry.maxLength} dígitos`}
                    placeholderTextColor={COLORS.text.tertiary}
                    keyboardType="phone-pad"
                    onFocus={onFocus}
                    onBlur={onBlur}
                    maxLength={selectedCountry.maxLength}
                />
                {isValid && (
                    <View style={styles.validCheck}>
                        <Ionicons name="checkmark-circle" size={22} color="#2563EB" />
                    </View>
                )}
            </View>

            {error ? (
                <View style={styles.errorContainer}>
                    <Text style={styles.errorIcon}>⚠️</Text>
                    <Text style={styles.errorText}>{error}</Text>
                </View>
            ) : successMessage && isValid ? (
                <View style={styles.successContainer}>
                    <Ionicons name="checkmark-circle" size={14} color="#2563EB" style={styles.successIcon} />
                    <Text style={styles.successText}>{successMessage}</Text>
                </View>
            ) : null}

            {/* Modal de selección de país */}
            <Modal
                visible={modalVisible}
                transparent={true}
                animationType="slide"
                onRequestClose={() => setModalVisible(false)}
            >
                <TouchableOpacity
                    style={styles.modalOverlay}
                    activeOpacity={1}
                    onPress={() => setModalVisible(false)}
                >
                    <View style={styles.modalContent}>
                        <View style={styles.modalHeader}>
                            <Text style={styles.modalTitle}>Selecciona tu país</Text>
                            <TouchableOpacity onPress={() => setModalVisible(false)}>
                                <Text style={styles.closeButton}>✕</Text>
                            </TouchableOpacity>
                        </View>

                        <ScrollView style={styles.countryList}>
                            {COUNTRIES.map((country) => (
                                <TouchableOpacity
                                    key={country.code}
                                    style={[
                                        styles.countryItem,
                                        country.code === countryCode && styles.countryItemSelected
                                    ]}
                                    onPress={() => handleCountrySelect(country.code)}
                                >
                                    <Text style={styles.countryFlag}>{country.flag}</Text>
                                    <View style={styles.countryInfo}>
                                        <Text style={styles.countryName}>{country.name}</Text>
                                        <Text style={styles.countryDialCode}>{country.dialCode}</Text>
                                    </View>
                                    {country.code === countryCode && (
                                        <Text style={styles.checkmark}>✓</Text>
                                    )}
                                </TouchableOpacity>
                            ))}
                        </ScrollView>
                    </View>
                </TouchableOpacity>
            </Modal>
        </View>
    );
};

const styles = StyleSheet.create({
    container: {
        marginBottom: SPACING.lg,
    },
    label: {
        fontSize: TYPOGRAPHY.fontSize.sm,
        fontWeight: TYPOGRAPHY.fontWeight.semibold,
        color: COLORS.text.secondary,
        marginBottom: SPACING.sm,
        textTransform: 'uppercase',
        letterSpacing: 0.5,
    },
    wrapper: {
        backgroundColor: COLORS.surface,
        borderRadius: BORDER_RADIUS.md,
        borderWidth: 2,
        borderColor: COLORS.border.primary,
        flexDirection: 'row',
        alignItems: 'center',
        overflow: 'hidden',
    },
    wrapperFocused: {
        borderColor: COLORS.primary,
        ...Platform.select({
            ios: SHADOWS.lg,
            android: { ...SHADOWS.lg, elevation: 6 },
        }),
    },
    wrapperFilled: {
        borderColor: COLORS.border.light,
    },
    wrapperError: {
        borderColor: COLORS.error,
    },
    countrySelector: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingHorizontal: SPACING.md,
        paddingVertical: SPACING.md,
        gap: SPACING.xs,
    },
    flag: {
        fontSize: 24,
        color: COLORS.text.primary,
    },
    dialCode: {
        fontSize: TYPOGRAPHY.fontSize.md,
        color: '#FFFFFF',
        fontWeight: TYPOGRAPHY.fontWeight.semibold,
    },
    dropdownIcon: {
        fontSize: 10,
        color: '#FFFFFF',
        marginLeft: SPACING.xs,
    },
    separator: {
        width: 1,
        height: '60%',
        backgroundColor: COLORS.border.primary,
    },
    input: {
        flex: 1,
        paddingVertical: SPACING.md,
        paddingHorizontal: SPACING.md,
        fontSize: TYPOGRAPHY.fontSize.lg,
        color: COLORS.text.primary,
        fontWeight: TYPOGRAPHY.fontWeight.medium,
    },
    validCheck: {
        marginRight: SPACING.md,
        alignItems: 'center',
        justifyContent: 'center',
    },
    errorContainer: {
        flexDirection: 'row',
        alignItems: 'center',
        marginTop: SPACING.xs,
        paddingHorizontal: SPACING.xs,
    },
    errorIcon: {
        fontSize: 14,
        marginRight: SPACING.xs,
    },
    errorText: {
        fontSize: TYPOGRAPHY.fontSize.sm,
        color: COLORS.error,
        flex: 1,
    },

    // Modal styles
    modalOverlay: {
        flex: 1,
        backgroundColor: 'rgba(0, 0, 0, 0.7)',
        justifyContent: 'flex-end',
    },
    modalContent: {
        backgroundColor: COLORS.surface,
        borderTopLeftRadius: BORDER_RADIUS.xl,
        borderTopRightRadius: BORDER_RADIUS.xl,
        maxHeight: '70%',
        ...Platform.select({
            ios: SHADOWS.xl,
            android: { ...SHADOWS.xl, elevation: 10 },
        }),
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
        fontSize: TYPOGRAPHY.fontSize.xl,
        fontWeight: TYPOGRAPHY.fontWeight.bold,
        color: COLORS.text.primary,
    },
    closeButton: {
        fontSize: 24,
        color: COLORS.text.secondary,
        padding: SPACING.xs,
    },
    countryList: {
        padding: SPACING.md,
    },
    countryItem: {
        flexDirection: 'row',
        alignItems: 'center',
        padding: SPACING.md,
        borderRadius: BORDER_RADIUS.md,
        marginBottom: SPACING.xs,
    },
    countryItemSelected: {
        backgroundColor: COLORS.primary + '20',
    },
    countryFlag: {
        fontSize: 28,
        marginRight: SPACING.md,
    },
    countryInfo: {
        flex: 1,
    },
    countryName: {
        fontSize: TYPOGRAPHY.fontSize.md,
        fontWeight: TYPOGRAPHY.fontWeight.medium,
        color: COLORS.text.primary,
    },
    countryDialCode: {
        fontSize: TYPOGRAPHY.fontSize.sm,
        color: COLORS.text.secondary,
        marginTop: 2,
    },
    checkmark: {
        fontSize: 20,
        color: COLORS.primary,
        fontWeight: TYPOGRAPHY.fontWeight.bold,
    },
    successContainer: {
        flexDirection: 'row',
        alignItems: 'center',
        marginTop: SPACING.xs,
        paddingHorizontal: SPACING.xs,
    },
    successIcon: {
        marginRight: SPACING.xs,
    },
    successText: {
        fontSize: TYPOGRAPHY.fontSize.sm,
        color: '#2563EB',
        flex: 1,
        fontWeight: '500',
    },
});
