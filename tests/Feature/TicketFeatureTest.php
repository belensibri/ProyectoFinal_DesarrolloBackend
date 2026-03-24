<?php

use App\Models\User;
use App\Models\Department;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('usuario puede crear ticket', function () {
    $user = User::create(['name'=>'TestUser', 'email'=>'fake@e.com', 'password'=>'123', 'tipo_usuario' => 'USUARIO']);
    $dept = Department::create(['name' => 'IT']);

    $response = $this->actingAs($user, 'sanctum')->postJson('/api/tickets', [
        'department_id' => $dept->id,
        'titulo' => 'Mi problema',
        'descripcion' => 'Ayuda por favor',
        'prioridad' => 'alta'
    ]);

    $response->assertStatus(201)
             ->assertJsonFragment(['titulo' => 'Mi problema']);
});

test('tecnico puede ser asignado a un ticket', function () {
    $tech = User::create(['name'=>'Tech', 'email'=>'tec@e.com', 'password'=>'123', 'tipo_usuario' => 'TECNICO']);
    $user = User::create(['name'=>'User', 'email'=>'u@e.com', 'password'=>'123', 'tipo_usuario' => 'USUARIO']);
    $dept = Department::create(['name' => 'IT']);
    $ticket = Ticket::create(['usuario_id' => $user->id, 'department_id' => $dept->id, 'titulo' => 't', 'descripcion' => 'd']);

    $response = $this->actingAs($tech, 'sanctum')->patchJson("/api/tickets/{$ticket->id}/assign", [
        'tecnico_id' => $tech->id
    ]);

    $response->assertStatus(200)
             ->assertJsonFragment(['estado' => 'en_progreso', 'tecnico_id' => $tech->id]);
});

test('tecnico puede cambiar estado a resuelto', function () {
    $tech = User::create(['name'=>'Tech', 'email'=>'tec1@e.com', 'password'=>'123', 'tipo_usuario' => 'TECNICO']);
    $user = User::create(['name'=>'User', 'email'=>'u2@e.com', 'password'=>'123', 'tipo_usuario' => 'USUARIO']);
    $dept = Department::create(['name' => 'IT']);
    $ticket = Ticket::create([
        'usuario_id' => $user->id, 
        'department_id' => $dept->id,
        'tecnico_id' => $tech->id,
        'titulo' => 't',
        'descripcion' => 'd',
        'estado' => 'en_progreso'
    ]);

    $response = $this->actingAs($tech, 'sanctum')->patchJson("/api/tickets/{$ticket->id}/status", [
        'estado' => 'resuelto'
    ]);

    $response->assertStatus(200)
             ->assertJsonFragment(['estado' => 'resuelto']);
});

test('usuario normal no puede cambiar estado de resuelto', function () {
    $user = User::create(['name'=>'User', 'email'=>'u4@e.com', 'password'=>'123', 'tipo_usuario' => 'USUARIO']);
    $dept = Department::create(['name' => 'IT']);
    $ticket = Ticket::create([
        'usuario_id' => $user->id, 
        'department_id' => $dept->id,
        'titulo' => 't',
        'descripcion' => 'd',
        'estado' => 'abierto'
    ]);

    $response = $this->actingAs($user, 'sanctum')->patchJson("/api/tickets/{$ticket->id}/status", [
        'estado' => 'resuelto'
    ]);

    $response->assertStatus(403);
});
