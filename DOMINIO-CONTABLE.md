# Dominio Contable — Sistema de Gestión Constructora

> **País:** Ecuador
> **Normativa:** NIIF para PYMES + Normativa SRI
> **Moneda:** USD
> **IVA:** 15%
> **Retenciones:** Fuente según tabla SRI, Retención IVA 30% / 70% / 100%
> **Versión:** 1.0 — [FECHA]
> **Estado:** BORRADOR — Requiere validación contable antes de Fase 1

---

## 1. Plan de Cuentas

Estructura jerárquica de 4 niveles: `Grupo.Subcuenta.Detalle.Movimiento`

### 1.1 Grupos Principales

| Código | Grupo | Naturaleza | Descripción |
|--------|-------|-----------|-------------|
| 1 | **ACTIVOS** | Deudor | Recursos controlados por la empresa |
| 2 | **PASIVOS** | Acreedor | Obligaciones con terceros |
| 3 | **PATRIMONIO** | Acreedor | Derechos de los propietarios |
| 4 | **INGRESOS** | Acreedor | Incrementos en beneficios económicos |
| 5 | **GASTOS Y COSTOS** | Deudor | Disminuciones en beneficios económicos |

### 1.2 Plan de Cuentas Detallado para Constructora

#### GRUPO 1 — ACTIVOS

| Código | Cuenta | Tipo | Descripción |
|--------|--------|------|-------------|
| 1.1.1.01 | Caja General | Activo Corriente | Efectivo disponible en oficina |
| 1.1.1.02 | Banco — Cuenta Operativa | Activo Corriente | Cuenta corriente principal |
| 1.1.1.03 | Banco — Cuenta Obra | Activo Corriente | Fondos destinados por obra |
| 1.1.2.01 | Cuentas por Cobrar — Clientes | Activo Corriente | Facturación pendiente de cobro |
| 1.1.2.02 | Anticipos al Personal | Activo Corriente | Anticipos de sueldo a trabajadores |
| 1.1.2.03 | Anticipos de Impuestos | Activo Corriente | Retenciones pagadas por anticipado |
| 1.1.2.04 | IVA Pagado (Crédito Tributario) | Activo Corriente | IVC pagado en compras (recuperable) |
| 1.1.3.01 | Inventarios — Materiales en Obra | Activo Corriente | Materiales comprados sin consumir |
| 1.1.3.02 | Inventarios — Herramientas Menores | Activo Corriente | Herramientas de bajo costo unitario |
| 1.2.1.01 | Maquinaria y Equipos | Activo No Corriente | Equipos de construcción (depreciables) |
| 1.2.1.02 | Vehículos | Activo No Corriente | Unidades de transporte |
| 1.2.1.03 | Mobiliario y Equipo de Oficina | Activo No Corriente | Equipos administrativos |
| 1.2.2.01 | Depreciación Acum. Maquinaria | Activo No Corriente | Depreciación acumulada maquinaria |
| 1.2.2.02 | Depreciación Acum. Vehículos | Activo No Corriente | Depreciación acumulada vehículos |
| 1.2.2.03 | Depreciación Acum. Mobiliario | Activo No Corriente | Depreciación acumulada oficina |

#### GRUPO 2 — PASIVOS

| Código | Cuenta | Tipo | Descripción |
|--------|--------|------|-------------|
| 2.1.1.01 | Cuentas por Pagar — Proveedores | Pasivo Corriente | Compras a crédito pendientes |
| 2.1.1.02 | Cuentas por Pagar — Subcontratistas | Pasivo Corriente | Servicios subcontratados pendientes |
| 2.1.1.03 | Cuentas por Pagar — Personal | Pasivo Corriente | Sueldos y beneficios pendientes |
| 2.1.1.04 | Retenciones en la Fuente por Pagar | Pasivo Corriente | Retenciones retenidas a proveedores |
| 2.1.1.05 | Retención IVA por Pagar | Pasivo Corriente | Retención IVA retenida |
| 2.1.1.06 | IVA por Pagar (Débito Tributario) | Pasivo Corriente | IVA cobrado en ventas |
| 2.1.2.01 | Obligaciones Laborales CP | Pasivo Corriente | Decimotercer sueldo, vacaciones, aporte patronal |
| 2.1.2.02 | Deudas Bancarias CP | Pasivo Corriente | Préstamos a corto plazo |
| 2.2.1.01 | Deudas Bancarias LP | Pasivo No Corriente | Préstamos a largo plazo |

