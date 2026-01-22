import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet } from 'react-native';

interface CountdownTimerProps {
  eventDate: string; // ISO format: "2025-12-25T18:00:00"
}

export const CountdownTimer: React.FC<CountdownTimerProps> = ({ eventDate }) => {
  const [timeLeft, setTimeLeft] = useState({
    days: 0,
    hours: 0,
    minutes: 0,
    seconds: 0
  });

  useEffect(() => {
    const calculateTimeLeft = () => {
      const difference = +new Date(eventDate) - +new Date();
      
      if (difference > 0) {
        setTimeLeft({
          days: Math.floor(difference / (1000 * 60 * 60 * 24)),
          hours: Math.floor((difference / (1000 * 60 * 60)) % 24),
          minutes: Math.floor((difference / 1000 / 60) % 60),
          seconds: Math.floor((difference / 1000) % 60)
        });
      }
    };

    calculateTimeLeft();
    const timer = setInterval(calculateTimeLeft, 1000);

    return () => clearInterval(timer);
  }, [eventDate]);

  return (
    <View style={styles.container}>
      <Text style={styles.title}>🥊 CUENTA REGRESIVA 🥊</Text>
      <View style={styles.timerRow}>
        <View style={styles.timeBlock}>
          <Text style={styles.number}>{timeLeft.days}</Text>
          <Text style={styles.label}>DÍAS</Text>
        </View>
        <Text style={styles.separator}>:</Text>
        <View style={styles.timeBlock}>
          <Text style={styles.number}>{String(timeLeft.hours).padStart(2, '0')}</Text>
          <Text style={styles.label}>HRS</Text>
        </View>
        <Text style={styles.separator}>:</Text>
        <View style={styles.timeBlock}>
          <Text style={styles.number}>{String(timeLeft.minutes).padStart(2, '0')}</Text>
          <Text style={styles.label}>MIN</Text>
        </View>
        <Text style={styles.separator}>:</Text>
        <View style={styles.timeBlock}>
          <Text style={styles.number}>{String(timeLeft.seconds).padStart(2, '0')}</Text>
          <Text style={styles.label}>SEG</Text>
        </View>
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#1a1a1a',
    padding: 20,
    marginHorizontal: 15,
    marginVertical: 15,
    borderRadius: 15,
    borderWidth: 2,
    borderColor: '#FFD700',
    alignItems: 'center',
  },
  title: {
    color: '#FFD700',
    fontSize: 16,
    fontWeight: 'bold',
    marginBottom: 15,
    letterSpacing: 2,
  },
  timerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
  },
  timeBlock: {
    alignItems: 'center',
    minWidth: 60,
  },
  number: {
    color: '#FFD700',
    fontSize: 32,
    fontWeight: 'bold',
  },
  label: {
    color: '#888',
    fontSize: 10,
    fontWeight: '600',
    marginTop: 4,
  },
  separator: {
    color: '#FFD700',
    fontSize: 28,
    fontWeight: 'bold',
    marginHorizontal: 5,
  },
});