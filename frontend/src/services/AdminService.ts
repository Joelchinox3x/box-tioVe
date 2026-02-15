import api from './api';
import { Config } from '../config/config';

export class AdminService {
  /**
   * Obtener estadísticas del dashboard
   */
  static async getEstadisticas() {
    const response = await api.get('/admin/estadisticas');
    return response.data;
  }

  /**
   * Obtener peleadores pendientes de aprobación
   */
  static async getPeleadoresPendientes() {
    const response = await api.get('/admin/peleadores-pendientes');
    return response.data;
  }

  /**
   * Obtener TODOS los peleadores con filtro opcional
   * @param filtro - 'todos' | 'pendiente' | 'aprobado' | 'rechazado'
   */
  static async getPeleadores(filtro: string = 'todos') {
    const response = await api.get(`/admin/peleadores?filtro=${filtro}`);
    return response.data;
  }

  /**
   * Aprobar o rechazar peleador
   */
  static async cambiarEstadoPeleador(peleadorId: number, estado: 'aprobado' | 'rechazado', notas?: string) {
    const url = `/admin/peleadores/${peleadorId}`;
    const payload = { estado, notas: notas || '' };

    console.log('📤 AdminService - Enviando petición PUT:', {
      url,
      payload,
      fullUrl: `${Config.API_URL}${url}`
    });

    const response = await api.put(url, payload);

    console.log('📥 AdminService - Respuesta recibida:', response.data);
    return response.data;
  }

  /**
   * Editar datos de un peleador
   */
  static async editPeleador(peleadorId: number, data: Record<string, any>) {
    const url = `/admin/peleadores/${peleadorId}`;
    const response = await api.patch(url, data);
    return response.data;
  }

  /**
   * Eliminar peleador y todos sus datos relacionados
   */
  static async deletePeleador(peleadorId: number) {
    const url = `/admin/peleadores/${peleadorId}`;

    console.log('🗑️ AdminService - Eliminando peleador:', {
      url,
      peleadorId,
      fullUrl: `${Config.API_URL}${url}`
    });

    const response = await api.delete(url);

    console.log('✅ AdminService - Peleador eliminado:', response.data);
    return response.data;
  }

  /**
   * Obtener todos los clubs
   */
  static async getAllClubs() {
    const response = await api.get('/admin/clubs');
    return response.data;
  }

  /**
   * Crear nuevo club
   */
  static async crearClub(data: {
    nombre: string;
    direccion?: string;
    telefono?: string;
    email?: string;
    descripcion?: string;
  }) {
    const response = await api.post('/admin/clubs', data);
    return response.data;
  }

  /**
   * Buscar usuario por DNI
   */
  static async buscarUsuarioPorDNI(dni: string) {
    const response = await api.get(`/admin/buscar-usuario?dni=${dni}`);
    return response.data;
  }

  /**
   * Asignar dueño a un club
   */
  static async asignarDuenioClub(usuarioId: number, clubId: number) {
    const response = await api.post('/admin/asignar-duenio', {
      usuario_id: usuarioId,
      club_id: clubId,
    });
    return response.data;
  }

  /**
   * ========================================
   * GESTIÓN DE INSCRIPCIONES Y PAGOS
   * ========================================
   */

  /**
   * Obtener todas las inscripciones con filtros opcionales
   */
  static async getInscripciones(filters?: { estado_pago?: string; evento_id?: number }) {
    const params = new URLSearchParams();
    if (filters?.estado_pago) params.append('estado_pago', filters.estado_pago);
    if (filters?.evento_id) params.append('evento_id', filters.evento_id.toString());

    const response = await api.get(`/admin/inscripciones?${params.toString()}`);
    return response.data;
  }

  /**
   * Obtener inscripciones pendientes de pago
   */
  static async getInscripcionesPendientes() {
    const response = await api.get('/admin/inscripciones-pendientes');
    return response.data;
  }

