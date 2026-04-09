<?php

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use App\Policies\TicketPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new TicketPolicy();
    $this->u = User::factory()->create();
});

test('admin can view, update, reassign any ticket', function () {
    $admin = User::factory()->create(['tipo_usuario' => 'ADMINISTRADOR']);
    $t1 = User::factory()->create();
    
    $ticket = Ticket::create([
        'usuario_id' => $this->u->id,
        'descripcion' => 'D', 'titulo' => 'T',
        'estado' => Ticket::ESTADO_ACTIVO
    ]);

    expect($this->policy->viewAny($admin))->toBeTrue()
        ->and($this->policy->view($admin, $ticket))->toBeTrue()
        ->and($this->policy->update($admin, $ticket))->toBeTrue();

    // Admin can reassign if ticket is in progress
    $ticketInProgress = Ticket::create([
        'usuario_id' => $this->u->id,
        'descripcion' => 'D', 'titulo' => 'T',
        'estado' => Ticket::ESTADO_EN_PROCESO,
        'tecnico_id' => $t1->id
    ]);
    expect($this->policy->reassign($admin, $ticketInProgress))->toBeTrue();
});

test('user can only view their own inactive tickets? wait, logic in policy says user view -> my ticket AND NOT active', function () {
    $user = User::factory()->create(['tipo_usuario' => 'USUARIO']);
    
    // According to current Policy: $user->id === $ticket->usuario_id && ! $ticket->isActive()
    $ticketActivoMio = Ticket::create(['usuario_id' => $user->id, 'descripcion' => 'D', 'titulo' => 'T', 'estado' => Ticket::ESTADO_ACTIVO]);
    $ticketEnProcesoMio = Ticket::create(['usuario_id' => $user->id, 'descripcion' => 'D', 'titulo' => 'T', 'estado' => Ticket::ESTADO_EN_PROCESO]);
    
    expect($this->policy->view($user, $ticketActivoMio))->toBeFalse(); // Policy rejects if active? interesting
    expect($this->policy->view($user, $ticketEnProcesoMio))->toBeTrue();
});

test('tech can view unassigned active tickets or assigned to them', function () {
    $tech = User::factory()->create(['tipo_usuario' => 'TECNICO']);
    $otroTech = User::factory()->create(['tipo_usuario' => 'TECNICO']);

    $ticketActivoSinTecnico = Ticket::create(['usuario_id' => $this->u->id, 'descripcion' => 'D', 'titulo' => 'T', 'estado' => Ticket::ESTADO_ACTIVO]);
    $ticketMio = Ticket::create(['usuario_id' => $this->u->id, 'descripcion' => 'D', 'titulo' => 'T', 'estado' => Ticket::ESTADO_EN_PROCESO, 'tecnico_id' => $tech->id]);
    $ticketOtro = Ticket::create(['usuario_id' => $this->u->id, 'descripcion' => 'D', 'titulo' => 'T', 'estado' => Ticket::ESTADO_EN_PROCESO, 'tecnico_id' => $otroTech->id]);

    expect($this->policy->view($tech, $ticketActivoSinTecnico))->toBeTrue()
        ->and($this->policy->view($tech, $ticketMio))->toBeTrue()
        ->and($this->policy->view($tech, $ticketOtro))->toBeFalse();
});

test('tech can assign an active ticket if not assigned', function () {
    $tech = User::factory()->create(['tipo_usuario' => 'TECNICO']);
    $t2 = User::factory()->create();
    
    $ticket = Ticket::create(['usuario_id' => $this->u->id, 'descripcion' => 'D', 'titulo' => 'T', 'estado' => Ticket::ESTADO_ACTIVO]); // tecnico_id is null

    expect($this->policy->assign($tech, $ticket))->toBeTrue();

    // Already assigned
    $ticketAsignado = Ticket::create(['usuario_id' => $this->u->id, 'descripcion' => 'D', 'titulo' => 'T', 'estado' => Ticket::ESTADO_ACTIVO, 'tecnico_id' => $t2->id]);
    expect($this->policy->assign($tech, $ticketAsignado))->toBeFalse();
});

test('tech can close ticket only if there is a tech comment', function () {
    $tech = User::factory()->create(['tipo_usuario' => 'TECNICO']);
    $ticket = Ticket::create(['usuario_id' => $this->u->id, 'descripcion' => 'D', 'titulo' => 'T', 'tecnico_id' => $tech->id, 'estado' => Ticket::ESTADO_EN_PROCESO]);

    // No comments at all
    expect($this->policy->close($tech, $ticket))->toBeFalse();

    // User puts a comment
    Comment::create(['ticket_id' => $ticket->id, 'usuario_id' => $this->u->id, 'rol' => Comment::ROL_USUARIO, 'contenido' => 'Hi']);
    expect($this->policy->close($tech, $ticket))->toBeFalse();

    // Tech puts a comment
    Comment::create(['ticket_id' => $ticket->id, 'usuario_id' => $tech->id, 'rol' => Comment::ROL_TECNICO, 'contenido' => 'Solution']);
    expect($this->policy->close($tech, $ticket))->toBeTrue();
});
