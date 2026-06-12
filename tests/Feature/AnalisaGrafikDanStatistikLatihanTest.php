<?php

namespace Tests\Feature;

use App\Models\JenisLatihan;
use App\Models\LogLatihan;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalisaGrafikDanStatistikLatihanTest extends TestCase
{
    use RefreshDatabase;

    protected User $atlet;

    protected User $pelatih;

    protected User $manajer;

    protected JenisLatihan $jenisLatihan;

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

        $this->jenisLatihan = JenisLatihan::factory()->create();
    }

    // ---------------------------------------------------------------
    // UC-08.1: Lihat Grafik & Statistik Latihan (Atlet)
    // ---------------------------------------------------------------

    public function test_atlet_dapat_lihat_halaman_grafik_dan_statistik_latihan(): void
    {
        $response = $this->actingAs($this->atlet)
            ->get(route('atlet.latihan.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('grafik-statistik-latihan/Index')
            ->has('logs')
            ->has('statistics')
            ->has('performanceTrend')
            ->has('exerciseTypes')
        );
    }

    public function test_atlet_dapat_filter_latihan_berdasarkan_rentang_tanggal(): void
    {
        LogLatihan::factory()->create([
            'athlete_id' => $this->atlet->id,
            'date' => now()->subDays(5),
        ]);

        $response = $this->actingAs($this->atlet)
            ->get(route('atlet.latihan.index'), [
                'start_date' => now()->subDays(10)->toDateString(),
                'end_date' => now()->toDateString(),
            ]);

        $response->assertStatus(200);
    }

    public function test_non_atlet_tidak_dapat_akses_halaman_latihan_atlet(): void
    {
        $response = $this->actingAs($this->pelatih)
            ->get(route('atlet.latihan.index'));

        $response->assertStatus(403);
    }

    // ---------------------------------------------------------------
    // Atlet Simpan Log Latihan Manual
    // ---------------------------------------------------------------

    public function test_atlet_dapat_simpan_log_latihan_manual(): void
    {
        $response = $this->actingAs($this->atlet)
            ->post(route('atlet.latihan.log.store'), [
                'exercise_type_id' => $this->jenisLatihan->id,
                'title' => 'Latihan Mandiri Pagi',
                'distance_km' => 30.5,
                'duration_minutes' => 90,
                'avg_heart_rate' => 140,
                'rpm' => 85,
                'calories' => 800,
                'intensity' => 'medium',
                'notes' => 'Cuaca cerah, kondisi baik',
                'date' => now()->toDateString(),
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('training_logs', [
            'athlete_id' => $this->atlet->id,
            'title' => 'Latihan Mandiri Pagi',
            'distance_km' => 30.5,
        ]);
    }

    public function test_simpan_log_latihan_gagal_jika_data_tidak_valid(): void
    {
        $response = $this->actingAs($this->atlet)
            ->post(route('atlet.latihan.log.store'), [
                'distance_km' => -5, // Nilai negatif tidak valid
                'duration_minutes' => -10,
                'intensity' => 'extreme', // Bukan nilai enum yang valid
            ]);

        $response->assertSessionHasErrors();
    }

    public function test_non_atlet_tidak_dapat_simpan_log_latihan(): void
    {
        $response = $this->actingAs($this->pelatih)
            ->post(route('atlet.latihan.log.store'), [
                'exercise_type_id' => $this->jenisLatihan->id,
                'title' => 'Test',
                'distance_km' => 10,
                'duration_minutes' => 30,
                'date' => now()->toDateString(),
            ]);

        $response->assertStatus(403);
    }

    // ---------------------------------------------------------------
    // Atlet Hapus Log Latihan
    // ---------------------------------------------------------------

    public function test_atlet_dapat_hapus_log_latihan_miliknya(): void
    {
        $log = LogLatihan::factory()->create([
            'athlete_id' => $this->atlet->id,
            'date' => now(),
        ]);

        $response = $this->actingAs($this->atlet)
            ->delete(route('atlet.latihan.log.destroy', $log));

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('training_logs', ['id' => $log->id]);
    }

    public function test_atlet_tidak_dapat_hapus_log_latihan_atlet_lain(): void
    {
        $atletLain = User::factory()->create([
            'role_id' => Role::where('name', 'Atlet')->first()->id,
        ]);

        $logMilikAtletLain = LogLatihan::factory()->create([
            'athlete_id' => $atletLain->id,
            'date' => now(),
        ]);

        $response = $this->actingAs($this->atlet)
            ->delete(route('atlet.latihan.log.destroy', $logMilikAtletLain));

        $response->assertStatus(403);
        $this->assertDatabaseHas('training_logs', ['id' => $logMilikAtletLain->id]);
    }
}
