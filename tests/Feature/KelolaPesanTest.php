<?php

namespace Tests\Feature;

use App\Models\Pesan;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KelolaPesanTest extends TestCase
{
    use RefreshDatabase;

    protected User $pelatih;
    protected User $atlet;
    protected User $manajer;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Manajemen']);
        Role::create(['name' => 'Pelatih']);
        Role::create(['name' => 'Atlet']);

        $this->manajer = User::factory()->create([
            'role_id' => Role::where('name', 'Manajemen')->first()->id,
            'is_verified' => true,
        ]);

        $this->pelatih = User::factory()->create([
            'role_id' => Role::where('name', 'Pelatih')->first()->id,
            'is_verified' => true,
        ]);

        $this->atlet = User::factory()->create([
            'role_id' => Role::where('name', 'Atlet')->first()->id,
            'is_verified' => true,
            'coach_id' => $this->pelatih->id,
        ]);
    }

    // ---------------------------------------------------------------
    // UC-12.1: Kirim Pesan
    // ---------------------------------------------------------------

    public function test_pelatih_dapat_kirim_pesan_ke_atlet(): void
    {
        $response = $this->actingAs($this->pelatih)
            ->post(route('pelatih.pesan.store'), [
                'receiver_id' => $this->atlet->id,
                'content' => 'Besok latihan jam 6 pagi!',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('messages', [
            'sender_id' => $this->pelatih->id,
            'receiver_id' => $this->atlet->id,
            'content' => 'Besok latihan jam 6 pagi!',
            'is_read' => false,
        ]);
    }

    public function test_manajer_dapat_kirim_pesan(): void
    {
        $response = $this->actingAs($this->manajer)
            ->post(route('manajemen.pesan.store'), [
                'receiver_id' => $this->atlet->id,
                'content' => 'Harap lengkapi dokumen lisensi Anda.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('messages', [
            'sender_id' => $this->manajer->id,
            'receiver_id' => $this->atlet->id,
        ]);
    }

    public function test_kirim_pesan_gagal_jika_penerima_tidak_ada(): void
    {
        $response = $this->actingAs($this->pelatih)
            ->post(route('pelatih.pesan.store'), [
                'receiver_id' => 99999, // ID tidak ada
                'content' => 'Test pesan',
            ]);

        $response->assertSessionHasErrors(['receiver_id']);
    }

    public function test_kirim_pesan_gagal_jika_konten_kosong(): void
    {
        $response = $this->actingAs($this->pelatih)
            ->post(route('pelatih.pesan.store'), [
                'receiver_id' => $this->atlet->id,
                'content' => '', // Konten kosong
            ]);

        $response->assertSessionHasErrors(['content']);
    }

    public function test_konten_pesan_tidak_boleh_melebihi_1000_karakter(): void
    {
        $response = $this->actingAs($this->pelatih)
            ->post(route('pelatih.pesan.store'), [
                'receiver_id' => $this->atlet->id,
                'content' => str_repeat('a', 1001), // Melebihi 1000 karakter
            ]);

        $response->assertSessionHasErrors(['content']);
    }

    // ---------------------------------------------------------------
    // UC-12.2: Tandai Pesan Sudah Dibaca (Atlet)
    // ---------------------------------------------------------------

    public function test_atlet_dapat_tandai_pesan_sudah_dibaca(): void
    {
        $pesan = Pesan::create([
            'sender_id' => $this->pelatih->id,
            'receiver_id' => $this->atlet->id,
            'content' => 'Pesan dari pelatih',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->atlet)
            ->patch(route('atlet.pesan.read', $pesan));

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('messages', [
            'id' => $pesan->id,
            'is_read' => true,
        ]);
    }

    public function test_atlet_tidak_dapat_tandai_pesan_milik_pengguna_lain(): void
    {
        $atletLain = User::factory()->create([
            'role_id' => Role::where('name', 'Atlet')->first()->id,
        ]);

        $pesanUntukAtletLain = Pesan::create([
            'sender_id' => $this->pelatih->id,
            'receiver_id' => $atletLain->id, // Bukan milik $this->atlet
            'content' => 'Pesan untuk atlet lain',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->atlet)
            ->patch(route('atlet.pesan.read', $pesanUntukAtletLain));

        $response->assertStatus(403);
        $this->assertDatabaseHas('messages', [
            'id' => $pesanUntukAtletLain->id,
            'is_read' => false,
        ]);
    }

    // ---------------------------------------------------------------
    // UC-12.3: Hapus Pesan
    // ---------------------------------------------------------------

    public function test_pengirim_dapat_hapus_pesan_miliknya(): void
    {
        $pesan = Pesan::create([
            'sender_id' => $this->pelatih->id,
            'receiver_id' => $this->atlet->id,
            'content' => 'Pesan yang akan dihapus',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->pelatih)
            ->delete(route('pelatih.pesan.destroy', $pesan));

        $response->assertRedirect();
        $this->assertDatabaseMissing('messages', ['id' => $pesan->id]);
    }

    public function test_bukan_pengirim_tidak_dapat_hapus_pesan(): void
    {
        $pesan = Pesan::create([
            'sender_id' => $this->pelatih->id,
            'receiver_id' => $this->atlet->id,
            'content' => 'Pesan yang tidak boleh dihapus pengguna lain',
            'is_read' => false,
        ]);

        // Manajer (bukan pengirim) coba hapus
        $response = $this->actingAs($this->manajer)
            ->delete(route('manajemen.pesan.destroy', $pesan));

        $response->assertStatus(403);
        $this->assertDatabaseHas('messages', ['id' => $pesan->id]);
    }
}
