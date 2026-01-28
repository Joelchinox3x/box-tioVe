import React, { useState } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Modal, FlatList, SafeAreaView, Platform } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS, SHADOWS } from '../../constants/theme';

interface Option {
  label: string;
  value: string | number;
}

interface FormSelectProps {
  label: string;
  value: string | number;
  onValueChange: (value: string | number) => void;
  options: Option[];
  placeholder?: string;
  error?: string;
  icon?: string;
}

export const FormSelect: React.FC<FormSelectProps> = ({
  label,
  value,
  onValueChange,
  options,
  placeholder = 'Seleccione una opción',
  error,
  icon,
}) => {
  const [modalVisible, setModalVisible] = useState(false);
  const safeOptions = options || [];

  const selectedOption = safeOptions.find(opt => opt.value === value);

  const handleSelect = (val: string | number) => {
    onValueChange(val);
    setModalVisible(false);
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
          {icon ? (
            <Ionicons name={icon as any} size={20} color={value ? COLORS.primary : COLORS.text.tertiary} style={{ marginRight: 8 }} />
          ) : (
            <Ionicons name="list" size={20} color={value ? COLORS.primary : COLORS.text.tertiary} />
          )}

          <Text style={[styles.triggerText, !value && styles.placeholderText]}>
            {selectedOption ? selectedOption.label.toUpperCase() : placeholder}
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
              <Text style={styles.modalTitle}>{label.toUpperCase()}</Text>
              <TouchableOpacity onPress={() => setModalVisible(false)} style={styles.closeButton}>
                <Ionicons name="close" size={28} color={COLORS.text.primary} />
              </TouchableOpacity>
            </View>

            <FlatList
              data={safeOptions}
              keyExtractor={(item) => item.value.toString()}
              contentContainerStyle={styles.listContent}
              renderItem={({ item }) => (
                <TouchableOpacity
                  style={[
                    styles.optionItem,
                    value === item.value && styles.optionItemActive
                  ]}
                  onPress={() => handleSelect(item.value)}
                >
                  <View style={styles.optionInfo}>
                    <Text style={[
                      styles.optionLabel,
                      value === item.value && styles.optionLabelActive
                    ]}>
                      {item.label}
                    </Text>
                  </View>
                  {value === item.value && (
                    <Ionicons name="checkmark-circle" size={24} color={COLORS.primary} />
                  )}
                </TouchableOpacity>
              )}
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
    fontSize: 12, // Match ClubSelector
    fontWeight: '800', // Match ClubSelector
    color: COLORS.text.secondary,
    marginBottom: SPACING.sm,
    textTransform: 'uppercase',
    letterSpacing: 1, // Match ClubSelector
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
    height: 56, // Match ClubSelector
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
  icon: {
    fontSize: 20,
    marginRight: 4, // Adjust spacing for emoji icons
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
    backgroundColor: 'rgba(0, 0, 0, 0.8)', // Fondo más oscuro para enfoque
    justifyContent: 'center', // CENTRADO (No abajo)
    padding: SPACING.lg,
  },
  modalContent: {
    backgroundColor: COLORS.surface,
    borderRadius: BORDER_RADIUS.lg, // Bordes redondeados completos
    maxHeight: '80%',
    width: '100%',
    maxWidth: 500, // Ancho máximo elegante
    alignSelf: 'center',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.1)',
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
    backgroundColor: 'rgba(255,255,255,0.02)', // Sutil diferencia cabecera
    borderTopLeftRadius: BORDER_RADIUS.lg,
    borderTopRightRadius: BORDER_RADIUS.lg,
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
  listContent: {
    paddingHorizontal: 0, // Listado limpio borde a borde
    paddingBottom: SPACING.lg,
  },
  optionItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: SPACING.lg,
    paddingHorizontal: SPACING.xl,
    borderBottomWidth: 1,
    borderBottomColor: 'rgba(255,255,255,0.05)', // Separadores sutiles
    backgroundColor: 'transparent', // Sin burbujas
  },
  optionItemActive: {
    backgroundColor: 'rgba(255, 215, 0, 0.05)', // Highlight sutil
  },
  optionInfo: {
    flex: 1,
  },
  optionLabel: {
    fontSize: 16,
    fontWeight: '500', // Texto más limpio
    color: COLORS.text.primary,
  },
  optionLabelActive: {
    color: COLORS.primary,
    fontWeight: '700',
  },
});
