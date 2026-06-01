# Sesion InfoShare - Resumen de cambios

Fecha: 2026-06-01

## Base del sistema

- Se creo arquitectura MVC base en PHP con rutas centralizadas.
- Se separo en `controllers`, `models`, `views`, `helpers`, `middleware`, `routes`.
- Se implemento `Router` propio y bootstrap en `bootstrap/app.php`.

## Autenticacion y seguridad

- Login/logout con sesiones y CSRF.
- Middleware de autenticacion y roles.
- Control de intentos de login (bloqueo temporal por intentos fallidos).
- Errores amigables 403/404/405/500.

## Tickets

- CRUD operativo MVP de tickets.
- Reglas aplicadas:
  - asignacion automatica al creador.
  - SLA automatico desde SQL/trigger (5 dias).
  - soporte de datos parciales en creacion.
- Filtros avanzados + paginacion en listado de tickets.
- Busqueda por id, ticket_number, email, telefono, problema, estado_info.
- Columna de fecha de creacion agregada antes de vencimiento.

## Detalle de ticket

- Rediseno priorizando informacion basica.
- Comentarios como historial tipo timeline.
- Formulario de comentario funcional.
- Edicion de campos del ticket desde modal.
- Actualizacion de estado, tags, asignacion y soft delete.

## Tags y estado info

- Tags limitados a `fail` y `question` en crear y detalle.
- Tag `fail` por defecto en creacion.
- `estado_info` en seleccionable.
- Soporte para crear nuevo `estado_info` y guardarlo para uso futuro.

## Reportes

- Reporte de pendientes normal + agrupado por dia con boton copiar SMS.
- Reporte de metricas con boton copiar SMS.
- Reporte SLA operativo.

## Metricas

- Registro manual diario por usuario.
- Registro automatico desde comentarios de progreso con modos:
  - inbound_calls
  - outbound_calls
  - chats
  - emails
- Ajuste SMS de metricas:
  - Tickets = cerrados + respondidos del dia
  - Calls failed to connect = 0
  - Issues resolved over the first call = 0
  - Tickets needing HQ help or attention = 0

## SQL generados

- `bds/sql_mejoras_manual_v2.txt`
- `bds/sql_flex_tickets_v3.txt`
- `bds/sql_sla_5dias_v4.txt`
- `bds/sql_metricas_control_v5.txt`

## Nota operativa

- Para activar cambios SQL, ejecutar manualmente los archivos en phpMyAdmin/MySQL.
- Se recomienda ejecutar primero backup antes de aplicar scripts.