#### GRUPO 3 — PATRIMONIO

| Código | Cuenta | Tipo | Descripción |
|--------|--------|------|-------------|
| 3.1.1.01 | Capital Social | Patrimonio | Aportes de los socios |
| 3.1.1.02 | Reservas | Patrimonio | Reservas legales y facultativas |
| 3.1.1.03 | Resultados Acumulados | Patrimonio | Utilidades o pérdidas de ejercicios anteriores |
| 3.1.1.04 | Resultado del Ejercicio | Patrimonio | Utilidad o pérdida del período actual |

#### GRUPO 4 — INGRESOS

| Código | Cuenta | Tipo | Descripción |
|--------|--------|------|-------------|
| 4.1.1.01 | Ingresos por Contratos de Obra | Ingreso | Avances de obra facturados |
| 4.1.1.02 | Ingresos por Presupuestos | Ingreso | Trabajos por presupuesto cerrado |
| 4.1.1.03 | Ingresos por Adicionales | Ingreso | Ampliaciones de contrato |
| 4.2.1.01 | Otros Ingresos Operativos | Ingreso | Alquiler de equipos, multas cobradas |
| 4.3.1.01 | Ingresos Financieros | Ingreso | Intereses ganados, descuentos obtenidos |

#### GRUPO 5 — GASTOS Y COSTOS

| Código | Cuenta | Tipo | Descripción |
|--------|--------|------|-------------|
| 5.1.1.01 | Costo Materiales directos | Costo | Materiales consumidos en obra |
| 5.1.1.02 | Costo Mano de Obra directa | Costo | Salarios de personal de obra |
| 5.1.1.03 | Costo Subcontrataciones | Costo | Servicios subcontratados en obra |
| 5.1.1.04 | Costo Maquinaria y Equipos | Costo | Depreciación y operación de maquinaria |
| 5.1.1.05 | Costos Indirectos de Obra | Costo | Materiales, servicios y gastos indirectos |
| 5.2.1.01 | Gastos de Administración | Gasto | Sueldos oficina, alquiler, servicios básicos |
| 5.2.1.02 | Gastos de Ventas | Gasto | Comisiones, publicidad, cotizaciones |
| 5.2.1.03 | Gastos Financieros | Gasto | Intereses bancarios, comisiones |
| 5.2.1.04 | Depreciación Gastos | Gasto | Depreciación del período |
| 5.3.1.01 | Costo AIU — Administración | Costo | Porción de AIU asignada |
| 5.3.1.02 | Costo AIU — Imprevistos | Costo | Porción de AIU asignada |
| 5.3.1.03 | Costo AIU — Utilidad | Costo | Porción de AIU (ganancia) |

---

## 2. Diccionario de Entidades (Modelos)

### 2.1 Obra (Centro de Costo)

```
Obra
├── id                    BIGINT PK
├── codigo                VARCHAR(20) UNIQUE     — "OBR-001"
├── nombre                VARCHAR(255)           — "Edificio Residencial Los Pinos"
├── cliente_id            FK → Cliente
├── direccion             TEXT
├── fecha_inicio          DATE
├── fecha_fin_estimada    DATE
├── fecha_fin_real        DATE NULLABLE
├── estado                ENUM (planificada, en_curso, suspendida, culminada, cancelada)
├── contrato_monto        DECIMAL(14,2)          — monto total del contrato
├── anticipo_porcentaje   DECIMAL(5,2) DEFAULT 0 — % de anticipo permitido (ej: 30.00)
├── aiu_administracion    DECIMAL(5,2) DEFAULT 10.00
├── aiu_imprevistos       DECIMAL(5,2) DEFAULT 5.00
├── aiu_utilidad          DECIMAL(5,2) DEFAULT 10.00
├── costo_fijo_mensual    DECIMAL(14,2) DEFAULT 0 — para punto de equilibrio
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP
```

