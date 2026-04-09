<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user casting and defaults work', function () {
    $user = User::factory()->create();

    expect($user->email_verified_at)->toBeInstanceOf(Illuminate\Support\Carbon::class);
});

test('user role helpers return true for specific types', function () {
    $usuario = User::factory()->create(['tipo_usuario' => 'USUARIO']);
    expect($usuario->isUsuario())->toBeTrue()
        ->and($usuario->isTecnico())->toBeFalse()
        ->and($usuario->isAdministrador())->toBeFalse();

    $tecnico = User::factory()->create(['tipo_usuario' => 'TECNICO']);
    expect($tecnico->isTecnico())->toBeTrue()
        ->and($tecnico->isUsuario())->toBeFalse();

    $admin = User::factory()->create(['tipo_usuario' => 'ADMINISTRADOR']);
    expect($admin->isAdministrador())->toBeTrue()
        ->and($admin->isUsuario())->toBeFalse();
});
