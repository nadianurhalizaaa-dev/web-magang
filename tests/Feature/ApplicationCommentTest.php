<?php

namespace Tests\Feature;

use App\Models\KomentarAplikasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_submit_an_application_comment(): void
    {
        $this->postJson('/api/komentar', [
            'rating' => 5,
            'komentar' => 'Bagus',
        ])->assertUnauthorized();

        $this->assertDatabaseCount('komentar_aplikasi', 0);
    }

    public function test_authenticated_comment_uses_the_logged_in_users_name(): void
    {
        $user = User::factory()->create(['name' => 'Widya']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/komentar', [
                'rating' => 5,
                'komentar' => 'Bagus',
                'nama_pengguna' => 'Nama palsu',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.nama_pengguna', 'Widya')
            ->assertJsonPath('data.user.name', 'Widya');

        $this->assertDatabaseHas('komentar_aplikasi', [
            'user_id' => $user->id,
            'nama_pengguna' => 'Widya',
            'komentar' => 'Bagus',
        ]);
    }

    public function test_published_comment_includes_the_linked_users_name(): void
    {
        $user = User::factory()->create(['name' => 'Widya']);
        $comment = KomentarAplikasi::create([
            'user_id' => $user->id,
            'nama_pengguna' => 'Pengguna App',
            'rating' => 5,
            'komentar' => 'Bagus',
            'status' => 'published',
        ]);

        $this->getJson('/api/komentar')
            ->assertOk()
            ->assertJsonPath('data.0.id', $comment->id)
            ->assertJsonPath('data.0.user.name', 'Widya');
    }
}