**Reglas de negocio:**
- `aiu_administracion + aiu_imprevistos + aiu_utilidad` debe sumar exactamente el % total de AIU configurado
- `anticipo_porcentaje` determina el monto máximo de anticipo facturable
- `costo_fijo_mensual` se prorratea entre obras activas para punto de equilibrio

### 2.2 Cliente

```
Cliente
├── id                    BIGINT PK
├── razon_social          VARCHAR(255)
├── ruc                   VARCHAR(13) UNIQUE     — RUC ecuatoriano
├── tipo                  ENUM (publico, privado)
├── email                 VARCHAR(255)
├── telefono              VARCHAR(20)
├── direccion             TEXT
├── representa_legal      VARCHAR(255)
├──.created_at            TIMESTAMP
└── updated_at            TIMESTAMP
```

### 2.3 Presupuesto (APU)

```
Presupuesto
├── id                    BIGINT PK
├── obra_id               FK → Obra
├── codigo                VARCHAR(30) UNIQUE     — "PRES-OBR001-001"
├── descripcion           VARCHAR(500)
├── unidad_medida         VARCHAR(20)            — "m2", "ml", "kg", "un"
├── cantidad              DECIMAL(14,4)
├── costo_unitario        DECIMAL(14,6)          — APU completo
├── precio_venta_unitario DECIMAL(14,6)          — precio al cliente
├── subtotal_costo        DECIMAL(14,2)          — cantidad × costo_unitario
├── subtotal_venta        DECIMAL(14,2)          — cantidad × precio_venta_unitario
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP
```

**APU (Análisis de Precio Unitario) — Estructura:**

```
Presupuesto → tiene_many → DetalleAPU
```

```
DetalleAPU
├── id                    BIGINT PK
├── presupuesto_id        FK → Presupuesto
├── tipo                  ENUM (material, mano_obra, subcontrato, equipo)
├── descripcion           VARCHAR(255)           — "Cemento gris 50kg"
├── unidad_medida         VARCHAR(20)            — "kg", "hr", "glb"
├── cantidad              DECIMAL(14,4)
├── costo_unitario        DECIMAL(14,6)          — costo base sin AIU
├── costo_total           DECIMAL(14,2)          — cantidad × costo_unitario
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP
```

**Fórmula APU:**
```
Costo Directo = Σ(detalle_apu.costo_total)
Costo con AIU = Costo Directo × (1 + AIU%)

Donde AIU% = (Administración + Imprevistos + Utilidad) / 100

Precio Unitario de Venta = Costo con AIU
```

### 2.4 Registro Contable (Asiento)

```
AsientoContable
├── id                    BIGINT PK
├── numero_asiento        VARCHAR(30) UNIQUE     — "ASI-2026-0001"
├── fecha                 DATE
├── descripcion           TEXT
├── obra_id               FK → Obra NULLABLE     — NULL = asiento global
├── tipo                  ENUM (manual, automatico, cierre, apertura)
├── estado                ENUM (borrador, aprobado, anulado)
├── usuario_creacion      FK → User
├── usuario_aprobacion    FK → User NULLABLE
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP
```

```
DetalleAsiento
├── id                    BIGINT PK
├── asiento_id            FK → AsientoContable
├── cuenta_id             FK → PlanCuentas
├── debe                  DECIMAL(14,2) DEFAULT 0
├── haber                 DECIMAL(14,2) DEFAULT 0
├── referencia            VARCHAR(255)           — "Anticipo cliente OBR-001"
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP
```

**CONSTRAINT DB (partida doble):**
```sql
-- En cada asiento contable, la suma de débitos DEBE ser igual a la suma de créditos
-- Esto se valida ANTES de insertar, no con trigger
-- Si debe ≠ haber → RECHAZAR la transacción con error explícito
```

### 2.5 Plan de Cuentas (Catálogo)

