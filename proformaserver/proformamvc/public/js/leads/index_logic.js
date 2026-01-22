// ============================================
// SISTEMA DE NOTIFICACIONES MODERNO
// ============================================
function mostrarNotificacion(titulo, mensaje, tipo = 'info', duracion = 3000) {
    const container = document.getElementById('notificationContainer');

    const colores = {
        success: 'bg-green-50 border-green-500 text-green-800',
        info: 'bg-blue-50 border-blue-500 text-blue-800',
        warning: 'bg-yellow-50 border-yellow-500 text-yellow-800',
        error: 'bg-red-50 border-red-500 text-red-800'
    };

    const iconos = {
        success: 'ph-check-circle',
        info: 'ph-info',
        warning: 'ph-warning',
        error: 'ph-x-circle'
    };

    const notificacion = document.createElement('div');
    notificacion.className = `${colores[tipo]} border-l-4 p-3 rounded-lg shadow-lg animate-slide-in`;
    notificacion.innerHTML = `
      <div class="flex items-start gap-2">
        <i class="ph-bold ${iconos[tipo]} text-lg mt-0.5"></i>
        <div class="flex-1">
          <p class="font-bold text-sm">${titulo}</p>
          <p class="text-xs mt-0.5">${mensaje}</p>
        </div>
      </div>
    `;

    container.appendChild(notificacion);

    setTimeout(() => {
        notificacion.style.opacity = '0';
        notificacion.style.transform = 'translateX(100%)';
        setTimeout(() => notificacion.remove(), 300);
    }, duracion);
}

