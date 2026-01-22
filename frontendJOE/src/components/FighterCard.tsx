import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Image, Share } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

interface FighterCardProps {
  fighter: {
    id: number;
    nombre: string;
    apodo: string;
    foto: string;
    club: string;
    estilo: 'fajador' | 'estilista';
    record: string;
    promociones: number;
  };
  onPress?: () => void;
}

export const FighterCard: React.FC<FighterCardProps> = ({ fighter, onPress }) => {
  
  const handleShare = async () => {
    try {
      const shareUrl = `https://boxevent.app/peleador/${fighter.id}`;
      await Share.share({
        message: `🥊 ¡Apoya a ${fighter.nombre} "${fighter.apodo}"!\n\n` +
                 `${fighter.estilo.toUpperCase()} | ${fighter.club}\n` +
                 `Récord: ${fighter.record}\n\n` +
                 `Ver perfil: ${shareUrl}`,
        url: shareUrl,
      });
    } catch (error) {
      console.error('Error compartiendo:', error);
    }
  };

  return (
    <TouchableOpacity 
      style={styles.card}
      onPress={onPress}
      activeOpacity={0.8}
    >
      {/* Foto del peleador */}
      <Image 
        source={{ uri: fighter.foto }} 
        style={styles.photo}
        resizeMode="cover"
      />
      
      {/* Badge de estilo */}
      <View style={[
        styles.styleBadge,
        { backgroundColor: fighter.estilo === 'fajador' ? '#FF3B30' : '#007AFF' }
      ]}>
        <Text style={styles.styleText}>
          {fighter.estilo === 'fajador' ? '🔥 FAJADOR' : '🎯 ESTILISTA'}
        </Text>
      </View>

      {/* Info del peleador */}
      <View style={styles.info}>
        <Text style={styles.name}>{fighter.nombre}</Text>
        <Text style={styles.nickname}>"{fighter.apodo}"</Text>
        <Text style={styles.club}>🥋 {fighter.club}</Text>
        <Text style={styles.record}>Récord: {fighter.record}</Text>
        
        {/* Botón de promoción */}
        <TouchableOpacity 
          style={styles.shareButton}
          onPress={handleShare}
        >
          <Ionicons name="share-social" size={18} color="#000" />
          <Text style={styles.shareText}>PROMOCIONAR</Text>
        </TouchableOpacity>

        {/* Contador de promociones */}
        <View style={styles.promotions}>
          <Ionicons name="flame" size={16} color="#FFD700" />
          <Text style={styles.promotionsText}>
            {fighter.promociones} personas lo apoyan
          </Text>
        </View>
      </View>
    </TouchableOpacity>
  );
};

const styles = StyleSheet.create({
  card: {
    backgroundColor: '#1a1a1a',
    borderRadius: 15,
    marginHorizontal: 15,
    marginVertical: 10,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: '#333',
  },
  photo: {
    width: '100%',
    height: 200,
    backgroundColor: '#000',
  },
  styleBadge: {
    position: 'absolute',
    top: 10,
    right: 10,
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 20,
  },
  styleText: {
    color: '#fff',
    fontSize: 11,
    fontWeight: 'bold',
  },
  info: {
    padding: 15,
  },
  name: {
    color: '#fff',
    fontSize: 20,
    fontWeight: 'bold',
  },
  nickname: {
    color: '#FFD700',
    fontSize: 16,
    fontStyle: 'italic',
    marginTop: 2,
  },
  club: {
    color: '#888',
    fontSize: 13,
    marginTop: 8,
  },
  record: {
    color: '#888',
    fontSize: 13,
    marginTop: 4,
  },
  shareButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#FFD700',
    paddingVertical: 12,
    borderRadius: 10,
    marginTop: 15,
    gap: 8,
  },
  shareText: {
    color: '#000',
    fontSize: 14,
    fontWeight: 'bold',
  },
  promotions: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 10,
    gap: 5,
  },
  promotionsText: {
    color: '#FFD700',
    fontSize: 12,
  },
});