```
PlanCuentas
├── id                    BIGINT PK
├── codigo                VARCHAR(30) UNIQUE     — "1.1.1.01"
├── nombre                VARCHAR(255)           — "Caja General"
├── grupo                 ENUM (activo, pasivo, patrimonio, ingreso, gasto)
├── tipo                  ENUM (deudor, acreedor)
├── es_auxiliar           BOOLEAN DEFAULT false  — true = acepta movimientos
├── padre_id              FK → PlanCuentas NULLABLE — jerarquía
├── activa                BOOLEAN DEFAULT true
├──.created_at            TIMESTAMP
└── updated_at            TIMESTAMP
```

**Regla:** Solo las cuentas auxiliares (`es_auxiliar = true`) aceptan débitos y créditos. Las cuentas padre son de concentración y solo reflejan saldos de sus hijas.

### 2.6 Flujo de Caja

```
FlujoCaja
├── id                    BIGINT PK
├── obra_id               FK → Obra
├── fecha                 DATE
├── tipo                  ENUM (ingreso, egreso)
├── categoria             ENUM (
│                           anticipo_cliente,
│                           pago_cliente,
│                           compra_material,
│                           pago_mano_obra,
│                           pago_subcontrato,
│                           pago_equipo,
│                           gasto_administrativo,
│                           otro
│                         )
├── monto                 DECIMAL(14,2)
├── referencia            VARCHAR(255)           — número de factura, comprobante
├── asiento_id            FK → AsientoContable NULLABLE — enlace contable
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP
```

**Resultado Neto por Obra (vista/virtual):**
```
Resultado = SUM(ingresos) - SUM(egresos)
         = SUM(tipo = 'ingreso') - SUM(tipo = 'egreso')
```

### 2.7 Anticipo y Amortización

```
AnticipoCliente
├── id                    BIGINT PK
├── obra_id               FK → Obra
├── monto_total           DECIMAL(14,2)          — monto total del anticipo
├── porcentaje            DECIMAL(5,2)           — % sobre contrato
├── estado                ENUM (pendiente, parcial, amortizado, cancelado)
├── fecha_concesion       DATE
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP
```

```
AmortizacionAnticipo
├── id                    BIGINT PK
├── anticipo_id           FK → AnticipoCliente
├── numero_amortizacion   INTEGER                — 1, 2, 3...
├── porcentaje_amortizar  DECIMAL(5,2)           — % de amortización en esta cuota
├── monto_amortizado      DECIMAL(14,2)          — monto amortizado en esta cuota
├── avance_porcentaje     DECIMAL(5,2)           — % de avance de obra al momento
├── fecha_amortizacion    DATE
├── asiento_id            FK → AsientoContable   — asiento contable generado
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP
```

**Fórmula de Amortización Automática:**
```
Monto Amortización = Anticipo Total × (% Avance de Obra actual)
Monto por Amortizar = Monto Total Anticipo - Σ(amortizaciones anteriores)

Si el avance es del 60% y el anticipo fue del 30%:
Amortización = 30% × 60% = 18% del contrato se amortiza
```

**Asiento contable de amortización:**
```
DEBE:  4.1.1.01  Ingresos por Contratos de Obra    $X
HABER: 1.1.2.0X  Cuentas por Cobrar — Anticipo     $X
```

### 2.8 Cuentas por Cobrar

```
CuentaPorCobrar
├── id                    BIGINT PK
├── obra_id               FK → Obra
├── cliente_id            FK → Cliente
├── tipo                  ENUM (factura, nota_venta, anticipos)
├── numero_comprobante    VARCHAR(30)
├── fecha_emision         DATE
├── fecha_vencimiento     DATE
├── monto_total           DECIMAL(14,2)
├── monto_cobrado         DECIMAL(14,2) DEFAULT 0
├── saldo                 DECIMAL(14,2) GENERATED ALWAYS AS (monto_total - monto_cobrado)
├── estado                ENUM (pendiente, parcial, cobrada, vencida, mora)
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP
```

### 2.9 Cuentas por Pagar

