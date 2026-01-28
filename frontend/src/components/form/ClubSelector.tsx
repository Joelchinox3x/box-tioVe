import React, { useState } from 'react';
import {
    View,
    Text,
    StyleSheet,
    TouchableOpacity,
    Modal,
    FlatList,
    TextInput,
    SafeAreaView,
    Animated,
    Platform,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS, SHADOWS } from '../../constants/theme';

interface Club {
    id: number | string;
    nombre: string;
    direccion?: string | null;
}

interface ClubSelectorProps {
    label: string;
    value: string | number;
    onValueChange: (value: string | number) => void;
    options: Club[];
    placeholder?: string;
    error?: string;
}

export const ClubSelector: React.FC<ClubSelectorProps> = ({
    label,
    value,
    onValueChange,
    options,
    placeholder = 'Selecciona tu club',
    error,
}) => {
    const [modalVisible, setModalVisible] = useState(false);
    const [search, setSearch] = useState('');

    const selectedClub = options.find(opt => opt.id === value);

    const filteredOptions = options
        .filter(opt => opt.nombre.toLowerCase().includes(search.toLowerCase()))
        .sort((a, b) => {
            const aIsInd = a.nombre.toUpperCase() === 'INDEPENDIENTE';
            const bIsInd = b.nombre.toUpperCase() === 'INDEPENDIENTE';
            if (aIsInd && !bIsInd) return -1;
            if (!aIsInd && bIsInd) return 1;
            return 0;
        });

    const handleSelect = (clubId: string | number) => {
        onValueChange(clubId);
        setModalVisible(false);
        setSearch('');
    };

    return (
        <View style={styles.container}>
            <Text style={styles.label}>{label}</Text>

            <TouchableOpacity
                style={[
                    styles.trigger,
                    !!value && styles.triggerActive,
                    !!error && styles.triggerError
                ]}
                onPress={() => setModalVisible(true)}
                activeOpacity={0.7}
            >
                <View style={styles.triggerContent}>
                    <Ionicons name="business" size={20} color={value ? COLORS.primary : COLORS.text.tertiary} />
                    <Text style={[styles.triggerText, !value && styles.placeholderText]}>
                        {selectedClub ? selectedClub.nombre.toUpperCase() : placeholder}
                    </Text>
                </View>
                <Ionicons name="chevron-down" size={20} color={COLORS.text.tertiary} />
            </TouchableOpacity>

            {error && (
                <View style={styles.errorContainer}>
                    <Ionicons name="alert-circle" size={14} color={COLORS.error} />
                    <Text style={styles.errorText}>{error}</Text>
                </View>
            )}

            <Modal
                visible={modalVisible}
                animationType="slide"
                transparent={true}
                onRequestClose={() => setModalVisible(false)}
            >
                <SafeAreaView style={styles.modalOverlay}>
                    <View style={styles.modalContent}>
                        <View style={styles.modalHeader}>
                            <Text style={styles.modalTitle}>BUSCA TU CLUB</Text>
                            <TouchableOpacity onPress={() => setModalVisible(false)} style={styles.closeButton}>
                                <Ionicons name="close" size={28} color={COLORS.text.primary} />
                            </TouchableOpacity>
                        </View>

                        <View style={styles.searchContainer}>
                            <Ionicons name="search" size={20} color={COLORS.text.tertiary} style={styles.searchIcon} />
                            <TextInput
                                style={styles.searchInput}
                                placeholder="Escribe el nombre del club..."
                                placeholderTextColor={COLORS.text.tertiary}
                                value={search}
                                onChangeText={setSearch}
                                autoFocus={true}
                            />
                        </View>

                        <FlatList
                            data={filteredOptions}
                            keyExtractor={(item) => item.id.toString()}
                            contentContainerStyle={styles.listContent}
                            renderItem={({ item }) => (
                                <TouchableOpacity
                                    style={[
                                        styles.clubItem,
                                        item.nombre.toUpperCase() === 'INDEPENDIENTE' && styles.clubItemIndependiente,
                                        value === item.id && styles.clubItemActive
                                    ]}
                                    onPress={() => handleSelect(item.id)}
                                >
                                    <View style={[
                                        styles.clubIconContainer,
                                        item.nombre.toUpperCase() === 'INDEPENDIENTE' && styles.clubIconContainerIndependiente
                                    ]}>
                                        {item.nombre.toUpperCase() === 'INDEPENDIENTE' ? (
                                            <Ionicons name="star" size={20} color={COLORS.primary} />
                                        ) : (
                                            <Text style={styles.clubInitials}>
                                                {item.nombre.substring(0, 2).toUpperCase()}
                                            </Text>
                                        )}
                                    </View>
                                    <View style={styles.clubInfo}>
                                        <Text style={[
                                            styles.clubName,
                                            item.nombre.toUpperCase() === 'INDEPENDIENTE' && styles.clubNameIndependiente,
                                            value === item.id && styles.clubNameActive
                                        ]}>
                                            {item.nombre.toUpperCase()}
                                        </Text>
                                        {item.direccion ? (
                                            <Text style={styles.clubAddress}>{item.direccion}</Text>
                                        ) : item.nombre.toUpperCase() === 'INDEPENDIENTE' ? (
                                            <Text style={styles.clubAddressIndependiente}>Peleador sin club oficial</Text>
                                        ) : null}
                                    </View>
                                    {value === item.id ? (
                                        <Ionicons name="checkmark-circle" size={24} color={COLORS.primary} />
                                    ) : item.nombre.toUpperCase() === 'INDEPENDIENTE' ? (
                                        <Ionicons name="shield-checkmark-outline" size={20} color="rgba(255, 215, 0, 0.4)" />
                                    ) : null}
                                </TouchableOpacity>
                            )}
                            ListEmptyComponent={
                                <View style={styles.emptyContainer}>
                                    <Text style={styles.emptyText}>No se encontraron clubs</Text>
                                </View>
                            }
                        />
                    </View>
                </SafeAreaView>
            </Modal>
        </View>
    );
};

