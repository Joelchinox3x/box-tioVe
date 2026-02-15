import React, { useState, useEffect } from 'react';
import {
  View,
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  ActivityIndicator,
  Platform,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { COLORS, SPACING, BORDER_RADIUS } from '../../constants/theme';
import { SettingsService } from '../../services/SettingsService';

interface SettingOption {
  value: string;
  label: string;
  description: string;
  icon: keyof typeof Ionicons.glyphMap;
}

interface SettingConfig {
  key: string;
  title: string;
  description: string;
  icon: keyof typeof Ionicons.glyphMap;
  iconColor: string;
  options: SettingOption[];
}

// Definir los settings configurables y sus opciones
const SETTINGS_CONFIG: SettingConfig[] = [
  {
    key: 'admin_nav_mode',
    title: 'Navegación del Panel',
    description: 'Controla cómo se muestran los tabs de navegación en el panel de admin',
    icon: 'menu',
    iconColor: '#3498db',
    options: [
      {
        value: 'normal',
        label: 'Siempre visible',
        description: 'Los tabs de navegación se muestran siempre',
        icon: 'eye',
      },
      {
        value: 'hidden',
        label: 'Ocultos',
        description: 'Los tabs se ocultan, solo se navega desde el dashboard',
        icon: 'eye-off',
      },
      {
        value: 'auto_hide',
        label: 'Auto-ocultar',
        description: 'Los tabs se ocultan al entrar a una sección desde el dashboard',
        icon: 'eye-off-outline',
      },
    ],
  },
  {
    key: 'bg_remover_mode',
    title: 'Removedor de Fondo',
    description: 'Controla la visibilidad del removedor de fondo en el editor',
    icon: 'cut',
    iconColor: '#9b59b6',
    options: [
      {
        value: 'debug',
        label: 'Debug',
        description: 'Muestra el removedor de fondo con información de depuración',
        icon: 'bug',
      },
      {
        value: 'invisible',
        label: 'Invisible',
        description: 'El removedor de fondo funciona sin interfaz visible',
        icon: 'eye-off',
      },
    ],
  },
  {
    key: 'crop_tool_version',
    title: 'Editor de Recortes',
    description: 'Versión del editor de recortes de imagen',
    icon: 'crop',
    iconColor: '#e67e22',
    options: [
      {
        value: 'legacy',
        label: 'Legacy',
        description: 'ImageCropper original (básico)',
        icon: 'image',
      },
      {
        value: 'v2',
        label: 'V2 (Skia)',
        description: 'EditarImagenCard con motor Skia (avanzado)',
        icon: 'sparkles',
      },
    ],
  },
];

export default function AdminSettingsScreen() {
  const [settingsValues, setSettingsValues] = useState<Record<string, string>>({});
  const [loading, setLoading] = useState(true);
  const [savingKey, setSavingKey] = useState<string | null>(null);

  useEffect(() => {
    loadSettings();
  }, []);

  const loadSettings = async () => {
    try {
      const data = await SettingsService.getAllSettings();
      if (data.success && data.settings) {
        const map: Record<string, string> = {};
        data.settings.forEach((s: any) => {
          map[s.setting_key] = s.setting_value;
        });
        setSettingsValues(map);
      }
    } catch (error) {
      console.error('Error loading settings:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleUpdateSetting = async (key: string, value: string) => {
    setSavingKey(key);
    const result = await SettingsService.updateSetting(key, value);
    if (result.success) {
      setSettingsValues(prev => ({ ...prev, [key]: value }));
      if (Platform.OS === 'web') {
        // Silencioso en web
      }
    }
    setSavingKey(null);
  };

  if (loading) {
    return (
      <View style={styles.centerContainer}>
        <ActivityIndicator size="large" color={COLORS.primary} />
        <Text style={styles.loadingText}>Cargando configuración...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Ionicons name="settings" size={24} color={COLORS.primary} />
        <Text style={styles.title}>Configuración del Sistema</Text>
      </View>

      <ScrollView contentContainerStyle={{ paddingBottom: 100 }}>
        {SETTINGS_CONFIG.map((config) => (
          <View key={config.key} style={styles.settingCard}>
            <View style={styles.settingHeader}>
              <View style={[styles.settingIconCircle, { backgroundColor: `${config.iconColor}20` }]}>
                <Ionicons name={config.icon} size={22} color={config.iconColor} />
              </View>
              <View style={{ flex: 1 }}>
                <Text style={styles.settingTitle}>{config.title}</Text>
                <Text style={styles.settingDesc}>{config.description}</Text>
              </View>
            </View>

            <View style={styles.optionsContainer}>
              {config.options.map((option) => {
                const isActive = settingsValues[config.key] === option.value;
                const isSaving = savingKey === config.key;

                return (
                  <TouchableOpacity
                    key={option.value}
                    style={[styles.optionCard, isActive && styles.optionCardActive]}
                    onPress={() => handleUpdateSetting(config.key, option.value)}
                    disabled={isSaving}
                  >
                    <View style={styles.optionRow}>
                      <Ionicons
                        name={option.icon}
                        size={20}
                        color={isActive ? COLORS.primary : '#666'}
                      />
                      <View style={{ flex: 1, marginLeft: 10 }}>
                        <Text style={[styles.optionLabel, isActive && styles.optionLabelActive]}>
                          {option.label}
                        </Text>
                        <Text style={styles.optionDesc}>{option.description}</Text>
                      </View>
                      {isActive && (
                        <Ionicons name="checkmark-circle" size={22} color={COLORS.primary} />
                      )}
                      {isSaving && !isActive && (
                        <ActivityIndicator size="small" color={COLORS.primary} />
                      )}
                    </View>
                  </TouchableOpacity>
                );
              })}
            </View>
          </View>
        ))}
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#1a1a1a',
  },
  centerContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#1a1a1a',
  },
  loadingText: {
    color: '#fff',
    marginTop: 10,
    fontSize: 16,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 20,
    paddingBottom: 10,
    gap: 10,
  },
  title: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#fff',
  },
  settingCard: {
    backgroundColor: '#2c2c2c',
    borderRadius: BORDER_RADIUS.lg,
    padding: SPACING.lg,
    marginHorizontal: 20,
    marginBottom: 15,
    borderWidth: 1,
    borderColor: '#444',
  },
  settingHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 15,
    gap: 12,
  },
  settingIconCircle: {
    width: 44,
    height: 44,
    borderRadius: 22,
    justifyContent: 'center',
    alignItems: 'center',
  },
  settingTitle: {
    fontSize: 17,
    fontWeight: 'bold',
    color: '#fff',
  },
  settingDesc: {
    fontSize: 12,
    color: '#999',
    marginTop: 2,
  },
  optionsContainer: {
    gap: 8,
  },
  optionCard: {
    backgroundColor: '#1a1a1a',
    borderRadius: 10,
    padding: 14,
    borderWidth: 1.5,
    borderColor: '#444',
  },
  optionCardActive: {
    borderColor: COLORS.primary,
    backgroundColor: 'rgba(255, 215, 0, 0.05)',
  },
  optionRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  optionLabel: {
    fontSize: 15,
    fontWeight: '600',
    color: '#ccc',
  },
  optionLabelActive: {
    color: COLORS.primary,
  },
  optionDesc: {
    fontSize: 11,
    color: '#777',
    marginTop: 2,
  },
});
