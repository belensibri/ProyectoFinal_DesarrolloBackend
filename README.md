# Sistema de Tickets IT y FAQ

## 1. Resumen del proyecto

Este proyecto es un sistema de gestión de tickets para soporte interno de TI, construido sobre Laravel 13 y Filament 5. Su objetivo es centralizar el registro, seguimiento, resolución y trazabilidad de incidencias técnicas dentro de una organización.

El sistema está orientado a un flujo operativo claro:

- Un usuario reporta un incidente.
- Un técnico toma el ticket y lo trabaja.
- La comunicación entre usuario y técnico ocurre dentro del ticket.
- Al cerrar el ticket, la solución se documenta en una base de conocimiento tipo FAQ.
- Todo el proceso queda registrado en un historial de auditoría.

La aplicación está implementada principalmente como un panel administrativo en Filament. Actualmente no expone una API REST pública activa.

## 2. Arquitectura

### Stack principal

- PHP `^8.3`
- Laravel `^13`
- Filament `^5.4`
- Laravel Sanctum
- Vite + Tailwind CSS 4

### Estructura utilizada

- `app/Models`
  Contiene las entidades principales del dominio: `Ticket`, `Comment`, `Attachment`, `FaqArticle`, `TicketHistory`, `User` y `Department`.

- `app/Policies`
  Centraliza la autorización por rol y por estado del ticket. Aquí viven las reglas de acceso reales del sistema.

- `app/Services`
  Contiene `TicketService`, que agrupa la lógica de negocio de tickets:
  - asignación
  - comentarios
  - cierre
  - reasignación
  - registro de historial
  - guardado de adjuntos

- `app/Filament/Resources`
  Implementa el panel operativo principal:
  - Tickets
  - FAQ
  - Usuarios
  - Departamentos

- `app/Http/Controllers` y `app/Http/Requests`
  Existen como base para una posible interfaz HTTP/API futura, pero el flujo productivo actual está en Filament.

### Separación de responsabilidades

- Las **Policies** deciden quién puede ejecutar una acción.
- El **Service** valida reglas de negocio y realiza cambios transaccionales.
- Los **Models** definen relaciones y helpers del dominio.
- **Filament** actúa como capa de interfaz y orquestación del panel.

Esto evita mezclar autorización, validación y persistencia directamente en la UI.

## 3. Roles y permisos

Los roles se almacenan en `users.tipo_usuario`:

- `USUARIO`
- `TECNICO`
- `ADMINISTRADOR`

### Usuario

Puede:

- Crear tickets.
- Ver únicamente sus propios tickets cuando ya fueron tomados por un técnico.
- Editar sus tickets solo mientras siguen en estado `activo` y aún no tienen técnico asignado.
- Agregar comentarios de tipo `usuario` cuando el ticket está en `en_proceso`.
- Adjuntar imágenes al comentar.
- Consultar la base de conocimiento FAQ.

No puede:

- Tomar tickets.
- Cerrar tickets.
- Reasignar tickets.
- Crear, editar o eliminar usuarios.
- Crear, editar o eliminar departamentos.
- Comentar tickets cerrados.

### Técnico

Puede:

- Ver tickets activos sin asignar y tickets ya asignados a él.
- Tomar tickets activos sin técnico asignado.
- Trabajar tickets asignados a él.
- Agregar comentarios de tipo `tecnico`.
- Adjuntar imágenes al comentar.
- Cerrar tickets solo si:
  - el ticket está en `en_proceso`
  - el ticket le pertenece
  - existe al menos un comentario técnico
- Consultar FAQ.

No puede:

- Crear tickets.
- Reasignar tickets.
- Gestionar usuarios.
- Gestionar departamentos.
- Cerrar tickets de otro técnico.
- Comentar tickets cerrados.

### Administrador

Puede:

- Ver todos los tickets.
- Editar tickets no cerrados.
- Crear, editar y eliminar usuarios.
- Crear, editar y eliminar departamentos.
- Ver FAQ.
- Reasignar tickets entre técnicos cuando están en `en_proceso`.

No puede:

- Tomar tickets como técnico.
- Comentar tickets.
- Cerrar tickets como técnico.
- Eliminar tickets.

## 4.Ciclo de vida del ticket

El sistema implementa tres estados:

- `activo`
- `en_proceso`
- `cerrado`

### 1. Creación

Quién lo ejecuta:

- `USUARIO`

Qué ocurre:

- Se crea el ticket con estado `activo`.
- Se registra un evento `ticket_creado` en el historial.

Restricciones:

- Solo usuarios pueden crear tickets.

### 2. Toma del ticket