const styles = StyleSheet.create({
    container: {
        marginBottom: SPACING.lg,
    },
    label: {
        fontSize: 12,
        fontWeight: '800',
        color: COLORS.text.secondary,
        marginBottom: SPACING.sm,
        textTransform: 'uppercase',
        letterSpacing: 1,
    },
    trigger: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        backgroundColor: 'rgba(255,255,255,0.05)',
        borderWidth: 2,
        borderColor: 'rgba(255,255,255,0.1)',
        borderRadius: 12,
        paddingHorizontal: SPACING.md,
        height: 56,
    },
    triggerActive: {
        borderColor: COLORS.primary,
        backgroundColor: 'rgba(255, 215, 0, 0.05)',
    },
    triggerError: {
        borderColor: COLORS.error,
    },
    triggerContent: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: SPACING.md,
    },
    triggerText: {
        fontSize: 16,
        color: COLORS.text.primary,
        fontWeight: '700',
    },
    placeholderText: {
        color: COLORS.text.tertiary,
        fontWeight: '500',
    },
    errorContainer: {
        flexDirection: 'row',
        alignItems: 'center',
        gap: 4,
        marginTop: 4,
    },
    errorText: {
        fontSize: 12,
        color: COLORS.error,
    },
    modalOverlay: {
        flex: 1,
        backgroundColor: 'rgba(0, 0, 0, 0.85)',
        justifyContent: 'center', // CENTRADO
        padding: SPACING.lg,
    },
    modalContent: {
        backgroundColor: COLORS.background,
        borderRadius: BORDER_RADIUS.lg, // Bordes completos
        maxHeight: '85%',
        width: '100%',
        maxWidth: 550,
        alignSelf: 'center',
        borderWidth: 1,
        borderColor: 'rgba(255,255,255,0.1)',
        overflow: 'hidden',
        ...Platform.select({
            ios: SHADOWS.xl,
            android: { elevation: 20 },
        }),
    },
    modalHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        padding: SPACING.lg,
        borderBottomWidth: 1,
        borderBottomColor: 'rgba(255,255,255,0.1)',
        backgroundColor: 'rgba(255,255,255,0.02)',
    },
    modalTitle: {
        fontSize: 16,
        fontWeight: '800',
        color: COLORS.primary,
        letterSpacing: 1,
    },
    closeButton: {
        padding: 4,
    },
    searchContainer: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: 'rgba(0,0,0,0.3)',
        margin: SPACING.md,
        borderRadius: BORDER_RADIUS.md,
        paddingHorizontal: SPACING.md,
        borderWidth: 1,
        borderColor: 'rgba(255,255,255,0.1)',
    },
    searchIcon: {
        marginRight: SPACING.sm,
    },
    searchInput: {
        flex: 1,
        height: 48,
        color: COLORS.text.primary,
        fontSize: 16,
    },
    listContent: {
        paddingBottom: SPACING.xl,
    },
    clubItem: {
        flexDirection: 'row',
        alignItems: 'center',
        paddingVertical: SPACING.md,
        paddingHorizontal: SPACING.lg,
        borderBottomWidth: 1,
        borderBottomColor: 'rgba(255,255,255,0.05)',
    },
    clubItemActive: {
        backgroundColor: 'rgba(255, 215, 0, 0.05)',
    },
    clubIconContainer: {
        width: 40,
        height: 40,
        borderRadius: 20,
        backgroundColor: 'rgba(255, 255, 255, 0.05)',
        justifyContent: 'center',
        alignItems: 'center',
        marginRight: SPACING.md,
    },
    clubInitials: {
        color: COLORS.text.secondary,
        fontWeight: '700',
        fontSize: 14,
    },
    clubInfo: {
        flex: 1,
    },
    clubName: {
        fontSize: 16,
        fontWeight: '800',
        color: COLORS.text.primary,
        marginBottom: 2,
    },
    clubNameActive: {
        color: COLORS.primary,
    },
    clubAddress: {
        fontSize: 12,
        color: COLORS.text.tertiary,
    },
    emptyContainer: {
        alignItems: 'center',
        paddingVertical: 40,
    },
    emptyText: {
        color: COLORS.text.tertiary,
        fontSize: 14,
    },
    clubItemIndependiente: {
        borderColor: 'rgba(255, 215, 0, 0.3)',
        backgroundColor: 'rgba(255, 215, 0, 0.08)',
        borderWidth: 1.5,
    },
    clubIconContainerIndependiente: {
        backgroundColor: 'rgba(255, 215, 0, 0.2)',
        borderWidth: 1,
        borderColor: COLORS.primary,
    },
    clubNameIndependiente: {
        color: COLORS.primary,
        letterSpacing: 0.5,
    },
    clubAddressIndependiente: {
        fontSize: 11,
        color: COLORS.primary,
        opacity: 0.7,
        fontStyle: 'italic',
    },
});
