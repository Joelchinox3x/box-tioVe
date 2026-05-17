-- Agregar campos de check-in/asistencia a inscripciones_eventos
ALTER TABLE inscripciones_eventos
  ADD COLUMN fecha_checkin TIMESTAMP NULL COMMENT 'Fecha/hora de entrada al evento',
  ADD COLUMN staff_checkin_id INT NULL COMMENT 'ID del staff que registró la entrada',
  ADD INDEX idx_checkin (fecha_checkin);
