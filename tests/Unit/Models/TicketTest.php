<?php

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('ticket status helpers return correct booleans', function () {
    $ticketActivo = new Ticket(['estado' => Ticket::ESTADO_ACTIVO]);
    expect($ticketActivo->isActive())->toBeTrue();
    expect($ticketActivo->isInProgress())->toBeFalse();

    $ticketEnProceso = new Ticket(['estado' => Ticket::ESTADO_EN_PROCESO]);
    expect($ticketEnProceso->isInProgress())->toBeTrue();

    $ticketCerrado = new Ticket(['estado' => Ticket::ESTADO_CERRADO]);
    expect($ticketCerrado->isClosed())->toBeTrue();
});

test('visible to scope filters tickets for basic user', function () {
    $usuario = User::factory()->create(['tipo_usuario' => 'USUARIO']);
    $otroUsuario = User::factory()->create(['tipo_usuario' => 'USUARIO']);

    $ticketMio = Ticket::create([
        'usuario_id' => $usuario->id,
        'titulo' => 'Mi ticket',
        'descripcion' => 'Desc',
        'estado' => Ticket::ESTADO_ACTIVO
    ]);

    $ticketOtro = Ticket::create([
        'usuario_id' => $otroUsuario->id,
        'titulo' => 'Otro ticket',
        'descripcion' => 'Desc',
        'estado' => Ticket::ESTADO_ACTIVO
    ]);

    $ticketsVisibles = Ticket::visibleTo($usuario)->get();

    expect($ticketsVisibles)->toHaveCount(1)
        ->and($ticketsVisibles->first()->id)->toBe($ticketMio->id);
});

test('visible to scope filters tickets for tech', function () {
    $tecnico = User::factory()->create(['tipo_usuario' => 'TECNICO']);
    $usuario = User::factory()->create(['tipo_usuario' => 'USUARIO']);

    // Activo y sin asignar -> visible para técnico
    $ticketAbierto = Ticket::create([
        'usuario_id' => $usuario->id,
        'titulo' => 'Ticket 1',
        'descripcion' => 'Descr',
        'estado' => Ticket::ESTADO_ACTIVO
    ]);

    // En proceso y asignado al técnico -> visible
    $ticketAsignado = Ticket::create([
        'usuario_id' => $usuario->id,
        'tecnico_id' => $tecnico->id,
        'titulo' => 'Ticket 2',
        'descripcion' => 'Descr',
        'estado' => Ticket::ESTADO_EN_PROCESO
    ]);

    // Asignado a otro técnico -> NO visible
    $otroTecnico = User::factory()->create(['tipo_usuario' => 'TECNICO']);
    $ticketOtroTec = Ticket::create([
        'usuario_id' => $usuario->id,
        'tecnico_id' => $otroTecnico->id,
        'titulo' => 'Ticket 3',
        'descripcion' => 'Descr',
        'estado' => Ticket::ESTADO_EN_PROCESO
    ]);

    $ticketsVisibles = Ticket::visibleTo($tecnico)->get();

    expect($ticketsVisibles)->toHaveCount(2)
        ->and($ticketsVisibles->pluck('id')->toArray())->toContain($ticketAbierto->id, $ticketAsignado->id)
        ->and($ticketsVisibles->pluck('id')->toArray())->not->toContain($ticketOtroTec->id);
});

test('visible to scope allows admin to see all tickets', function () {
    $admin = User::factory()->create(['tipo_usuario' => 'ADMINISTRADOR']);
    $u1 = User::factory()->create();
    $u2 = User::factory()->create();
    
    Ticket::create(['usuario_id' => $u1->id, 'titulo' => 'T1', 'descripcion' => 'Descr', 'estado' => Ticket::ESTADO_ACTIVO]);
    Ticket::create(['usuario_id' => $u2->id, 'titulo' => 'T2', 'descripcion' => 'Descr', 'estado' => Ticket::ESTADO_CERRADO]);

    $visibles = Ticket::visibleTo($admin)->get();
    expect($visibles)->toHaveCount(2);
});
