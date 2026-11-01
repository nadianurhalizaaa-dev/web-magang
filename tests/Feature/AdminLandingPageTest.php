<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_load_landing_page_management_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/landing-page');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.profil_kantor', null)
            ->assertJsonPath('data.alumni_magang', [])
            ->assertJsonPath('data.aktivitas_magang', [])
            ->assertJsonPath('data.komentar_aplikasi', []);
    }

    public function test_landing_page_summary_requires_admin_access(): void
    {
        $this->getJson('/api/admin/landing-page')->assertUnauthorized();

        $participant = User::factory()->create(['role' => 'peserta']);

        $this->actingAs($participant, 'sanctum')
            ->getJson('/api/admin/landing-page')
            ->assertForbidden();
    }
}
