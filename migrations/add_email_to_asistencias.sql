-- Agregar campo email a tabla asistencias
-- Este campo almacena el email del asistente

ALTER TABLE asistencias
ADD COLUMN email VARCHAR(255) DEFAULT NULL AFTER nombre_completo;

-- Índice para búsquedas por email
CREATE INDEX idx_email ON asistencias(email);

-- Verificar la estructura actualizada
DESCRIBE asistencias;
