<?php

namespace Tests\Feature;

use App\Models\PeriodeMagang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_periode_and_load_it_again(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/periode', [
                'tanggal_mulai' => '2026-11-01',
                'tanggal_selesai' => '2027-01-31',
            ])
            ->assertOk()
            ->assertJsonPath('data.tanggal_mulai', '2026-11-01')
            ->assertJsonPath('data.tanggal_selesai', '2027-01-31');

        $this->assertDatabaseHas('periode_magangs', [
            'nama_periode' => '2026-11-01 s/d 2027-01-31',
            'tanggal_mulai' => '2026-11-01',
            'tanggal_selesai' => '2027-01-31',
        ]);

        $this->getJson('/api/periode')
            ->assertOk()
            ->assertJsonPath('data.0.tanggal_mulai', '2026-11-01')
            ->assertJsonPath('data.0.tanggal_selesai', '2027-01-31');

        $this->assertSame(1, PeriodeMagang::count());
    }
}
