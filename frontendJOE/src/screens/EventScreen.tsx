import React, { useEffect, useState } from 'react';
import { 
  SafeAreaView, 
  ScrollView, 
  StyleSheet, 
  View, 
  Text,
  Image,
  TouchableOpacity,
  Linking 
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import MapView, { Marker } from 'react-native-maps';
import boxApi from '../api/boxApi';

interface EventData {
  titulo: string;
  descripcion: string;
  fecha_evento: string;
  lugar: string;
  direccion: string;
  imagen_portada: string;
  capacidad_total: number;
  patrocinadores: Array<{
    id: number;
    nombre: string;
    logo: string;
    nivel: 'oro' | 'plata' | 'bronce';
  }>;
}

export default function EventScreen() {
  const [eventData, setEventData] = useState<EventData | null>(null);

  useEffect(() => {
    loadEventDetails();
  }, []);

  const loadEventDetails = async () => {
    try {
      const res = await boxApi.get('/eventos');
      setEventData(res.data.evento);
    } catch (err) {
      console.error('Error cargando detalles:', err);
    }
  };

  const openMaps = () => {
    const address = encodeURIComponent(eventData?.direccion || '');
    const url = `https://www.google.com/maps/search/?api=1&query=${address}`;
    Linking.openURL(url);
  };

  if (!eventData) {
    return (
      <View style={styles.loading}>
        <Text style={styles.loadingText}>Cargando...</Text>
      </View>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView showsVerticalScrollIndicator={false}>
        {/* Imagen de portada */}
        <Image 
          source={{ uri: eventData.imagen_portada }} 
          style={styles.coverImage}
          resizeMode="cover"
        />

        {/* Información principal */}
        <View style={styles.content}>
          <Text style={styles.title}>{eventData.titulo}</Text>
          <Text style={styles.description}>{eventData.descripcion}</Text>

          {/* Fecha y lugar */}
          <View style={styles.infoCard}>
            <View style={styles.infoRow}>
              <Ionicons name="calendar" size={24} color="#FFD700" />
              <View style={styles.infoText}>
                <Text style={styles.infoLabel}>Fecha</Text>
                <Text style={styles.infoValue}>
                  {new Date(eventData.fecha_evento).toLocaleDateString('es-ES', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                  })}
                </Text>
              </View>
            </View>

            <View style={styles.divider} />

            <View style={styles.infoRow}>
              <Ionicons name="location" size={24} color="#FFD700" />
              <View style={styles.infoText}>
                <Text style={styles.infoLabel}>Lugar</Text>
                <Text style={styles.infoValue}>{eventData.lugar}</Text>
                <Text style={styles.infoAddress}>{eventData.direccion}</Text>
              </View>
            </View>

            <TouchableOpacity 
              style={styles.mapButton}
              onPress={openMaps}
            >
              <Ionicons name="map" size={20} color="#000" />
              <Text style={styles.mapButtonText}>Ver en Mapa</Text>
            </TouchableOpacity>
          </View>

          {/* Cronograma */}
          <View style={styles.section}>
            <Text style={styles.sectionTitle}>📋 CRONOGRAMA</Text>
            <View style={styles.scheduleCard}>
              <ScheduleItem time="17:00" activity="Apertura de puertas" />
              <ScheduleItem time="18:00" activity="Ceremonia de pesaje público" />
              <ScheduleItem time="19:00" activity="Inicio de peleas preliminares" />
              <ScheduleItem time="21:30" activity="Pelea estelar" />
              <ScheduleItem time="23:00" activity="Premiación y cierre" />
            </View>
          </View>

          {/* Muro de Patrocinadores */}
          {eventData.patrocinadores && eventData.patrocinadores.length > 0 && (
            <View style={styles.section}>
              <Text style={styles.sectionTitle}>🤝 PATROCINADORES</Text>
              <View style={styles.sponsorsGrid}>
                {eventData.patrocinadores.map(sponsor => (
                  <View 
                    key={sponsor.id} 
                    style={[
                      styles.sponsorCard,
                      sponsor.nivel === 'oro' && styles.goldSponsor
                    ]}
                  >
                    <Image 
                      source={{ uri: sponsor.logo }} 
                      style={styles.sponsorLogo}
                      resizeMode="contain"
                    />
                    <Text style={styles.sponsorName}>{sponsor.nombre}</Text>
                  </View>
                ))}
              </View>
            </View>
          )}

          {/* Información adicional */}
          <View style={styles.infoBox}>
            <Ionicons name="information-circle" size={24} color="#FFD700" />
            <Text style={styles.infoBoxText}>
              Capacidad: {eventData.capacidad_total} personas
            </Text>
          </View>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

// Componente auxiliar para el cronograma
const ScheduleItem = ({ time, activity }: { time: string; activity: string }) => (
  <View style={styles.scheduleItem}>
    <View style={styles.timeBadge}>
      <Text style={styles.timeText}>{time}</Text>
    </View>
    <Text style={styles.activityText}>{activity}</Text>
  </View>
);

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#000',
  },
  loading: {
    flex: 1,
    backgroundColor: '#000',
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    color: '#FFD700',
    fontSize: 14,
  },
  coverImage: {
    width: '100%',
    height: 250,
    backgroundColor: '#1a1a1a',
  },
  content: {
    padding: 15,
  },
  title: {
    color: '#FFD700',
    fontSize: 24,
    fontWeight: 'bold',
    marginBottom: 10,
    textAlign: 'center',
  },
  description: {
    color: '#ccc',
    fontSize: 14,
    lineHeight: 22,
    marginBottom: 20,
    textAlign: 'center',
  },
  infoCard: {
    backgroundColor: '#1a1a1a',
    borderRadius: 12,
    padding: 15,
    borderWidth: 1,
    borderColor: '#333',
    marginBottom: 20,
  },
  infoRow: {
    flexDirection: 'row',
    gap: 15,
  },
  infoText: {
    flex: 1,
  },
  infoLabel: {
    color: '#888',
    fontSize: 12,
    marginBottom: 4,
  },
  infoValue: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
    textTransform: 'capitalize',
  },
  infoAddress: {
    color: '#aaa',
    fontSize: 13,
    marginTop: 4,
  },
  divider: {
    height: 1,
    backgroundColor: '#333',
    marginVertical: 15,
  },
  mapButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#FFD700',
    paddingVertical: 12,
    borderRadius: 8,
    marginTop: 15,
    gap: 8,
  },
  mapButtonText: {
    color: '#000',
    fontSize: 14,
    fontWeight: 'bold',
  },
  section: {
    marginBottom: 25,
  },
  sectionTitle: {
    color: '#FFD700',
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 12,
    letterSpacing: 1,
  },
  scheduleCard: {
    backgroundColor: '#1a1a1a',
    borderRadius: 12,
    padding: 15,
    borderWidth: 1,
    borderColor: '#333',
  },
  scheduleItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#333',
    gap: 15,
  },
  timeBadge: {
    backgroundColor: '#FFD700',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 6,
  },
  timeText: {
    color: '#000',
    fontSize: 14,
    fontWeight: 'bold',
  },
  activityText: {
    color: '#fff',
    fontSize: 14,
    flex: 1,
  },
  sponsorsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
  },
  sponsorCard: {
    backgroundColor: '#1a1a1a',
    borderRadius: 10,
    padding: 15,
    width: '48%',
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#333',
  },
  goldSponsor: {
    borderColor: '#FFD700',
    borderWidth: 2,
  },
  sponsorLogo: {
    width: 80,
    height: 80,
    marginBottom: 8,
  },
  sponsorName: {
    color: '#fff',
    fontSize: 12,
    textAlign: 'center',
  },
  infoBox: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#1a1a1a',
    padding: 12,
    borderRadius: 8,
    gap: 10,
    marginTop: 10,
  },
  infoBoxText: {
    color: '#ccc',
    fontSize: 13,
  },
});