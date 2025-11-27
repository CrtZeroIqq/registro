-- Agregar campo evento a tabla asistencias
-- Este campo identifica a qué evento pertenece cada registro de asistencia

ALTER TABLE asistencias
ADD COLUMN evento VARCHAR(100) DEFAULT 'Nodo Bioceánico 2025' AFTER dia_evento;

-- Actualizar registros existentes con el evento por defecto
UPDATE asistencias
SET evento = 'Nodo Bioceánico 2025'
WHERE evento IS NULL OR evento = '';

-- Índice para mejorar búsquedas por evento
CREATE INDEX idx_evento ON asistencias(evento);

-- Verificar la estructura actualizada
DESCRIBE asistencias;
