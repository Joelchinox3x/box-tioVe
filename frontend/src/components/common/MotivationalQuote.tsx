import React, { useMemo } from 'react';
import { Text, TextStyle } from 'react-native';

const QUOTES = [
    // Motivación Pura
    '"El dolor de hoy es la fuerza de mañana."',
    '"Las peleas se ganan en el gimnasio, en el ring solo se recogen los trofeos."',
    '"Tu único rival verdadero es el que ves en el espejo."',
    '"No cuentes los días, haz que los días cuenten."',
    '"El sudor es la grasa llorando."',

    // Resiliencia
    '"No importa qué tan fuerte golpees, sino qué tan fuerte pueden golpearte y seguir avanzando."',
    '"Caerse está permitido, levantarse es obligatorio."',
    '"La lona es solo un lugar para descansar un segundo, no para quedarse."',
    '"Un campeón es alguien que se levanta cuando el resto no puede."',
    '"El éxito es ir de fracaso en fracaso sin perder el entusiasmo."',

    // Técnica y Enfoque
    '"Cabeza fría, corazón caliente, puños de acero."',
    '"La defensa es el primer paso del ataque."',
    '"Vuela como mariposa, pica como abeja."',
    '"La disciplina es hacer lo que odias como si lo amaras."',
    '"Todo el mundo tiene un plan hasta que recibe el primer puñetazo."',

    // Cortas y Directas
    '"Nunca te rindas."',
    '"Golpea, muévete, repite."',
    '"Guardia arriba siempre."',
    '"Nacido para pelear."',
    '"Sin sacrificio no hay victoria."',
];

interface MotivationalQuoteProps {
    style?: TextStyle;
}

export const MotivationalQuote: React.FC<MotivationalQuoteProps> = ({ style }) => {
    const quote = useMemo(() => QUOTES[Math.floor(Math.random() * QUOTES.length)], []);

    return <Text style={style}>{quote}</Text>;
};

export { QUOTES };
