import React, { useState } from 'react';
import { 
  SafeAreaView, 
  ScrollView, 
  StyleSheet, 
  View, 
  Text,
  TouchableOpacity,
  Alert 
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

export default function RegisterScreen({ navigation }: any) {
  const [selectedOption, setSelectedOption] = useState<'fighter' | 'ticket' | null>(null);

  const handleFighterRegistration = () => {
    Alert.alert(
      '¡Quiero Pelear!',
      'Serás redirigido al formulario de inscripción de peleadores',
      [
        { text: 'Cancelar', style: 'cancel' },
        { 
          text: 'Continuar', 
          onPress: () => {
            // Navegar a formulario de inscripción
            // navigation.navigate('FighterForm');
            console.log('Ir a formulario de peleador');
          }
        }
      ]
    );
  };

  const handleTicketPurchase = () => {
    Alert.alert(
      'Comprar Entradas',
      'Serás redirigido a la tienda de entradas',
      [
        { text: 'Cancelar', style: 'cancel' },
        { 
          text: 'Continuar', 
          onPress: () => {
            // Navegar a tienda de entradas
            // navigation.navigate('TicketStore');
            console.log('Ir a comprar entradas');
          }
        }
      ]
    );
  };

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView 
        showsVerticalScrollIndicator={false}
        contentContainerStyle={styles.scrollContent}
      >
        {/* Header */}
        <View style={styles.header}>
          <Text style={styles.headerTitle}>¿QUÉ DESEAS HACER?</Text>
          <Text style={styles.headerSubtitle}>
            Selecciona una opción para continuar
          </Text>
        </View>

        {/* Opción 1: Registrarse para Pelear */}
        <TouchableOpacity 
          style={[
            styles.optionCard,
            selectedOption === 'fighter' && styles.selectedCard
          ]}
          onPress={() => setSelectedOption('fighter')}
          activeOpacity={0.8}
        >
          <View style={styles.optionIcon}>
            <Ionicons name="fitness" size={60} color="#FFD700" />
          </View>
          
          <Text style={styles.optionTitle}>🥊 ¡QUIERO PELEAR!</Text>
          <Text style={styles.optionDescription}>
            Inscríbete como peleador y forma parte de la cartelera. 
            Muestra tu talento en el ring.
          </Text>

          {selectedOption === 'fighter' && (
            <TouchableOpacity 
              style={styles.actionButton}
              onPress={handleFighterRegistration}
            >
              <Text style={styles.actionButtonText}>INSCRIBIRME AHORA</Text>
              <Ionicons name="arrow-forward" size={20} color="#000" />
            </TouchableOpacity>
          )}

          <View style={styles.benefitsList}>
            <BenefitItem text="Participación en torneo oficial" />
            <BenefitItem text="Perfil público de peleador" />
            <BenefitItem text="Sistema de promoción por redes" />
            <BenefitItem text="Registro de récord profesional" />
          </View>
        </TouchableOpacity>

        {/* Opción 2: Comprar Entradas */}
        <TouchableOpacity 
          style={[
            styles.optionCard,
            selectedOption === 'ticket' && styles.selectedCard
          ]}
          onPress={() => setSelectedOption('ticket')}
          activeOpacity={0.8}
        >
          <View style={styles.optionIcon}>
            <Ionicons name="ticket" size={60} color="#FFD700" />
          </View>
          
          <Text style={styles.optionTitle}>🎟️ COMPRAR ENTRADAS</Text>
          <Text style={styles.optionDescription}>
            Asegura tu lugar en la noche más espectacular del boxeo. 
            Diferentes categorías disponibles.
          </Text>

          {selectedOption === 'ticket' && (
            <TouchableOpacity 
              style={styles.actionButton}
              onPress={handleTicketPurchase}
            >
              <Text style={styles.actionButtonText}>VER ENTRADAS</Text>
              <Ionicons name="arrow-forward" size={20} color="#000" />
            </TouchableOpacity>
          )}

          <View style={styles.ticketTypes}>
            <TicketType name="VIP Ringside" price="S/ 250" />
            <TicketType name="General" price="S/ 80" />
            <TicketType name="Estudiante" price="S/ 50" />
          </View>
        </TouchableOpacity>

        {/* Información adicional */}
        <View style={styles.infoBox}>
          <Ionicons name="information-circle" size={24} color="#FFD700" />
          <Text style={styles.infoText}>
            ¿Tienes dudas? Revisa las reglas del torneo o contáctanos 
            para más información.
          </Text>
        </View>

        {/* Botón: Reglas del Torneo */}
        <TouchableOpacity 
          style={styles.rulesButton}
          onPress={() => {
            Alert.alert(
              'Reglas del Torneo',
              '• Edad mínima: 18 años\n' +
              '• Peso: Según categoría\n' +
              '• Experiencia: Mínimo 3 peleas amateur\n' +
              '• Certificado médico obligatorio\n' +
              '• Seguro de accidentes incluido'
            );
          }}
        >
          <Ionicons name="book" size={20} color="#FFD700" />
          <Text style={styles.rulesText}>Ver Reglas del Torneo</Text>
        </TouchableOpacity>

        {/* Botón: Sorteo/Premios */}
        <TouchableOpacity 
          style={styles.prizeButton}
          onPress={() => {
            Alert.alert(
              '🎁 Gana Entradas',
              '¡Comparte el evento en tus redes y participa por entradas gratis!',
              [
                { text: 'Cerrar' },
                { text: 'Compartir Ahora', onPress: () => console.log('Compartir') }
              ]
            );
          }}
        >
          <Ionicons name="gift" size={24} color="#fff" />
          <Text style={styles.prizeText}>🎉 GANA ENTRADAS GRATIS</Text>
        </TouchableOpacity>
      </ScrollView>
    </SafeAreaView>
  );
}