// ============================================
// MANEJAR APROBACIÓN
// ============================================
function handleApprove(leadId, button) {
    const card = button.closest('.group');

    // Animación de salida
    card.style.transition = 'all 0.3s ease-out';
    card.style.opacity = '0';
    card.style.transform = 'scale(0.95) translateX(20px)';

    setTimeout(() => {
        fetch('/leads/approve', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + leadId
        })
            .then(response => {
                if (response.ok) {
                    card.remove();
                    mostrarNotificacion('Éxito', 'Cliente aprobado correctamente', 'success');

                    // Verificar si ya no hay más leads
                    const remainingCards = document.querySelectorAll('.group.relative');
                    if (remainingCards.length === 0) {
                        setTimeout(() => location.reload(), 1000);
                    }
                } else {
                    card.style.opacity = '1';
                    card.style.transform = 'none';
                    mostrarNotificacion('Error', 'No se pudo aprobar el cliente', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                card.style.opacity = '1';
                card.style.transform = 'none';
                mostrarNotificacion('Error', 'Error de conexión', 'error');
            });
    }, 300);
}

// ============================================
// MANEJAR RECHAZO
// ============================================
function handleReject(leadId, button) {
    const card = button.closest('.group');

    // Animación de salida
    card.style.transition = 'all 0.3s ease-out';
    card.style.opacity = '0';
    card.style.transform = 'scale(0.95) translateX(-20px)';

    setTimeout(() => {
        fetch('/leads/reject', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + leadId
        })
            .then(response => {
                if (response.ok) {
                    card.remove();
                    mostrarNotificacion('Éxito', 'Solicitud rechazada', 'success');

                    // Verificar si ya no hay más leads
                    const remainingCards = document.querySelectorAll('.group.relative');
                    if (remainingCards.length === 0) {
                        setTimeout(() => location.reload(), 1000);
                    }
                } else {
                    card.style.opacity = '1';
                    card.style.transform = 'none';
                    mostrarNotificacion('Error', 'No se pudo rechazar la solicitud', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                card.style.opacity = '1';
                card.style.transform = 'none';
                mostrarNotificacion('Error', 'Error de conexión', 'error');
            });
    }, 300);
}

// ============================================
// BÚSQUEDA DNI/RUC EN UN SOLO MODAL (4 FASES)
// ============================================
let currentLeadId = null;
let currentDniRuc = null;
let currentNombreActual = null;
let currentDireccionActual = null;
let consultaData = null;

// FASE 1: Abrir modal y mostrar información de confirmación
function abrirModalConfirmacion(leadId, dniRuc, nombreActual, direccionActual) {
    currentLeadId = leadId;
    currentDniRuc = dniRuc;
    currentNombreActual = nombreActual;
    currentDireccionActual = direccionActual;

    // Detectar tipo de documento
    const docLength = dniRuc.length;
    const isDni = docLength === 8;
    const isRuc = docLength === 11;

    // Mostrar modal
    const modal = document.getElementById('modalConfirmacionBusqueda');
    modal.classList.remove('hidden');

    // Resetear a fase 1 (confirmación)
    mostrarFase('confirmacion');

    // Configurar header
    const iconHeader = document.getElementById('modalHeaderIcon');
    if (iconHeader) iconHeader.className = 'ph-bold ph-info text-xl';

    document.getElementById('modalHeaderTitle').textContent = 'Confirmar Búsqueda';
    document.getElementById('confirmModalDniRuc').textContent = `${isDni ? 'DNI' : 'RUC'}: ${dniRuc}`;

    // Configurar información de la API
    if (isDni) {
        document.getElementById('confirmFuente').textContent = 'RENIEC (Registro Nacional de Identificación)';
        document.getElementById('confirmCampos').textContent = 'Se actualizará: Nombre completo';
    } else if (isRuc) {
        document.getElementById('confirmFuente').textContent = 'SUNAT (Sistema Tributario Nacional)';
        document.getElementById('confirmCampos').textContent = 'Se actualizará: Razón social y dirección fiscal';
    } else {
        document.getElementById('confirmFuente').textContent = 'Documento no reconocido';
        document.getElementById('confirmCampos').textContent = 'El documento debe tener 8 u 11 dígitos';
    }

    // Mostrar datos actuales
    document.getElementById('confirmNombreActual').textContent = nombreActual || 'Sin nombre registrado';

    if (direccionActual && direccionActual.trim() !== '') {
        document.getElementById('confirmDireccionActualContainer').classList.remove('hidden');
        document.getElementById('confirmDireccionActual').textContent = direccionActual;
    } else {
        document.getElementById('confirmDireccionActualContainer').classList.add('hidden');
    }
}

// Función helper para cambiar entre fases
function mostrarFase(fase) {
    document.getElementById('faseConfirmacion').classList.add('hidden');
    document.getElementById('faseBuscando').classList.add('hidden');
    document.getElementById('faseResultados').classList.add('hidden');
    document.getElementById('faseError').classList.add('hidden');

    switch (fase) {
        case 'confirmacion':
            document.getElementById('faseConfirmacion').classList.remove('hidden');
            break;
        case 'buscando':
            document.getElementById('faseBuscando').classList.remove('hidden');
            break;
        case 'resultados':
            document.getElementById('faseResultados').classList.remove('hidden');
            break;
        case 'error':
            document.getElementById('faseError').classList.remove('hidden');
            break;
    }
}

// FASE 2: Usuario confirma, cambiar a loading y ejecutar búsqueda
function confirmarYBuscar() {
    // Cambiar header
    document.getElementById('modalHeaderIcon').className = 'ph ph-spinner animate-spin text-xl';
    document.getElementById('modalHeaderTitle').textContent = 'Buscando...';

    // Mostrar fase de loading
    mostrarFase('buscando');

    // Ejecutar búsqueda - Asumimos URL relativa o base definida
    // Usamos el path relativo /clientes/consultar-dni que funcionará en el contexto de la app
    fetch('/clientes/consultar-dni', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ dni: currentDniRuc })
    })
        .then(response => response.json())
        .then(result => {
            if (result.success && result.data) {
                consultaData = result.data;

                // FASE 3: Mostrar resultados
                document.getElementById('modalHeaderIcon').className = 'ph-bold ph-check-circle text-xl';
                document.getElementById('modalHeaderTitle').textContent = 'Datos Encontrados';

                document.getElementById('resultadoNombre').textContent = result.data.nombre_completo;

                // Mostrar dirección solo si existe (RUC)
                if (result.data.direccion) {
                    document.getElementById('resultadoDireccionContainer').classList.remove('hidden');
                    document.getElementById('resultadoDireccion').textContent = result.data.direccion;
                } else {
                    document.getElementById('resultadoDireccionContainer').classList.add('hidden');
                }

                mostrarFase('resultados');
            } else {
                // FASE 4: Mostrar error
                document.getElementById('modalHeaderIcon').className = 'ph-bold ph-warning-circle text-xl';
                document.getElementById('modalHeaderTitle').textContent = 'Error';

                document.getElementById('errorMsg').textContent = result.message || 'No se encontró información para este documento';
                mostrarFase('error');
            }
        })
        .catch(error => {
            console.error('Error:', error);

            // FASE 4: Mostrar error de conexión
            document.getElementById('modalHeaderIcon').className = 'ph-bold ph-warning-circle text-xl';
            document.getElementById('modalHeaderTitle').textContent = 'Error';

            document.getElementById('errorMsg').textContent = 'Error de conexión. Intenta nuevamente.';
            mostrarFase('error');
        });
}

