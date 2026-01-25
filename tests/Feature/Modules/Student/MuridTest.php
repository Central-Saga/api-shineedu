<?php

namespace Tests\Feature\Modules\Student;

use App\Modules\Identity\Domain\Models\User;
use App\Modules\Student\Domain\Models\Murid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MuridTest extends TestCase
{
    // use RefreshDatabase; // Use transaction/refresh if project supports it

    public function test_can_list_murid()
    {
        // Mock permission/user
        $user = User::factory()->create();
        // Give permission logic here if needed (e.g. Spatie)
        // $user->givePermissionTo('student.view');

        $response = $this->actingAs($user)->getJson('/api/v2/murid');

        $response->assertStatus(200);
    }

    public function test_can_create_murid()
    {
        $user = User::factory()->create();
        // $user->givePermissionTo('student.create');

        $data = [
            'nama_lengkap' => 'Test Student',
            'no_hp' => '08123456789',
            'status' => 'Aktif',
        ];

        $response = $this->actingAs($user)->postJson('/api/v2/murid', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.nama_lengkap', 'Test Student');
    }
}