```
CuentaPorPagar
├── id                    BIGINT PK
├── obra_id               FK → Obra
├── proveedor_id          FK → Proveedor
├── tipo                  ENUM (factura_compra, liquidacion_subcontrato, planilla_mano_obra)
├── numero_comprobante    VARCHAR(30)
├── fecha_emision         DATE
├── fecha_vencimiento     DATE
├── monto_total           DECIMAL(14,2)
├── monto_pagado          DECIMAL(14,2) DEFAULT 0
├── saldo                 DECIMAL(14,2) GENERATED ALWAYS AS (monto_total - monto_pagado)
├── estado                ENUM (pendiente, parcial, pagada, vencida)
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP
```

### 2.10 Personal por Obra (Control de Horas)

```
AsistenciaObra
├── id                    BIGINT PK
├── obra_id               FK → Obra
├── trabajador_id         FK → Trabajador
├── fecha                 DATE
├── horas_trabajadas      DECIMAL(5,2)           — ej: 8.50
├── hora_entrada          TIME
├── hora_salida           TIME
├── tipo_jornada          ENUM (normal, extraordinaria, dominical_feriado)
├── observaciones         TEXT
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP
```

```
Trabajador
├── id                    BIGINT PK
├── cedula                VARCHAR(10) UNIQUE     — cédula ecuatoriana
├── nombres               VARCHAR(255)
├── apellidos             VARCHAR(255)
├── cargo                 VARCHAR(100)
├── sueldo_base           DECIMAL(10,2)
├── tipo_contrato         ENUM (indefinido, obra_determinada, servicio)
├── activo                BOOLEAN DEFAULT true
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP
```

---

## 3. Reglas de Negocio Contable

### 3.1 Partida Doble — Constraint en DB

**Regla ABSOLUTA:** En cualquier asiento contable, `SUM(debe) == SUM(haber)`.

```sql
-- Validación se hace en el service layer ANTES del insert
-- No se usa trigger porque Laravel maneja la transacción
-- Si la validación falla → RuntimeException, no se persiste nada
```

**Flujo de validación:**
1. Recibir líneas del asiento
2. Calcular `total_debe = SUM(debe)` y `total_haber = SUM(haber)`
3. Si `total_debe != total_haber` → lanzar `PartidaDobleException`
4. Si alguna línea tiene `debe > 0 AND haber > 0` → lanzar `LineaInvalidaException`
5. Si alguna línea tiene `debe == 0 AND haber == 0` → lanzar `LineaVaciaException`
6. Recién ahí ejecutar `DB::transaction(fn() => ...)`

### 3.2 Flujo de Caja por Obra

**Ingresos (crédito a la obra):**
- Anticipo de cliente al iniciar
- Avances de obra facturados (planillas de avance)
- Trabajos adicionales facturados

**Egresos (débito a la obra):**
- Materiales comprados para la obra
- Mano de obra directa (sueldos + aportes)
- Subcontrataciones
- Depreciación de maquinaria asignada
- Gastos indirectos asignados

**Resultado Neto:**
```
Resultado Neto = Total Ingresos - Total Egresos
Si positivo → Obra con ganancia
Si negativo → Obra con pérdida
```

### 3.3 Presupuestación con AIU

**Parámetros configurables por obra:**
- Administración: 10% (default)
- Imprevistos: 5% (default)
- Utilidad: 10% (default)

**Cálculo automático:**
```
Costo Directo = Σ(detalles APU)
AIU = Costo Directo × (Administración% + Imprevistos% + Utilidad%) / 100
Precio de Venta = Costo Directo + AIU
```

**Ejemplo real:**
```
Costo Directo: $100,000.00
Administración (10%): $10,000.00
Imprevistos (5%): $5,000.00
Utilidad (10%): $10,000.00
Total AIU: $25,000.00
Precio de Venta: $125,000.00
```

### 3.4 Anticipos y Amortizaciones

**Escenario típico constructora Ecuador:**
- Cliente paga 30% de anticipo al firmar contrato
- Anticipo se amortiza progresivamente según avance de obra
- Cada vez que se factura un avance, se descuenta el porcentaje de anticipo correspondiente