function cerrarModalConfirmacion() {
    const modal = document.getElementById('modalConfirmacionBusqueda');
    modal.classList.add('hidden');

    // Reset
    currentLeadId = null;
    currentDniRuc = null;
    currentNombreActual = null;
    currentDireccionActual = null;
    consultaData = null;
}

// Cerrar modal con ESC
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('modalConfirmacionBusqueda');
        if (modal && !modal.classList.contains('hidden')) {
            cerrarModalConfirmacion();
        }
    }
});

// Cerrar modal al hacer clic fuera
document.getElementById('modalConfirmacionBusqueda')?.addEventListener('click', function (e) {
    if (e.target === this) {
        cerrarModalConfirmacion();
    }
});

function actualizarLead() {
    if (!currentLeadId || !consultaData) return;

    // Mostrar loading en el botón
    const btnActualizar = event.target;
    // Guardar contenido original si no es un spinner
    const btnText = btnActualizar.innerHTML.includes('ph-spinner') ? 'Actualizar' : btnActualizar.innerHTML;

    btnActualizar.disabled = true;
    btnActualizar.innerHTML = '<i class="ph ph-spinner animate-spin"></i> Actualizando...';

    // Enviar datos al servidor
    fetch('/leads/update-from-query', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: currentLeadId,
            nombre: consultaData.nombre_completo,
            direccion: consultaData.direccion || null
        })
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(result => {
            btnActualizar.disabled = false;
            btnActualizar.innerHTML = btnText;

            if (result.success) {
                // Mostrar notificación de éxito
                mostrarNotificacion('Éxito', 'Datos actualizados correctamente', 'success');

                // Actualizar la tarjeta en la interfaz
                actualizarTarjetaLead(currentLeadId, consultaData.nombre_completo);

                // Cerrar modal después de todo
                setTimeout(() => {
                    cerrarModalConfirmacion();
                }, 300);
            } else {
                mostrarNotificacion('Error', result.message || 'No se pudo actualizar', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btnActualizar.disabled = false;
            btnActualizar.innerHTML = btnText;
            mostrarNotificacion('Error', 'Error de conexión', 'error');
        });
}

function actualizarTarjetaLead(leadId, nuevoNombre) {
    // Buscar la tarjeta del lead
    const cards = document.querySelectorAll('.group.relative');

    cards.forEach(card => {
        // Buscar el botón de aprobar que tiene el leadId
        // Opción robusta: buscar atributos
        const approveBtn = card.querySelector(`button[onclick*="handleApprove(${leadId}"]`);

        if (approveBtn) {
            // Encontramos la tarjeta correcta
            const nombreElement = card.querySelector('h3.font-black.text-slate-900');

            if (nombreElement) {
                // Animar el cambio
                card.style.transition = 'all 0.3s ease';
                card.style.transform = 'scale(1.05)';
                card.style.boxShadow = '0 10px 40px rgba(59, 130, 246, 0.3)';

                setTimeout(() => {
                    // Actualizar el nombre
                    nombreElement.textContent = nuevoNombre;
                    nombreElement.setAttribute('title', nuevoNombre);

                    // Restaurar el estilo
                    setTimeout(() => {
                        card.style.transform = 'scale(1)';
                        card.style.boxShadow = '';
                    }, 300);
                }, 150);
            }
        }
    });
}