Quién lo ejecuta:

- `TECNICO`

Qué ocurre:

- El técnico se asigna a sí mismo.
- `tecnico_id` se actualiza.
- El estado cambia de `activo` a `en_proceso`.
- Se registra `ticket_asignado` en historial.

Restricciones:

- El ticket debe estar en `activo`.
- No debe tener técnico asignado.

### 3. Trabajo del ticket

Quién interactúa:

- `TECNICO` asignado
- `USUARIO` propietario

Qué ocurre:

- Ambos pueden comentar mientras el ticket esté en `en_proceso`.
- El comentario del técnico funciona como bitácora técnica.
- El comentario del usuario funciona como respuesta funcional o evidencia adicional.

Restricciones:

- No se puede comentar si el ticket está `cerrado`.
- No se puede comentar si el ticket aún está `activo`.

### 4. Cierre

Quién lo ejecuta:

- Solo el `TECNICO` asignado

Qué ocurre:

- El ticket cambia a `cerrado`.
- Se llena `fecha_cierre`.
- Se crea un artículo FAQ vinculado al ticket.
- Se registra `ticket_cerrado` en historial.

Validaciones obligatorias:

- El ticket debe estar en `en_proceso`.
- El técnico autenticado debe ser el asignado.
- Debe existir al menos un comentario `tecnico`.
- El ticket no debe tener ya un FAQ asociado.

### Restricciones generales del ciclo

- No se permite cerrar un ticket directamente desde `activo`.
- Un ticket `cerrado` queda de solo lectura a nivel operativo.
- Un ticket cerrado sigue visible para trazabilidad, pero ya no admite comentarios ni cambios funcionales.

## 5. Sistema de comentarios (Chat)

El sistema maneja comentarios tipo chat dentro del ticket, visibles desde la vista detallada del recurso en Filament.

### Tipos de comentario

- `usuario`
- `tecnico`

### Comportamiento

- Los comentarios se listan de forma cronológica dentro del ticket.
- El backend decide automáticamente qué rol de comentario corresponde según el usuario autenticado.
- El comentario no se crea si el rol enviado no coincide con el rol del actor.

### Reglas de backend

- `USUARIO` solo puede crear comentarios `usuario`.
- `TECNICO` solo puede crear comentarios `tecnico`.
- `ADMINISTRADOR` no puede comentar.
- Solo se puede comentar cuando el ticket está en `en_proceso`.
- No se puede editar ni eliminar comentarios desde el flujo actual.

## 6. Reasignacion de tickets

La reasignación permite transferir la responsabilidad de un ticket de un técnico a otro.

### Reglas

- Solo `ADMINISTRADOR` puede reasignar.
- Solo se permite cuando el ticket está en `en_proceso`.
- El nuevo usuario debe ser un `TECNICO`.
- No se puede reasignar al mismo técnico actual.

### Implementación

- Autorización en `TicketPolicy::reassign()`.
- Lógica de negocio en `TicketService::reassignTicket()`.
- Acción visible en la tabla de tickets de Filament.

### Historial

Cada reasignación genera un evento `ticket_reasignado` en `ticket_histories`, indicando el técnico anterior y el nuevo.

## 7. Sistema de FAQ

La base de conocimiento se alimenta automáticamente cuando un ticket se cierra correctamente.

### Propósito

- Documentar soluciones reutilizables.
- Convertir la resolución operativa en conocimiento consultable.

### Cuándo se crea

- Solo durante el cierre de un ticket.
- Solo si el ticket no tiene ya un FAQ vinculado.

### Campos

- `titulo`
- `descripcion_problema`
- `resolucion`
- `causa_raiz`
- `tipo_resolucion`
- `es_reutilizable`
- `categoria`
- `usuario_id`
- `ticket_id`

### Reglas de visibilidad

- La FAQ es visible para todos los roles.
- En el panel actual se expone como recurso de solo lectura.

## 8. Historial de un ticket

El sistema registra eventos clave en `ticket_histories` para auditoría y trazabilidad.

### Propósito

- Saber quién hizo cada acción.
- Reconstruir el ciclo de vida del ticket.
- Mantener evidencia operativa del proceso.

### Estructura real actual

La implementación actual usa:

- `accion`
- `comentario`
- `usuario_id`
- `ticket_id`
- timestamps

### Acciones registradas actualmente

- `ticket_creado`
- `ticket_asignado`
- `comentario_agregado`
- `ticket_actualizado`
- `ticket_cerrado`
- `ticket_reasignado`

### Nota importante

Aunque conceptualmente el historial funciona como bitácora de cambios, la implementación actual **no** usa una estructura `descripcion + cambios JSON`. El repositorio hoy registra texto descriptivo en `comentario`.

