// src/types/boxeo.ts

export interface Fighter {
  id: number;
  usuario_id: number;
  nombre: string;
  apodo: string;
  fecha_nacimiento: string;
  peso_actual: number;
  altura: number;
  club: string;
  estilo: 'fajador' | 'estilista';
  foto_perfil: string;
  victorias: number;
  derrotas: number;
  empates: number;
  promociones: number;
  estado_inscripcion: 'pendiente' | 'aprobado' | 'rechazado';
  fecha_inscripcion: string;
}

export interface FightMatch {
  id: number;
  evento_id: number;
  peleador_1: Fighter;
  peleador_2: Fighter;
  categoria_peso: string;
  numero_rounds: number;
  orden_pelea: number;
  resultado: 'pendiente' | 'ganador_1' | 'ganador_2' | 'empate';
  metodo_victoria?: 'KO' | 'TKO' | 'Decision' | 'Descalificacion';
  votos_peleador_1: number;
  votos_peleador_2: number;
  es_pelea_estelar: boolean;
  fecha_creacion: string;
}

export interface Event {
  id: number;
  titulo: string;
  descripcion: string;
  fecha_evento: string;
  lugar: string;
  direccion: string;
  capacidad_total: number;
  estado: 'programado' | 'en_curso' | 'finalizado';
  imagen_portada: string;
  fecha_creacion: string;
}

export interface Ticket {
  id: number;
  evento_id: number;
  nombre: string;
  precio: number;
  cantidad_disponible: number;
  cantidad_vendida: number;
  descripcion: string;
  beneficios: string[];
  activo: boolean;
}

export interface Purchase {
  id: number;
  usuario_id: number;
  tipo_entrada_id: number;
  cantidad: number;
  precio_total: number;
  codigo_qr: string;
  estado: 'pendiente' | 'pagado' | 'usado' | 'cancelado';
  metodo_pago: string;
  fecha_compra: string;
}

export interface User {
  id: number;
  email: string;
  nombre: string;
  telefono?: string;
  tipo_usuario: 'espectador' | 'peleador' | 'admin';
  fecha_registro: string;
  activo: boolean;
}

export interface Sponsor {
  id: number;
  evento_id: number;
  nombre: string;
  logo: string;
  url_sitio?: string;
  nivel: 'oro' | 'plata' | 'bronce';
  activo: boolean;
}

export interface Vote {
  id: number;
  pelea_id: number;
  usuario_id?: number;
  peleador_votado_id: number;
  fecha_voto: string;
}

export interface Promotion {
  id: number;
  usuario_id?: number;
  peleador_id: number;
  tipo_promocion: 'compartir_whatsapp' | 'compartir_instagram' | 'compartir_facebook';
  fecha_promocion: string;
}

// Tipos para navegación
export type RootStackParamList = {
  Home: undefined;
  Event: undefined;
  Register: undefined;
  Fighters: undefined;
  Profile: undefined;
  FighterDetail: { fighterId: number };
  FighterForm: undefined;
  TicketStore: undefined;
  Admin: undefined;
};

// Respuestas de API
export interface ApiResponse<T> {
  success: boolean;
  data: T;
  message?: string;
}

export interface EventDataResponse {
  evento: Event;
  peleadores_destacados: Fighter[];
  peleas_pactadas: FightMatch[];
  patrocinadores: Sponsor[];
}