<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemverifikasiPendaftaranDanMenetapkanPelatihTest extends TestCase
{
    use RefreshDatabase;

    protected User $manajer;
    protected User $pelatih;
    protected User $atlet;

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
            'is_verified' => false,
        ]);
    }

    // ---------------------------------------------------------------
    // UC-04.1: Lihat Daftar Atlet Belum Terverifikasi
    // ---------------------------------------------------------------

    public function test_manajer_dapat_lihat_daftar_pengguna(): void
    {
        $response = $this->actingAs($this->manajer)
            ->get(route('manajemen.pengguna.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('verifikasi-pendaftaran/DaftarPengguna')
        );
    }

    public function test_manajer_dapat_lihat_daftar_atlet_tertunda(): void
    {
        $response = $this->actingAs($this->manajer)
            ->get(route('manajemen.pengguna.tertunda'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('verifikasi-pendaftaran/MenungguVerifikasi')
        );
    }

    public function test_pelatih_tidak_dapat_akses_halaman_verifikasi(): void
    {
        $response = $this->actingAs($this->pelatih)
            ->get(route('manajemen.pengguna.tertunda'));

        $response->assertStatus(403);
    }

    // ---------------------------------------------------------------
    // UC-04.2: Ubah Status Terverifikasi dan Menentukan Pelatih
    // ---------------------------------------------------------------

    public function test_manajer_dapat_verifikasi_atlet_dan_tetapkan_pelatih(): void
    {
        $response = $this->actingAs($this->manajer)
            ->post(route('manajemen.pengguna.verifikasi', $this->atlet), [
                'coach_id' => $this->pelatih->id,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $this->atlet->id,
            'is_verified' => true,
            'coach_id' => $this->pelatih->id,
        ]);
    }

    public function test_manajer_dapat_verifikasi_atlet_tanpa_pelatih(): void
    {
        $response = $this->actingAs($this->manajer)
            ->post(route('manajemen.pengguna.verifikasi', $this->atlet), [
                'coach_id' => null,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $this->atlet->id,
            'is_verified' => true,
        ]);
    }

    // ---------------------------------------------------------------
    // CRUD User oleh Manajer
    // ---------------------------------------------------------------

    public function test_manajer_dapat_tambah_pengguna_baru(): void
    {
        $atletRole = Role::where('name', 'Atlet')->first();

        $response = $this->actingAs($this->manajer)
            ->post(route('manajemen.pengguna.store'), [
                'name' => 'Atlet Baru',
                'email' => 'atletbaru@example.com',
                'password' => 'Password123!',
                'role_id' => $atletRole->id,
                'is_verified' => true,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'Atlet Baru',
            'email' => 'atletbaru@example.com',
        ]);
    }

    public function test_tambah_pengguna_gagal_jika_email_duplikat(): void
    {
        $atletRole = Role::where('name', 'Atlet')->first();

        $response = $this->actingAs($this->manajer)
            ->post(route('manajemen.pengguna.store'), [
                'name' => 'Atlet Duplikat',
                'email' => $this->atlet->email, // Email yang sudah ada
                'password' => 'Password123!',
                'role_id' => $atletRole->id,
            ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_manajer_dapat_perbarui_data_pengguna(): void
    {
        $response = $this->actingAs($this->manajer)
            ->patch(route('manajemen.pengguna.update', $this->atlet), [
                'name' => 'Nama Diperbarui',
                'email' => $this->atlet->email,
                'role_id' => $this->atlet->role_id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $this->atlet->id,
            'name' => 'Nama Diperbarui',
        ]);
    }

    public function test_manajer_dapat_hapus_pengguna(): void
    {
        $atletYangAkanDihapus = User::factory()->create([
            'role_id' => Role::where('name', 'Atlet')->first()->id,
        ]);

        $response = $this->actingAs($this->manajer)
            ->delete(route('manajemen.pengguna.destroy', $atletYangAkanDihapus));

        $response->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $atletYangAkanDihapus->id]);
    }

    public function test_manajer_tidak_dapat_hapus_diri_sendiri(): void
    {
        $response = $this->actingAs($this->manajer)
            ->delete(route('manajemen.pengguna.destroy', $this->manajer));

        // Should redirect back with error
        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $this->manajer->id]);
    }

    public function test_guest_tidak_dapat_akses_halaman_manajemen_pengguna(): void
    {
        $response = $this->get(route('manajemen.pengguna.index'));
        $response->assertRedirect(route('login'));
    }
}
