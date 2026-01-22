import React from 'react';
import { View, Text, StyleSheet, Image, TouchableOpacity } from 'react-native';
import { FightMatch } from '../types/boxeo';

// Definimos la interfaz de las propiedades
interface ScheduledProps {
  data: {
    peleas_pactadas: FightMatch[];
  };
}

export const ScheduledFights: React.FC<ScheduledProps> = ({ data }) => {
  // Obtenemos las peleas del objeto data
  const matches = data.peleas_pactadas || [];

  return (
    <View style={styles.section}>
      <View style={styles.headerContainer}>
        <Text style={styles.sectionTitle}>Peleas Pactadas</Text>
        <Text style={styles.sectionSub}>Próximos encuentros confirmados</Text>
      </View>

      <View style={styles.list}>
        {matches.map((match) => (
          <TouchableOpacity key={match.id} activeOpacity={0.8} style={styles.card}>
            
            {/* Peleador A (Izquierda) */}
            <View style={styles.fighterRow}>
              <View style={styles.avatarWrapper}>
                <Image 
                  source={{ uri: match.fighterA.foto_url || match.fighterA.foto_url }} 
                  style={styles.avatar} 
                />
              </View>
              <View style={styles.infoCol}>
                <Text style={styles.nickname} numberOfLines={1}>
                  {match.fighterA.apodo}
                </Text>
                <Text style={styles.company} numberOfLines={1}>
                  {match.fighterA.empresa}
                </Text>
              </View>
            </View>

            {/* Separador VS */}
            <View style={styles.vsContainer}>
              <Text style={styles.vsText}>VS</Text>
            </View>

            {/* Peleador B (Derecha) */}
            <View style={[styles.fighterRow, { justifyContent: 'flex-end' }]}>
              <View style={[styles.infoCol, { alignItems: 'flex-end' }]}>
                <Text style={[styles.nickname, { textAlign: 'right' }]} numberOfLines={1}>
                  {match.fighterB.apodo}
                </Text>
                <Text style={[styles.company, { textAlign: 'right' }]} numberOfLines={1}>
                  {match.fighterB.empresa}
                </Text>
              </View>
              <View style={[styles.avatarWrapper, { marginLeft: 12, marginRight: 0 }]}>
                <Image 
                  source={{ uri: match.fighterB.foto_url || match.fighterB.foto_url }} 
                  style={styles.avatar} 
                />
              </View>
            </View>

          </TouchableOpacity>
        ))}
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  section: { 
    paddingHorizontal: 20, 
    marginTop: 25 
  },
  headerContainer: { 
    marginBottom: 16 
  },
  sectionTitle: { 
    color: 'white', 
    fontSize: 20, 
    fontWeight: 'bold',
    letterSpacing: -0.5 
  },
  sectionSub: { 
    color: '#636366', 
    fontSize: 12, 
    marginTop: 2 
  },
  list: { 
    gap: 12 
  },
  card: {
    backgroundColor: '#1c1c1e',
    padding: 14,
    borderRadius: 22,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.06)',
  },
  fighterRow: { 
    flexDirection: 'row', 
    alignItems: 'center', 
    flex: 1 
  },
  avatarWrapper: {
    width: 46,
    height: 46,
    borderRadius: 23,
    backgroundColor: '#2c2c2e',
    overflow: 'hidden',
    borderWidth: 1.5,
    borderColor: 'rgba(255,255,255,0.1)',
    marginRight: 10
  },
  avatar: { 
    width: '100%', 
    height: '100%', 
    resizeMode: 'cover' 
  },
  infoCol: { 
    flex: 1, 
    justifyContent: 'center' 
  },
  nickname: { 
    color: 'white', 
    fontSize: 13, 
    fontWeight: 'bold',
    letterSpacing: 0.2
  },
  company: { 
    color: '#8e8e93', 
    fontSize: 10,
    marginTop: 1
  },
  vsContainer: { 
    paddingHorizontal: 12 
  },
  vsText: { 
    fontSize: 20, 
    fontWeight: '900', 
    fontStyle: 'italic', 
    color: 'rgba(255, 215, 0, 0.25)', // Dorado semitransparente
  }
});