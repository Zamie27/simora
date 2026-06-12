<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KelolaDataMetrikFisikTest extends TestCase
{
    use RefreshDatabase;

    protected User $atlet;

    protected User $manajer;

    protected User $pelatih;

    protected Kategori $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Manajemen']);
        Role::create(['name' => 'Pelatih']);
        Role::create(['name' => 'Atlet']);

        $this->kategori = Kategori::create(['name' => 'Junior']);

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
            'category_id' => $this->kategori->id,
            'date_of_birth' => now()->subYears(20),
            'gender' => 'male',
        ]);
    }

    // ---------------------------------------------------------------
    // UC-06.2: Lihat Statistik BMI (Halaman Metrik Fisik)
    // ---------------------------------------------------------------

    public function test_atlet_dapat_akses_halaman_metrik_fisik(): void
    {
        $response = $this->actingAs($this->atlet)
            ->get(route('atlet.fisik.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('metrik-fisik/Index')
            ->has('metrics')
        );
    }

    public function test_non_atlet_tidak_dapat_akses_halaman_metrik_fisik(): void
    {
        $response = $this->actingAs($this->pelatih)
            ->get(route('atlet.fisik.index'));

        $response->assertStatus(403);
    }

    // ---------------------------------------------------------------
    // UC-06.1: Update BMI Atlet
    // ---------------------------------------------------------------

    public function test_atlet_dapat_simpan_data_fisik_baru(): void
    {
        $response = $this->actingAs($this->atlet)
            ->post(route('atlet.fisik.store'), [
                'height' => 175,
                'weight' => 70,
                'recorded_at' => now()->toDateString(),
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('physical_metrics', [
            'user_id' => $this->atlet->id,
            'height' => 175,
            'weight' => 70,
        ]);
    }

    public function test_simpan_data_fisik_gagal_jika_profil_tidak_lengkap(): void
    {
        // Atlet tanpa tanggal lahir dan gender
        $atletBelumLengkap = User::factory()->create([
            'role_id' => Role::where('name', 'Atlet')->first()->id,
            'is_verified' => true,
            'category_id' => $this->kategori->id,
            'date_of_birth' => null,
            'gender' => null,
        ]);

        $response = $this->actingAs($atletBelumLengkap)
            ->post(route('atlet.fisik.store'), [
                'height' => 175,
                'weight' => 70,
                'recorded_at' => now()->toDateString(),
            ]);

        $response->assertSessionHasErrors(['profile_incomplete']);
    }

    public function test_simpan_data_fisik_gagal_jika_belum_punya_kategori(): void
    {
        // Atlet tanpa kategori
        $atletBelumKategori = User::factory()->create([
            'role_id' => Role::where('name', 'Atlet')->first()->id,
            'is_verified' => true,
            'category_id' => null,
            'date_of_birth' => now()->subYears(20),
            'gender' => 'female',
        ]);

        $response = $this->actingAs($atletBelumKategori)
            ->post(route('atlet.fisik.store'), [
                'height' => 165,
                'weight' => 55,
                'recorded_at' => now()->toDateString(),
            ]);

        $response->assertSessionHasErrors(['category']);
    }

    public function test_validasi_tinggi_dan_berat_badan_tidak_valid(): void
    {
        $response = $this->actingAs($this->atlet)
            ->post(route('atlet.fisik.store'), [
                'height' => 10, // terlalu pendek
                'weight' => 300, // terlalu berat
                'recorded_at' => now()->toDateString(),
            ]);

        $response->assertSessionHasErrors(['height', 'weight']);
    }

    // ---------------------------------------------------------------
    // Pelatih Simpan Metrik Fisik untuk Atlet Binaannya
    // ---------------------------------------------------------------

    public function test_pelatih_dapat_simpan_metrik_fisik_atlet_binaannya(): void
    {
        $response = $this->actingAs($this->pelatih)
            ->post(route('pelatih.atlet.metrics.store', $this->atlet), [
                'height' => 178,
                'weight' => 72,
                'recorded_at' => now()->toDateString(),
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('physical_metrics', [
            'user_id' => $this->atlet->id,
            'height' => 178,
            'weight' => 72,
        ]);
    }

    public function test_pelatih_tidak_dapat_simpan_metrik_atlet_yang_bukan_binaannya(): void
    {
        $atletLain = User::factory()->create([
            'role_id' => Role::where('name', 'Atlet')->first()->id,
            'coach_id' => null,
            'category_id' => $this->kategori->id,
            'date_of_birth' => now()->subYears(22),
            'gender' => 'female',
        ]);

        $response = $this->actingAs($this->pelatih)
            ->post(route('pelatih.atlet.metrics.store', $atletLain), [
                'height' => 165,
                'weight' => 55,
                'recorded_at' => now()->toDateString(),
            ]);

        $response->assertStatus(403);
    }
}
