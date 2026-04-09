<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Sistema de Tickets IT - API de referencia',
    version: '1.0.0',
    description: 'Documentacion OpenAPI de las operaciones de negocio principales del sistema. La aplicacion opera canonicamente desde Filament, pero estas rutas representan el flujo REST equivalente para fines de integracion y documentacion.'
)]
#[OA\Server(
    url: '/',
    description: 'Servidor local'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctumBearer',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Token',
    description: 'Token de acceso autenticado con Laravel Sanctum.'
)]
#[OA\Tag(
    name: 'Tickets',
    description: 'Operaciones centrales del ciclo de vida de tickets.'
)]
#[OA\Tag(
    name: 'FAQ',
    description: 'Consulta de articulos generados al cerrar tickets.'
)]
#[OA\Tag(
    name: 'Users',
    description: 'Gestion administrativa de usuarios.'
)]
class OpenApiSpec
{
}

#[OA\Schema(
    schema: 'Ticket',
    type: 'object',
    required: ['id', 'usuario_id', 'titulo', 'descripcion', 'categoria', 'estado', 'prioridad', 'fecha_creacion'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 18),
        new OA\Property(property: 'usuario_id', type: 'integer', example: 7),
        new OA\Property(property: 'tecnico_id', type: 'integer', nullable: true, example: 12),
        new OA\Property(property: 'titulo', type: 'string', example: 'No puedo conectarme a la VPN'),
        new OA\Property(property: 'descripcion', type: 'string', example: 'Al intentar conectarme aparece un error de autenticacion.'),
        new OA\Property(property: 'categoria', type: 'string', enum: ['backend', 'frontend', 'bases_de_datos', 'devops', 'testing', 'seguridad', 'otro'], example: 'seguridad'),
        new OA\Property(property: 'estado', type: 'string', enum: ['activo', 'en_proceso', 'cerrado'], example: 'en_proceso'),
        new OA\Property(property: 'prioridad', type: 'string', enum: ['baja', 'media', 'alta'], example: 'alta'),
        new OA\Property(property: 'fecha_creacion', type: 'string', format: 'date-time', example: '2026-04-08T10:15:00Z'),
        new OA\Property(property: 'fecha_cierre', type: 'string', format: 'date-time', nullable: true, example: '2026-04-08T13:40:00Z'),
        new OA\Property(property: 'faq_article', ref: '#/components/schemas/FaqArticle', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-04-08T10:15:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-04-08T11:20:00Z'),
    ]
)]
class TicketSchema
{
}

#[OA\Schema(
    schema: 'Comment',
    type: 'object',
    required: ['id', 'ticket_id', 'usuario_id', 'rol', 'contenido'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 31),
        new OA\Property(property: 'ticket_id', type: 'integer', example: 18),
        new OA\Property(property: 'usuario_id', type: 'integer', example: 12),
        new OA\Property(property: 'rol', type: 'string', enum: ['tecnico', 'usuario'], example: 'tecnico'),
        new OA\Property(property: 'contenido', type: 'string', example: 'Se reinicio el servicio de autenticacion y la conexion volvio a funcionar.'),
        new OA\Property(
            property: 'attachments',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Attachment')
        ),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-04-08T11:05:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-04-08T11:05:00Z'),
    ]
)]
class CommentSchema
{
}

#[OA\Schema(
    schema: 'Attachment',
    type: 'object',
    required: ['id', 'ruta_archivo', 'size'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 4),
        new OA\Property(property: 'ruta_archivo', type: 'string', example: 'comments/31/captura-vpn.png'),
        new OA\Property(property: 'nombre', type: 'string', nullable: true, example: 'captura-vpn.png'),
        new OA\Property(property: 'mime_type', type: 'string', nullable: true, example: 'image/png'),
        new OA\Property(property: 'size', type: 'integer', example: 184532),
    ]
)]
class AttachmentSchema
{
}

#[OA\Schema(
    schema: 'FaqArticle',
    type: 'object',
    required: ['id', 'ticket_id', 'titulo', 'resolucion', 'causa_raiz', 'tipo_resolucion', 'es_reutilizable', 'categoria', 'usuario_id'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 6),
        new OA\Property(property: 'ticket_id', type: 'integer', example: 18),
        new OA\Property(property: 'titulo', type: 'string', example: 'Error de autenticacion en VPN corporativa'),
        new OA\Property(property: 'descripcion_problema', type: 'string', nullable: true, example: 'Usuarios remotos no podian autenticarse al intentar conectarse.'),
        new OA\Property(property: 'resolucion', type: 'string', example: 'Se reinicio el servicio y se regenero la configuracion del cliente afectado.'),
        new OA\Property(property: 'causa_raiz', type: 'string', example: 'Configuracion desincronizada despues de una actualizacion.'),
        new OA\Property(property: 'tipo_resolucion', type: 'string', enum: ['workaround', 'solucion_definitiva'], example: 'solucion_definitiva'),
        new OA\Property(property: 'es_reutilizable', type: 'boolean', example: true),
        new OA\Property(property: 'categoria', type: 'string', enum: ['backend', 'frontend', 'bases_de_datos', 'devops', 'testing', 'seguridad', 'otro'], example: 'seguridad'),
        new OA\Property(property: 'usuario_id', type: 'integer', example: 12),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-04-08T13:40:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-04-08T13:40:00Z'),
    ]
)]
class FaqArticleSchema
{
}

#[OA\Schema(
    schema: 'User',
    type: 'object',
    required: ['id', 'name', 'email', 'tipo_usuario'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 12),
        new OA\Property(property: 'name', type: 'string', example: 'Maria Lopez'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'maria.lopez@example.com'),
        new OA\Property(property: 'email_verified_at', type: 'string', format: 'date-time', nullable: true, example: '2026-04-08T08:00:00Z'),
        new OA\Property(property: 'tipo_usuario', type: 'string', enum: ['USUARIO', 'TECNICO', 'ADMINISTRADOR'], example: 'TECNICO'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2026-04-08T07:30:00Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2026-04-08T09:10:00Z'),
    ]
)]
class UserSchema
{
}

#[OA\Schema(
    schema: 'ValidationError',
    type: 'object',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            example: ['ticket' => ['Debe existir al menos un comentario tecnico antes de cerrar el ticket.']]
        ),
    ]
)]
class ValidationErrorSchema
{
}

#[OA\Schema(
    schema: 'ForbiddenError',
    type: 'object',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'This action is unauthorized.'),
    ]
)]
class ForbiddenErrorSchema
{
}

#[OA\Schema(
    schema: 'NotFoundError',
    type: 'object',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'No query results for model [App\\Models\\Ticket] 18'),
    ]
)]
class NotFoundErrorSchema
{
}
