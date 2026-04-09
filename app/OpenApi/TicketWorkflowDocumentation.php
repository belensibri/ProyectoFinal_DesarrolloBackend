<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class TicketWorkflowDocumentation
{
    #[OA\Post(
        path: '/api/tickets',
        operationId: 'createTicket',
        tags: ['Tickets'],
        summary: 'Crear ticket',
        description: 'Representa la creacion de un ticket por parte de un usuario final. El ticket siempre nace con estado activo.',
        security: [['sanctumBearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['titulo', 'descripcion', 'categoria', 'prioridad'],
                properties: [
                    new OA\Property(property: 'titulo', type: 'string', maxLength: 255, example: 'No puedo conectarme a la VPN'),
                    new OA\Property(property: 'descripcion', type: 'string', example: 'Desde esta manana la conexion falla con error de autenticacion.'),
                    new OA\Property(property: 'categoria', type: 'string', enum: ['backend', 'frontend', 'bases_de_datos', 'devops', 'testing', 'seguridad', 'otro'], example: 'seguridad'),
                    new OA\Property(property: 'prioridad', type: 'string', enum: ['baja', 'media', 'alta'], example: 'alta'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Ticket creado correctamente.', content: new OA\JsonContent(ref: '#/components/schemas/Ticket')),
            new OA\Response(response: 403, description: 'Solo usuarios de tipo USUARIO pueden crear tickets.', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenError')),
            new OA\Response(response: 422, description: 'Error de validacion.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function createTicket(): void
    {
    }

    #[OA\Patch(
        path: '/api/tickets/{ticket}',
        operationId: 'updateTicket',
        tags: ['Tickets'],
        summary: 'Actualizar ticket',
        description: 'Permite editar un ticket unicamente antes de que sea asignado. Los tickets cerrados no pueden modificarse.',
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'ticket', in: 'path', required: true, description: 'ID del ticket.', schema: new OA\Schema(type: 'integer', example: 18)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'titulo', type: 'string', maxLength: 255, example: 'No puedo conectarme a la VPN corporativa'),
                    new OA\Property(property: 'descripcion', type: 'string', example: 'El error comenzo despues del cambio de contrasena.'),
                    new OA\Property(property: 'categoria', type: 'string', enum: ['backend', 'frontend', 'bases_de_datos', 'devops', 'testing', 'seguridad', 'otro'], example: 'seguridad'),
                    new OA\Property(property: 'prioridad', type: 'string', enum: ['baja', 'media', 'alta'], example: 'media'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Ticket actualizado correctamente.', content: new OA\JsonContent(ref: '#/components/schemas/Ticket')),
            new OA\Response(response: 403, description: 'El usuario no tiene permiso para editar el ticket.', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenError')),
            new OA\Response(response: 404, description: 'Ticket no encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError')),
            new OA\Response(response: 422, description: 'Error de validacion o ticket no editable.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function updateTicket(): void
    {
    }

    #[OA\Patch(
        path: '/api/tickets/{ticket}/assign',
        operationId: 'assignTicket',
        tags: ['Tickets'],
        summary: 'Tomar ticket',
        description: 'Permite que un tecnico tome un ticket activo y lo cambie a en_proceso. Solo tickets activos y sin tecnico asignado pueden ser tomados.',
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'ticket', in: 'path', required: true, description: 'ID del ticket.', schema: new OA\Schema(type: 'integer', example: 18)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Ticket asignado correctamente.', content: new OA\JsonContent(ref: '#/components/schemas/Ticket')),
            new OA\Response(response: 403, description: 'Solo tecnicos pueden tomar tickets.', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenError')),
            new OA\Response(response: 404, description: 'Ticket no encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError')),
            new OA\Response(response: 422, description: 'El ticket no esta disponible para ser tomado.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function assignTicket(): void
    {
    }

    #[OA\Post(
        path: '/api/tickets/{ticket}/comments',
        operationId: 'addTicketComment',
        tags: ['Tickets'],
        summary: 'Agregar comentario',
        description: 'Registra un comentario dentro de la conversacion del ticket. Solo el usuario creador o el tecnico asignado pueden comentar mientras el ticket este en_proceso. Los tickets cerrados no aceptan nuevos comentarios.',
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'ticket', in: 'path', required: true, description: 'ID del ticket.', schema: new OA\Schema(type: 'integer', example: 18)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['contenido'],
                    properties: [
                        new OA\Property(property: 'contenido', type: 'string', example: 'Probe nuevamente y ahora si conecta.'),
                        new OA\Property(property: 'attachments[]', type: 'array', items: new OA\Items(type: 'string', format: 'binary')),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Comentario agregado correctamente.', content: new OA\JsonContent(ref: '#/components/schemas/Comment')),
            new OA\Response(response: 403, description: 'El usuario no puede participar en la conversacion del ticket.', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenError')),
            new OA\Response(response: 404, description: 'Ticket no encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError')),
            new OA\Response(response: 422, description: 'El ticket no permite comentarios o el archivo no cumple validaciones.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function addTicketComment(): void
    {
    }

    #[OA\Patch(
        path: '/api/tickets/{ticket}/close',
        operationId: 'closeTicket',
        tags: ['Tickets'],
        summary: 'Cerrar ticket',
        description: 'Cierra un ticket en_proceso y crea su registro FAQ asociado. Solo el tecnico asignado puede cerrarlo. Regla importante: un ticket no puede cerrarse sin al menos un comentario tecnico previo.',
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'ticket', in: 'path', required: true, description: 'ID del ticket.', schema: new OA\Schema(type: 'integer', example: 18)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['titulo', 'resolucion', 'causa_raiz', 'tipo_resolucion', 'es_reutilizable'],
                properties: [
                    new OA\Property(property: 'titulo', type: 'string', example: 'Error de autenticacion en VPN corporativa'),
                    new OA\Property(property: 'descripcion_problema', type: 'string', nullable: true, example: 'Usuarios remotos no podian autenticarse tras el cambio de contrasena.'),
                    new OA\Property(property: 'resolucion', type: 'string', example: 'Se regenero la configuracion del cliente y se reinicio el servicio de autenticacion.'),
                    new OA\Property(property: 'causa_raiz', type: 'string', example: 'La configuracion local del cliente quedo desfasada.'),
                    new OA\Property(property: 'tipo_resolucion', type: 'string', enum: ['workaround', 'solucion_definitiva'], example: 'solucion_definitiva'),
                    new OA\Property(property: 'es_reutilizable', type: 'boolean', example: true),
                    new OA\Property(property: 'categoria', type: 'string', enum: ['backend', 'frontend', 'bases_de_datos', 'devops', 'testing', 'seguridad', 'otro'], example: 'seguridad'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Ticket cerrado y FAQ creada correctamente.', content: new OA\JsonContent(ref: '#/components/schemas/Ticket')),
            new OA\Response(response: 403, description: 'El usuario no es el tecnico asignado al ticket.', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenError')),
            new OA\Response(response: 404, description: 'Ticket no encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError')),
            new OA\Response(response: 422, description: 'No se puede cerrar el ticket por reglas del negocio o errores de validacion.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function closeTicket(): void
    {
    }

    #[OA\Patch(
        path: '/api/tickets/{ticket}/reassign',
        operationId: 'reassignTicket',
        tags: ['Tickets'],
        summary: 'Reasignar ticket',
        description: 'Permite a un administrador cambiar el tecnico asignado. Solo los administradores pueden reasignar tickets y unicamente cuando el ticket esta en_proceso.',
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'ticket', in: 'path', required: true, description: 'ID del ticket.', schema: new OA\Schema(type: 'integer', example: 18)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['tecnico_id'],
                properties: [
                    new OA\Property(property: 'tecnico_id', type: 'integer', example: 15),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Ticket reasignado correctamente.', content: new OA\JsonContent(ref: '#/components/schemas/Ticket')),
            new OA\Response(response: 403, description: 'Solo administradores pueden reasignar tickets.', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenError')),
            new OA\Response(response: 404, description: 'Ticket no encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError')),
            new OA\Response(response: 422, description: 'El ticket no esta en proceso o el tecnico no es valido.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function reassignTicket(): void
    {
    }

    #[OA\Get(
        path: '/api/faq-articles',
        operationId: 'listFaqArticles',
        tags: ['FAQ'],
        summary: 'Listar FAQ',
        description: 'Devuelve la base de conocimiento generada al cerrar tickets. Disponible para usuarios, tecnicos y administradores.',
        security: [['sanctumBearer' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de articulos FAQ.',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/FaqArticle'))
            ),
            new OA\Response(response: 403, description: 'El usuario no tiene permisos para consultar FAQ.', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenError')),
        ]
    )]
    public function listFaqArticles(): void
    {
    }

    #[OA\Get(
        path: '/api/faq-articles/{faqArticle}',
        operationId: 'showFaqArticle',
        tags: ['FAQ'],
        summary: 'Ver articulo FAQ',
        description: 'Obtiene el detalle de un articulo FAQ asociado a un ticket cerrado.',
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'faqArticle', in: 'path', required: true, description: 'ID del articulo FAQ.', schema: new OA\Schema(type: 'integer', example: 6)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detalle del articulo FAQ.', content: new OA\JsonContent(ref: '#/components/schemas/FaqArticle')),
            new OA\Response(response: 403, description: 'El usuario no tiene permisos para consultar FAQ.', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenError')),
            new OA\Response(response: 404, description: 'Articulo FAQ no encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError')),
        ]
    )]
    public function showFaqArticle(): void
    {
    }

    #[OA\Get(
        path: '/api/users',
        operationId: 'listUsers',
        tags: ['Users'],
        summary: 'Listar usuarios',
        description: 'Devuelve el listado de usuarios del sistema. Solo administradores pueden gestionar usuarios.',
        security: [['sanctumBearer' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de usuarios.',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/User'))
            ),
            new OA\Response(response: 403, description: 'Solo administradores pueden consultar usuarios.', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenError')),
        ]
    )]
    public function listUsers(): void
    {
    }

    #[OA\Post(
        path: '/api/users',
        operationId: 'createUser',
        tags: ['Users'],
        summary: 'Crear usuario',
        description: 'Permite a un administrador registrar usuarios del sistema y definir su rol.',
        security: [['sanctumBearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'tipo_usuario'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Maria Lopez'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'maria.lopez@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'Secret123!'),
                    new OA\Property(property: 'tipo_usuario', type: 'string', enum: ['USUARIO', 'TECNICO', 'ADMINISTRADOR'], example: 'TECNICO'),
                    new OA\Property(property: 'email_verified_at', type: 'string', format: 'date-time', nullable: true, example: '2026-04-08T08:00:00Z'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Usuario creado correctamente.', content: new OA\JsonContent(ref: '#/components/schemas/User')),
            new OA\Response(response: 403, description: 'Solo administradores pueden crear usuarios.', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenError')),
            new OA\Response(response: 422, description: 'Error de validacion.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function createUser(): void
    {
    }

    #[OA\Get(
        path: '/api/users/{user}',
        operationId: 'showUser',
        tags: ['Users'],
        summary: 'Ver usuario',
        description: 'Obtiene el detalle de un usuario del sistema. Operacion reservada a administradores.',
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, description: 'ID del usuario.', schema: new OA\Schema(type: 'integer', example: 12)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Detalle del usuario.', content: new OA\JsonContent(ref: '#/components/schemas/User')),
            new OA\Response(response: 403, description: 'Solo administradores pueden consultar usuarios.', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenError')),
            new OA\Response(response: 404, description: 'Usuario no encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError')),
        ]
    )]
    public function showUser(): void
    {
    }

    #[OA\Patch(
        path: '/api/users/{user}',
        operationId: 'updateUser',
        tags: ['Users'],
        summary: 'Actualizar usuario',
        description: 'Permite a un administrador actualizar datos del usuario, incluyendo su rol.',
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, description: 'ID del usuario.', schema: new OA\Schema(type: 'integer', example: 12)),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Maria Lopez'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'maria.lopez@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'NuevoSecret123!'),
                    new OA\Property(property: 'tipo_usuario', type: 'string', enum: ['USUARIO', 'TECNICO', 'ADMINISTRADOR'], example: 'ADMINISTRADOR'),
                    new OA\Property(property: 'email_verified_at', type: 'string', format: 'date-time', nullable: true, example: '2026-04-08T08:00:00Z'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Usuario actualizado correctamente.', content: new OA\JsonContent(ref: '#/components/schemas/User')),
            new OA\Response(response: 403, description: 'Solo administradores pueden actualizar usuarios.', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenError')),
            new OA\Response(response: 404, description: 'Usuario no encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError')),
            new OA\Response(response: 422, description: 'Error de validacion.', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function updateUser(): void
    {
    }

    #[OA\Delete(
        path: '/api/users/{user}',
        operationId: 'deleteUser',
        tags: ['Users'],
        summary: 'Eliminar usuario',
        description: 'Permite a un administrador eliminar un usuario del sistema.',
        security: [['sanctumBearer' => []]],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, description: 'ID del usuario.', schema: new OA\Schema(type: 'integer', example: 12)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Usuario eliminado correctamente.'),
            new OA\Response(response: 403, description: 'Solo administradores pueden eliminar usuarios.', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenError')),
            new OA\Response(response: 404, description: 'Usuario no encontrado.', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundError')),
        ]
    )]
    public function deleteUser(): void
    {
    }
}
