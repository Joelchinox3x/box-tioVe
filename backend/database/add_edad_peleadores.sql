-- Agregar campo edad y permitir fecha_nacimiento NULL
ALTER TABLE peleadores
  MODIFY COLUMN fecha_nacimiento DATE NULL,
  ADD COLUMN edad INT NULL AFTER fecha_nacimiento;
