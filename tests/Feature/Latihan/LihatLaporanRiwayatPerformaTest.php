<?php

namespace Tests\Feature\Latihan;

use App\Models\LogLatihan;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LihatLaporanRiwayatPerformaTest extends TestCase
{
    use RefreshDatabase;

    protected User $manajer;

    protected User $pelatih;

    protected User $atlet;

    protected User $atletLain;

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

        $this->atletLain = User::factory()->create([
            'role_id' => Role::where('name', 'Atlet')->first()->id,
            'is_verified' => true,
            'coach_id' => null,
        ]);

        // Buat beberapa log latihan
        LogLatihan::factory()->count(3)->create(['athlete_id' => $this->atlet->id]);
    }

    // ---------------------------------------------------------------
    // UC-10.1: Lihat Ringkasan Performa Atlet
    // ---------------------------------------------------------------

    public function test_manajer_dapat_lihat_laporan_performa(): void
    {
        $response = $this->actingAs($this->manajer)
            ->get(route('manajemen.laporan.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('laporan-performa/Manajemen')
            ->has('athletes')
            ->has('reportData')
        );
    }

    public function test_pelatih_dapat_lihat_laporan_performa_atletnya(): void
    {
        $response = $this->actingAs($this->pelatih)
            ->get(route('pelatih.laporan.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('laporan-performa/Pelatih')
            ->has('athletes')
            ->has('reportData')
        );
    }

    public function test_atlet_tidak_dapat_akses_halaman_laporan(): void
    {
        $response = $this->actingAs($this->atlet)
            ->get(route('manajemen.laporan.index'));

        $response->assertStatus(403);
    }

    public function test_laporan_dapat_difilter_berdasarkan_periode(): void
    {
        $response = $this->actingAs($this->manajer)
            ->get(route('manajemen.laporan.index'), [
                'period' => 'this_month',
            ]);

        $response->assertStatus(200);
    }

    public function test_laporan_dapat_difilter_berdasarkan_rentang_tanggal_kustom(): void
    {
        $response = $this->actingAs($this->manajer)
            ->get(route('manajemen.laporan.index'), [
                'start_date' => now()->subMonths(3)->toDateString(),
                'end_date' => now()->toDateString(),
            ]);

        $response->assertStatus(200);
    }

    // ---------------------------------------------------------------
    // UC-10.2: Export CSV Semua Atlet
    // ---------------------------------------------------------------

    public function test_manajer_dapat_export_csv_semua_atlet(): void
    {
        $response = $this->actingAs($this->manajer)
            ->post(route('manajemen.laporan.export'), [
                'format' => 'csv',
                'period' => 'this_month',
            ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }

    // ---------------------------------------------------------------
    // UC-10.3: Export CSV Satu Atlet
    // ---------------------------------------------------------------

    public function test_manajer_dapat_export_csv_satu_atlet(): void
    {
        $response = $this->actingAs($this->manajer)
            ->post(route('manajemen.laporan.export'), [
                'athlete_id' => $this->atlet->id,
                'format' => 'csv',
                'period' => 'this_month',
            ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }

    public function test_pelatih_dapat_export_csv_atlet_binaannya(): void
    {
        $response = $this->actingAs($this->pelatih)
            ->post(route('pelatih.laporan.export'), [
                'athlete_id' => $this->atlet->id,
                'format' => 'csv',
                'period' => 'this_month',
            ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }

    public function test_pelatih_tidak_dapat_export_csv_atlet_yang_bukan_binaannya(): void
    {
        $response = $this->actingAs($this->pelatih)
            ->post(route('pelatih.laporan.export'), [
                'athlete_id' => $this->atletLain->id,
                'format' => 'csv',
                'period' => 'this_month',
            ]);

        $response->assertStatus(403);
    }

    public function test_export_gagal_jika_format_tidak_valid(): void
    {
        $response = $this->actingAs($this->manajer)
            ->post(route('manajemen.laporan.export'), [
                'format' => 'excel', // Hanya 'csv' yang diizinkan
            ]);

        $response->assertSessionHasErrors(['format']);
    }
}
