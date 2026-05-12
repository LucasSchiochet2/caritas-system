<?php

use App\Models\Parish;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows diocese admins to manage any parish', function () {
    $user = User::factory()->dioceseAdmin()->create();
    $parish = Parish::factory()->create();

    expect($user->canManageParish($parish))->toBeTrue();
});

it('allows parish admins to manage only their parish', function () {
    $user = User::factory()->create();
    $ownParish = Parish::factory()->create();
    $otherParish = Parish::factory()->create();

    $user->parishes()->attach($ownParish, ['role' => 'admin']);

    expect($user->isParishAdmin())->toBeTrue()
        ->and($user->canManageParish($ownParish))->toBeTrue()
        ->and($user->canManageParish($otherParish))->toBeFalse();
});

it('redirects authenticated admins away from the login page to the admin dashboard', function () {
    $user = User::factory()->dioceseAdmin()->create();

    $response = $this->actingAs($user, 'backpack')->get('/admin/login');

    $response->assertRedirect('/admin/dashboard');
});

it('redirects admins to the admin dashboard after login', function () {
    User::factory()->dioceseAdmin()->create([
        'email' => 'admin@example.com',
        'password' => 'password',
    ]);

    $response = $this
        ->from('/admin/login')
        ->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

    $response->assertRedirect('/admin/dashboard');
});

it('does not send backpack admins to an old intended url after login', function () {
    User::factory()->dioceseAdmin()->create([
        'email' => 'admin@example.com',
        'password' => 'password',
    ]);

    $response = $this
        ->withSession(['url.intended' => '/'])
        ->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

    $response->assertRedirect('/admin/dashboard');
});

it('allows diocese admins to see the backpack dashboard', function () {
    $user = User::factory()->dioceseAdmin()->create();

    $response = $this->actingAs($user, 'backpack')->get('/admin/dashboard');

    $response->assertOk();
});

it('uses forwarded https URLs for backpack basset assets', function () {
    $user = User::factory()->dioceseAdmin()->create();

    $response = $this
        ->actingAs($user, 'backpack')
        ->withServerVariables([
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_HOST' => 'caritas-system-production.up.railway.app',
            'HTTP_X_FORWARDED_HOST' => 'caritas-system-production.up.railway.app',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_PORT' => '443',
        ])
        ->get('/admin/dashboard');

    $response->assertOk();

    preg_match_all('/(?:src|href)=["\']([^"\']*\/storage\/basset\/[^"\']+)/i', $response->getContent(), $matches);

    $bassetUrls = collect($matches[1]);

    expect($bassetUrls)->not->toBeEmpty()
        ->and($bassetUrls->filter(fn (string $url) => str_starts_with($url, 'http://'))->all())->toBe([]);
});

it('allows diocese admins to open parish and user management screens', function () {
    $user = User::factory()->dioceseAdmin()->create();

    $this->actingAs($user, 'backpack')->get('/admin/bazaar-item')->assertOk();
    $this->actingAs($user, 'backpack')->get('/admin/parish')->assertOk();
    $this->actingAs($user, 'backpack')->get('/admin/user')->assertOk();
});

it('prevents parish admins from opening bazaar inventory management', function () {
    $user = User::factory()->create();
    $parish = Parish::factory()->create();

    $user->parishes()->attach($parish, ['role' => 'admin']);

    $this->actingAs($user, 'backpack')->get('/admin/bazaar-item')->assertForbidden();
});

it('generates unique parish slugs from the parish name', function () {
    $user = User::factory()->dioceseAdmin()->create();

    $this->actingAs($user, 'backpack')->post('/admin/parish', [
        'name' => 'Paroquia Sao Jose',
        'active' => true,
    ])->assertRedirect();

    $this->actingAs($user, 'backpack')->post('/admin/parish', [
        'name' => 'Paroquia Sao Jose',
        'active' => true,
    ])->assertRedirect();

    $this->assertDatabaseHas('parishes', [
        'name' => 'Paroquia Sao Jose',
        'slug' => 'paroquia-sao-jose',
    ]);

    $this->assertDatabaseHas('parishes', [
        'name' => 'Paroquia Sao Jose',
        'slug' => 'paroquia-sao-jose-2',
    ]);
});