**Ejemplo:**
```
Contrato: $500,000
Anticipo (30%): $150,000

Cuando avance = 20%:
  Amortización = $150,000 × (20% / 100%) × factores = $30,000
  Se factura al cliente: $100,000 - $30,000 = $70,000

Cuando avance = 50%:
  Amortización acumulada = $150,000 × 50% = $75,000
  Nueva amortización = $75,000 - $30,000 (ya amortizado) = $45,000
  Se factura: $250,000 - $75,000 = $175,000
```

**Asientos contables de anticipo:**
```
1. Al recibir anticipo:
   DEBE:  1.1.1.02  Banco Cuenta Operativa    $150,000
   HABER: 2.1.1.01  Cuentas por Cobrar          $150,000

2. Al amortizar anticipo:
   DEBE:  4.1.1.01  Ingresos por Contratos      $30,000
   HABER: 1.1.2.0X  Anticipo Cliente            $30,000
```

### 3.5 Contabilidad por Centros de Costo

**Cada obra es un centro de costo independiente.**

**Reglas:**
- Todo movimiento contable puede (y debe) etiquetarse con `obra_id`
- Los asientos administrativos (gastos de oficina, etc.) van con `obra_id = NULL` o se prorratean
- Los reportes financieros se pueden filtrar por obra
- El libro diario muestra el centro de costo en cada línea

**Prorrateo de costos fijos:**
```
Costo Fijo Mensual Total = $20,000
Obras activas = 4
Costo fijo por obra = $20,000 / 4 = $5,000

Si una obra tiene más actividad, se puede configurar un peso:
Obra A: peso 40% → $8,000
Obra B: peso 30% → $6,000
Obra C: peso 20% → $4,000
Obra D: peso 10% → $2,000
```

### 3.6 Punto de Equilibrio por Obra

**Fórmula:**
```
Punto de Equilibrio (unidades) = Costos Fijos / (Precio Unitario - Costo Variable Unitario)
Punto de Equilibrio (USD) = Costos Fijos / Margen de Contribución %

Donde:
- Costos Fijos = Gastos administrativos fijos prorrateados + depreciación + servicios fijos
- Costo Variable Unitario = materiales + mano de obra directa + subcontrato por unidad
- Precio Unitario = precio de venta por unidad
- Margen de Contribución = (Precio - Costo Variable) / Precio
```

**Ejemplo:**
```
Costos Fijos Mensuales Obra: $15,000
Costo Variable por m2: $85.00
Precio Venta por m2: $125.00
Margen Contribución: ($125 - $85) / $125 = 32%

Punto Equilibrio (m2) = $15,000 / ($125 - $85) = 375 m2
Punto Equilibrio (USD) = $15,000 / 0.32 = $46,875
```

### 3.7 Cuentas por Cobrar y Pagar — Alertas de Vencimiento

**Configuración de alertas:**
```
Alerta a 7 días antes  → Notificación informativa
Alerta a 3 días antes  → Notificación de recordatorio
Alerta a 1 día antes   → Notificación urgente
Día de vencimiento     → Alerta crítica
```

**Cálculo automático:**
```
dias_para_vencer = fecha_vencimiento - fecha_actual
Si dias_para_vencer <= 7 → mostrar alerta
Si dias_para_vencer <= 0 → estado = 'vencida' (cambiar automáticamente)
```

### 3.8 Análisis de Desviación Presupuesto vs Ejecutado

**Fórmula:**
```
Desviación = (Ejecutado - Presupuestado) / Presupuestado × 100%

Si desviación > 0 → Sobrecosto (negativo para la obra)
Si desviación < 0 → Subconsumo (puede ser bueno o malo)
Si desviación = 0 → Exacto dentro del presupuesto
```

**Ejemplo:**
```
Presupuestado: $50,000
Ejecutado: $55,000
Desviación: ($55,000 - $50,000) / $50,000 × 100% = +10% (Sobrecosto)
```

### 3.9 Estados Financieros Exportables

**Balance General:**
- Agrupar cuentas por: Activo Corriente, Activo No Corriente, Pasivo Corriente, Pasivo No Corriente, Patrimonio
- Fórmula de balance: `Activos = Pasivos + Patrimonio`

