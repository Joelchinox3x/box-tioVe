// Tipos principales de la aplicación

export interface Fighter {
  id: number;
  nombre: string;
  apellido: string;
  apodo?: string;
  peso: number;
  altura: number;
  edad: number;
  email: string;
  telefono: string;
  dni: string;
  categoria?: string;
  foto_perfil?: string;
  victorias?: number;
  derrotas?: number;
  empates?: number;
  created_at?: string;
}

export interface Event {
  id: number;
  titulo: string;
  descripcion?: string;
  fecha_evento: string;
  lugar: string;
  direccion?: string;
  precio_entrada?: number;
  estado: 'activo' | 'finalizado' | 'cancelado';
  imagen?: string;
}

export interface FightMatch {
  id: number;
  evento_id: number;
  peleador1_id: number;
  peleador2_id: number;
  peleador1?: Fighter;
  peleador2?: Fighter;
  categoria: string;
  rondas: number;
  estado: 'programada' | 'en_curso' | 'finalizada' | 'cancelada';
  ganador_id?: number;
  fecha_pelea?: string;
}

export interface User {
  id: number;
  nombre: string;
  email: string;
  telefono?: string;
  tipo: 'admin' | 'usuario' | 'peleador';
  foto?: string;
}

export interface Ticket {
  id: number;
  evento_id: number;
  usuario_id: number;
  tipo: 'general' | 'vip' | 'premium';
  precio: number;
  codigo_qr?: string;
  estado: 'activo' | 'usado' | 'cancelado';
}

// Tipos de respuesta de la API
export interface ApiResponse<T> {
  success: boolean;
  data: T;
  message?: string;
  error?: string;
}

export interface EventData {
  evento: Event;
  peleadores_destacados: Fighter[];
  peleas_pactadas: FightMatch[];
}
