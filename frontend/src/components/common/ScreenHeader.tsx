import React from 'react';
import {
    View,
    Text,
    StyleSheet,
    TouchableOpacity,
    Platform,
    ImageBackground,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { LinearGradient } from 'expo-linear-gradient';
import { COLORS, SPACING, TYPOGRAPHY, BORDER_RADIUS } from '../../constants/theme';

interface ScreenHeaderProps {
    title: string;
    subtitle?: string;
    slogan?: string;
    showBackButton?: boolean;
    onBackPress?: () => void;
    rightElement?: React.ReactNode;
}

export const ScreenHeader: React.FC<ScreenHeaderProps> = ({
    title,
    subtitle,
    slogan,
    showBackButton = true,
    onBackPress,
    rightElement,
}) => {
    const navigation = useNavigation();

    const handleBack = () => {
        if (onBackPress) {
            onBackPress();
        } else if (navigation.canGoBack()) {
            navigation.goBack();
        }
    };

    return (
        <View style={styles.outerContainer}>
            <ImageBackground
                source={require('../../../assets/el_jab_dorado_hero_bg.png')}
                style={styles.backgroundImage}
                resizeMode="cover"
            >
                <LinearGradient
                    colors={['rgba(0,0,0,0.4)', 'rgba(0,0,0,0.8)']}
                    style={[styles.container, Platform.OS === 'ios' && styles.iosPadding]}
                >
                    {/* Sección Izquierda: Botón de Volver o Logo */}
                    <View style={styles.leftSection}>
                        {showBackButton ? (
                            <TouchableOpacity
                                onPress={handleBack}
                                style={styles.backButton}
                                activeOpacity={0.7}
                            >
                                <Ionicons name="arrow-back" size={24} color={COLORS.primary} />
                            </TouchableOpacity>
                        ) : (
                            <View style={styles.brandContainer}>
                                <Text style={styles.brandText}>Box TioVE</Text>
                            </View>
                        )}
                    </View>

                    {/* Sección Central: Título */}
                    <View style={styles.centerSection}>
                        <Text style={styles.title} numberOfLines={1}>{title}</Text>
                        {subtitle && (
                            <Text style={styles.subtitle} numberOfLines={1}>{subtitle}</Text>
                        )}
                        {slogan && (
                            <Text style={styles.slogan} numberOfLines={1}>{slogan}</Text>
                        )}
                    </View>

                    {/* Sección Derecha: Acciones Personalizadas */}
                    <View style={styles.rightSection}>
                        {rightElement || <View style={styles.placeholder} />}
                    </View>
                </LinearGradient>
            </ImageBackground>
        </View>
    );
};

const styles = StyleSheet.create({
    outerContainer: {
        minHeight: Platform.OS === 'ios' ? 110 : 80,
        backgroundColor: '#000',
        borderBottomWidth: 1,
        borderBottomColor: 'rgba(255, 215, 0, 0.4)',
        overflow: 'hidden', // Asegura que nada se salga
    },
    backgroundImage: {
        flex: 1,
        width: '100%',
    },
    container: {
        flex: 1, // Ahora cubre el total del fondo
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        paddingHorizontal: SPACING.lg,
        paddingVertical: SPACING.sm,
    },
    iosPadding: {
        paddingTop: 30,
    },
    leftSection: {
        width: 60,
        alignItems: 'flex-start',
    },
    centerSection: {
        flex: 1,
        alignItems: 'center',
    },
    rightSection: {
        width: 60,
        alignItems: 'flex-end',
    },
    title: {
        fontSize: 18,
        fontWeight: '900',
        color: '#FFF',
        letterSpacing: 1,
        textAlign: 'center',
        textTransform: 'uppercase',
    },
    subtitle: {
        fontSize: 12,
        color: COLORS.primary,
        fontWeight: '900',
        marginTop: 2,
        letterSpacing: 0.5,
    },
    slogan: {
        fontSize: 10,
        color: COLORS.text.secondary,
        fontWeight: '600',
        marginTop: 2,
        fontStyle: 'italic',
    },
    backButton: {
        width: 40,
        height: 40,
        borderRadius: 20,
        backgroundColor: 'rgba(255, 255, 255, 0.05)',
        justifyContent: 'center',
        alignItems: 'center',
        borderWidth: 1,
        borderColor: 'rgba(255, 255, 255, 0.1)',
    },
    brandContainer: {
        paddingHorizontal: 8,
        paddingVertical: 4,
        borderRadius: 4,
        backgroundColor: 'rgba(255, 215, 0, 0.1)',
        borderWidth: 1,
        borderColor: 'rgba(255, 215, 0, 0.2)',
    },
    brandText: {
        color: COLORS.primary,
        fontSize: 10,
        fontWeight: '900',
    },
    placeholder: {
        width: 40,
    },
});