  /**
   * Crear nueva inscripción
   */
  static async crearInscripcion(peleadorId: number, eventoId: number) {
    const response = await api.post('/admin/inscripciones', {
      peleador_id: peleadorId,
      evento_id: eventoId,
    });
    return response.data;
  }

  /**
   * Confirmar pago de una inscripción
   */
  static async confirmarPago(
    inscripcionId: number,
    data: {
      monto_pagado: number;
      metodo_pago: string;
      comprobante_pago?: string;
      notas_admin?: string;
    }
  ) {
    const response = await api.put(`/admin/inscripciones/${inscripcionId}`, data);
    return response.data;
  }

  /**
   * Actualizar precio de inscripción de un evento
   */
  static async actualizarPrecioEvento(eventoId: number, precio: number) {
    const response = await api.put(`/admin/eventos/${eventoId}/precio`, {
      precio_inscripcion_peleador: precio,
    });
    return response.data;
  }

  /**
   * ========================================
   * MÉTODOS DE PAGO
   * ========================================
   */

  static async getMetodosPago(params?: { activo?: number }) {
    const query = params?.activo !== undefined ? `?activo=${params.activo}` : '';
    const response = await api.get(`/admin/metodos-pago${query}`);
    return response.data;
  }

  static async crearMetodoPago(data: {
    codigo: string;
    nombre: string;
    requiere_comprobante?: number;
    activo?: number;
    orden?: number;
    qr_imagen_url?: string | null;
    telefono_receptor?: string | null;
    nombre_receptor?: string | null;
  }) {
    const response = await api.post('/admin/metodos-pago', data);
    return response.data;
  }

  static async actualizarMetodoPago(
    metodoId: number,
    data: {
      codigo?: string;
      nombre?: string;
      requiere_comprobante?: number;
      activo?: number;
      orden?: number;
      qr_imagen_url?: string | null;
      telefono_receptor?: string | null;
      nombre_receptor?: string | null;
    }
  ) {
    const response = await api.put(`/admin/metodos-pago/${metodoId}`, data);
    return response.data;
  }

  /**
   * Subir imagen QR para método de pago
   * Usa fetch nativo como fighterService para evitar problemas con multipart
   */
  static async uploadQRImage(formData: FormData) {
    const response = await fetch(`${Config.API_URL}/admin/metodos-pago/upload-qr`, {
      method: 'POST',
      body: formData,
      headers: {
        'Accept': 'application/json',
      },
    });

    const rawText = await response.text();

    let result;
    try {
      // Intentar extraer solo el JSON si hay warnings de PHP antes
      const jsonMatch = rawText.match(/\{.*\}$/s);
      const jsonText = jsonMatch ? jsonMatch[0] : rawText;
      result = JSON.parse(jsonText);
    } catch (e) {
      console.error('❌ Failed to parse JSON. Raw response:', rawText);
      throw new Error(`Server returned invalid response: ${rawText.substring(0, 500)}`);
    }

    if (!response.ok) {
      throw { response: { data: result, status: response.status } };
    }

    return result;
  }


  /**
   * Obtener los logos activos para todos los tipos (card, pdf, header)
   */
  static async getActiveLogos() {
    const response = await api.get('/admin/branding/active');
    return response.data;
  }

  /**
   * Obtener el historial de logos (opcionalmente filtrado por tipo)
   */
  static async getLogosHistory(tipo?: string) {
    const url = tipo ? `/admin/branding/logos?tipo=${tipo}` : '/admin/branding/logos';
    const response = await api.get(url);
    return response.data;
  }

  /**
   * Subir un nuevo logo para un tipo específico
   */
  static async uploadLogo(formData: FormData) {
    const response = await api.post('/admin/branding/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  }

  /**
   * Cambiar el logo activo desde el historial
   */
  static async setActiveLogo(logoId: number) {
    const response = await api.post('/admin/branding/set-active', { id: logoId });
    return response.data;
  }
}