## 9. Adjuntos

Los adjuntos son imágenes asociadas al ticket y se cargan desde la acción de comentario en la vista del ticket.

### Funcionamiento actual

- Se envían junto con un comentario.
- Son opcionales.
- El backend los guarda como registros independientes en `attachments`.
- La relación actual es directa con `ticket_id`, no con `comment_id`.

### Campos almacenados

- `ticket_id`
- `usuario_id`
- `ruta_archivo`
- `size`

### Storage

- Se guardan en el disco `public`.
- Directorio: `ticket-attachments`

### Formatos permitidos en UI

- `PNG`
- `JPG`
- `JPEG`

## 10. Instalacion y setup

### Requisitos

- PHP `8.3+`
- Composer
- Node.js
- npm
- SQLite u otro motor compatible con Laravel

### Instalación

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### Base de datos

Si usarás SQLite local:

```bash
touch database/database.sqlite
```

Configura en `.env`:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/ruta/absoluta/al/proyecto/database/database.sqlite
```

### Migraciones

```bash
php artisan migrate
```

### Enlace de storage para adjuntos

```bash
php artisan storage:link
```

### Frontend assets

```bash
npm install
npm run build
```

### Alternativa mas rápida

```bash
composer run setup
```

### Ejecución en desarrollo

Opción simple:

```bash
php artisan serve
```

O usando el script del proyecto:

```bash
composer run dev
```

Ese script levanta:

- servidor Laravel
- escucha de cola
- Vite en modo desarrollo


## 11. Guia de uso

### Crear un ticket

1. Iniciar sesión en `/admin`.
2. Entrar al recurso de tickets.
3. Crear un ticket con:
   - título
   - descripción
   - categoría
   - prioridad
4. El sistema lo registrará como `activo`.

### Flujo del técnico

1. Ver tickets activos disponibles o tickets ya asignados a él.
2. Tomar un ticket.
3. Agregar bitácora técnica y, si aplica, imágenes adjuntas.
4. Resolver el caso.
5. Cerrar el ticket llenando los datos de FAQ.

### Flujo del usuario

1. Crear el ticket.
2. Esperar a que un técnico lo tome.
3. Una vez en `en_proceso`, revisar el ticket y responder dentro del hilo si es necesario.
4. Consultar la solución final documentada vía FAQ cuando aplique.

### Flujo del administrador

1. Gestionar usuarios y departamentos.
2. Supervisar todos los tickets.
3. Editar tickets no cerrados.
4. Reasignar tickets en `en_proceso` a otro técnico.

## 12. Reglas de negocio por usuario

### Reglas por rol

- Solo `USUARIO` crea tickets.
- Solo `TECNICO` toma tickets.
- Solo el `TECNICO` asignado puede cerrar tickets.
- Solo `ADMINISTRADOR` puede reasignar tickets.
- `ADMINISTRADOR` no participa en comentarios operativos.

### Reglas por estado

- `activo`: recién creado, sin trabajo técnico activo.
- `en_proceso`: ticket tomado por un técnico.
- `cerrado`: ticket finalizado y sin nuevas interacciones operativas.

### Reglas de comentarios

- Solo en `en_proceso`.
- Usuario crea `usuario`.
- Técnico crea `tecnico`.
- No se permiten comentarios en tickets cerrados.

### Reglas de cierre

- Debe existir al menos un comentario técnico.
- Solo cierra el técnico asignado.
- El cierre genera FAQ.

### Reglas de reasignación

- Solo admin.
- Solo en `en_proceso`.
- Solo a otro técnico distinto al actual.

### Reglas de visibilidad

- Usuario ve solo sus tickets y solo cuando ya fueron tomados.
- Técnico ve tickets activos sin asignar y tickets asignados a él.
- Admin ve todos los tickets.

## 13. Implementaciones futuras

Algunas mejoras naturales para la siguiente iteración:

- Notificaciones por correo al crear, tomar, cerrar o reasignar tickets.
- SLA y vencimientos por prioridad.
- Chat en tiempo real con WebSockets.
- Búsqueda avanzada y filtros más ricos sobre FAQ e historial.
- API pública formal para integraciones externas.
- Versionado de FAQ y trazabilidad de conocimiento.
- Automatización de categorización o sugerencia de soluciones con IA.

---

## Notas técnicas relevantes

- El panel principal vive en `/admin`.
- La aplicación está configurada como **Filament-first**.
- El historial actual implementado en código es `accion + comentario`, y esa es la fuente correcta para documentación y mantenimiento.