**Estado de Resultados:**
- Ingresos - Costos - Gastos = Utilidad/Pérdida del ejercicio
- Porcentajes verticales respecto a ingresos

**Libro Diario:**
- Listado cronológico de todos los asientos aprobados
- Filtro por rango de fechas y por obra
- Muestra: fecha, número asiento, descripción, cuenta, debe, haber

**Libro Mayor:**
- Movimientos agrupados por cuenta
- Saldo inicial + débitos - créditos = saldo final
- Filtro por cuenta y rango de fechas

### 3.10 Retenciones SRI

**Retención en la Fuente (aplicada a proveedores):**
- Servicios profesionales: 2.75%
- Compra de bienes: 1.75%
- Compra de bienes de树第三国: 2.75%
- Consultoría: 2.75%

**Retención IVA:**
- Contribuyente especial: 30% (bienes) / 70% (servicios)
- Régimen RIMPE: 100%
- No obligado: 0%

**Asiento de retención:**
```
Al pagar factura de proveedor con retención:
DEBE:  5.1.1.01  Costo Materiales          $100.00
DEBE:  1.1.2.04  IVA Pagado (Crédito Trib)  $15.00
HABER: 2.1.1.01  Cuentas por Pagar          $100.00
HABER: 2.1.1.04  Retenciones en la Fuente   $1.75
HABER: 2.1.1.05  Retención IVA por Pagar     $10.50
HABER: 1.1.1.02  Banco Cuenta Operativa      $102.75
```

---

## 4. Estados del Sistema

### 4.1 Estado de Obra
```
planificada → en_curso → culminada
              ↓
           suspendida → en_curse (reanudar)
              ↓
           cancelada (solo desde suspendida o planificada)
```

### 4.2 Estado de Asiento Contable
```
borrador → aprobado → (no se puede modificar)
            ↓
         anulado (genera asiento de reversión)
```

### 4.3 Estado de Cuenta por Cobrar
```
pendiente → parcial → cobrada
    ↓
  vencida → mora (automático por scheduler)
```

### 4.4 Estado de Anticipo
```
pendiente → parcial → amortizado
    ↓
  cancelado (si obra cancelada)
```

---

## 5. Operaciones Contables Automáticas

El sistema debe generar asientos automáticamente para:

| Evento | Asiento Generado |
|--------|------------------|
| Recepción anticipo cliente | DEBE: Banco, HABER: Cuentas por Cobrar |
| Amortización anticipo | DEBE: Ingresos por Contratos, HABER: Anticipo Cliente |
| Facturación avance de obra | DEBE: Cuentas por Cobrar, HABER: Ingresos por Contratos |
| Pago proveedor con retención | DEBE: Proveedor + IVA Pagado, HABER: Banco + Retenciones |
| Pago mano de obra | DEBE: Costo Mano de Obra + Aportes, HABER: Banco |
| Asignación costo fijo mensual | DEBE: Costo Obra X, HABER: Gastos Administración |
| Cierre mensual | DEBE/HABER: Resultado del Ejercicio, HABER/DEBE: Cuentas de Resultado |
| Depreciación mensual | DEBE: Depreciación Gasto, HABER: Depreciación Acumulada |

---

## 6. Validación del Documento

Antes de pasar a Fase 1, validar:

- [ ] ¿El plan de cuentas cubre todas las operaciones de la constructora?
- [ ] ¿Los campos de cada entidad son suficientes para los reportes requeridos?
- [ ] ¿Las fórmulas de AIU, amortización y punto de equilibrio son correctas?
- [ ] ¿Los asientos automáticos son contablemente correctos?
- [ ] ¿Las retenciones SRI están bien parametrizadas?
- [ ] ¿Falta alguna entidad o regla de negocio?
- [ ] ¿Los estados financieros (Balance, Estado de Resultados, Libro Diario/Mayor) son suficientes?

---

## 7. Próximos Pasos

Una vez validado este documento:
1. **Fase 1:** Setup Laravel 12 + Filament 5 + PostgreSQL + brick/money + Spatie Permission
2. **Fase 2:** Migraciones + Modelos Eloquent + Tests unitarios de reglas contables