// Componente auxiliar para beneficios
const BenefitItem = ({ text }: { text: string }) => (
  <View style={styles.benefitItem}>
    <Ionicons name="checkmark-circle" size={18} color="#4CAF50" />
    <Text style={styles.benefitText}>{text}</Text>
  </View>
);

// Componente auxiliar para tipos de entrada
const TicketType = ({ name, price }: { name: string; price: string }) => (
  <View style={styles.ticketTypeItem}>
    <Text style={styles.ticketName}>{name}</Text>
    <Text style={styles.ticketPrice}>{price}</Text>
  </View>
);

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#000',
  },
  scrollContent: {
    padding: 15,
    paddingBottom: 100,
  },
  header: {
    alignItems: 'center',
    marginBottom: 25,
    marginTop: 20,
  },
  headerTitle: {
    color: '#FFD700',
    fontSize: 24,
    fontWeight: 'bold',
    letterSpacing: 2,
    textAlign: 'center',
  },
  headerSubtitle: {
    color: '#888',
    fontSize: 14,
    marginTop: 8,
    textAlign: 'center',
  },
  optionCard: {
    backgroundColor: '#1a1a1a',
    borderRadius: 15,
    padding: 20,
    marginBottom: 20,
    borderWidth: 2,
    borderColor: '#333',
  },
  selectedCard: {
    borderColor: '#FFD700',
    backgroundColor: '#1f1f1f',
  },
  optionIcon: {
    alignItems: 'center',
    marginBottom: 15,
  },
  optionTitle: {
    color: '#FFD700',
    fontSize: 20,
    fontWeight: 'bold',
    textAlign: 'center',
    marginBottom: 10,
  },
  optionDescription: {
    color: '#ccc',
    fontSize: 14,
    textAlign: 'center',
    lineHeight: 22,
    marginBottom: 15,
  },
  actionButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#FFD700',
    paddingVertical: 15,
    borderRadius: 10,
    marginTop: 10,
    marginBottom: 15,
    gap: 10,
  },
  actionButtonText: {
    color: '#000',
    fontSize: 16,
    fontWeight: 'bold',
    letterSpacing: 1,
  },
  benefitsList: {
    marginTop: 10,
    gap: 10,
  },
  benefitItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  benefitText: {
    color: '#aaa',
    fontSize: 13,
  },
  ticketTypes: {
    marginTop: 15,
    gap: 8,
  },
  ticketTypeItem: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    backgroundColor: '#111',
    padding: 12,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#333',
  },
  ticketName: {
    color: '#fff',
    fontSize: 14,
    fontWeight: '600',
  },
  ticketPrice: {
    color: '#FFD700',
    fontSize: 16,
    fontWeight: 'bold',
  },
  infoBox: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    backgroundColor: '#1a1a1a',
    padding: 15,
    borderRadius: 10,
    gap: 12,
    marginTop: 10,
    borderWidth: 1,
    borderColor: '#333',
  },
  infoText: {
    color: '#ccc',
    fontSize: 13,
    lineHeight: 20,
    flex: 1,
  },
  rulesButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: '#FFD700',
    paddingVertical: 12,
    borderRadius: 8,
    marginTop: 15,
    gap: 8,
  },
  rulesText: {
    color: '#FFD700',
    fontSize: 14,
    fontWeight: '600',
  },
  prizeButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#FF3B30',
    paddingVertical: 15,
    borderRadius: 10,
    marginTop: 15,
    gap: 10,
    shadowColor: '#FF3B30',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.4,
    shadowRadius: 8,
    elevation: 6,
  },
  prizeText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
});