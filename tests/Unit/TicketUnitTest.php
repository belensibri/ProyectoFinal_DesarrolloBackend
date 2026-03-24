<?php

use App\Models\User;
use App\Models\Ticket;
use App\Policies\TicketPolicy;

test('politica de ticket permite tecnico cambiar a resuelto', function () {
    $tech = new User();
    $tech->id = 2;
    $tech->tipo_usuario = 'TECNICO';

    $ticket = new Ticket();
    $ticket->id = 1;
    $ticket->usuario_id = 1;
    $ticket->tecnico_id = 2;
    $ticket->estado = 'en_progreso';

    $policy = new TicketPolicy();
    expect($policy->updateStatus($tech, $ticket, 'resuelto'))->toBeTrue();
});

test('politica de ticket deniega usuario cambiar a resuelto', function () {
    $user = new User();
    $user->id = 1;
    $user->tipo_usuario = 'USUARIO';

    $ticket = new Ticket();
    $ticket->id = 1;
    $ticket->usuario_id = 1;
    $ticket->estado = 'en_progreso';

    $policy = new TicketPolicy();
    expect($policy->updateStatus($user, $ticket, 'resuelto'))->toBeFalse();
});
