-- Migration: Add 'inscrito' state to inscripciones_eventos.estado_pago
-- 3-state flow: inscrito → pendiente → pagado
-- inscrito: fighter registered for event but hasn't paid yet
-- pendiente: fighter submitted payment, waiting for admin confirmation
-- pagado: admin confirmed payment

ALTER TABLE inscripciones_eventos
MODIFY COLUMN estado_pago ENUM('inscrito', 'pendiente', 'pagado') DEFAULT 'inscrito';

-- Verify
DESCRIBE inscripciones_eventos;
