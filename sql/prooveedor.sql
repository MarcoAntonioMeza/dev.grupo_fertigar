-- Índices para acelerar la subconsulta
CREATE INDEX idx_credito_tipo_status_proveedor_compra
  ON credito (tipo, status, proveedor_id, compra_id);

CREATE INDEX idx_compra_proveedor_id
  ON compra (proveedor_id);


ALTER TABLE
  proveedor
-- Agregar columna para el país del proveedor
  ADD COLUMN pais INT UNSIGNED DEFAULT 10;