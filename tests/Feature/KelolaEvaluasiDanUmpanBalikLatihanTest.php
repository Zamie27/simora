<?php

namespace Tests\Feature;

use App\Models\JenisLatihan;
use App\Models\LogLatihan;
use App\Models\Role;
use App\Models\SesiLatihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KelolaEvaluasiDanUmpanBalikLatihanTest extends TestCase
{
    use RefreshDatabase;

    protected User $pelatih;

    protected User $atlet;

    protected User $pelatihLain;

    protected LogLatihan $log;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Manajemen']);
        Role::create(['name' => 'Pelatih']);
        Role::create(['name' => 'Atlet']);

        $this->pelatih = User::factory()->create([
            'role_id' => Role::where('name', 'Pelatih')->first()->id,
            'is_verified' => true,
        ]);

        $this->pelatihLain = User::factory()->create([
            'role_id' => Role::where('name', 'Pelatih')->first()->id,
            'is_verified' => true,
        ]);

        $this->atlet = User::factory()->create([
            'role_id' => Role::where('name', 'Atlet')->first()->id,
            'is_verified' => true,
            'coach_id' => $this->pelatih->id,
        ]);

        $jenisLatihan = JenisLatihan::factory()->create();
        $sesi = SesiLatihan::factory()->create([
            'coach_id' => $this->pelatih->id,
            'exercise_type_id' => $jenisLatihan->id,
        ]);

        $this->log = LogLatihan::factory()->create([
            'athlete_id' => $this->atlet->id,
            'training_session_id' => $sesi->id,
            'date' => now(),
        ]);
    }

    // ---------------------------------------------------------------
    // UC-11.2: Update Evaluasi & Umpan Balik Latihan
    // ---------------------------------------------------------------

    public function test_pelatih_dapat_update_evaluasi_log_latihan_atletnya(): void
    {
        $response = $this->actingAs($this->pelatih)
            ->patch(route('pelatih.riwayat-latihan.evaluation', $this->log), [
                'coach_rating' => 4,
                'coach_evaluation' => 'Performa baik, pertahankan konsistensi',
                'coach_comments' => 'Tingkatkan kecepatan di lap terakhir',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('training_logs', [
            'id' => $this->log->id,
            'coach_rating' => 4,
            'coach_evaluation' => 'Performa baik, pertahankan konsistensi',
        ]);
    }

    public function test_pelatih_tidak_dapat_update_evaluasi_log_atlet_yang_bukan_binaannya(): void
    {
        // Log yang dibuat pelatih lain dan atletnya bukan binaan pelatih ini
        $atletPelatihLain = User::factory()->create([
            'role_id' => Role::where('name', 'Atlet')->first()->id,
            'coach_id' => $this->pelatihLain->id,
        ]);

        $jenisLatihan = JenisLatihan::factory()->create();
        $sesiPelatihLain = SesiLatihan::factory()->create([
            'coach_id' => $this->pelatihLain->id,
            'exercise_type_id' => $jenisLatihan->id,
        ]);

        $logAtletLain = LogLatihan::factory()->create([
            'athlete_id' => $atletPelatihLain->id,
            'training_session_id' => $sesiPelatihLain->id,
            'date' => now(),
        ]);

        $response = $this->actingAs($this->pelatih)
            ->patch(route('pelatih.riwayat-latihan.evaluation', $logAtletLain), [
                'coach_rating' => 3,
                'coach_evaluation' => 'Evaluasi tidak sah',
            ]);

        $response->assertStatus(403);
    }

    public function test_validasi_coach_rating_harus_antara_1_dan_5(): void
    {
        $response = $this->actingAs($this->pelatih)
            ->patch(route('pelatih.riwayat-latihan.evaluation', $this->log), [
                'coach_rating' => 10, // Lebih dari 5
                'coach_evaluation' => 'Test',
            ]);

        $response->assertSessionHasErrors(['coach_rating']);
    }

    // ---------------------------------------------------------------
    // Pelatih Update Log Latihan (Attendance & Completion Status)
    // ---------------------------------------------------------------

    public function test_pelatih_dapat_update_log_latihan_atletnya(): void
    {
        $response = $this->actingAs($this->pelatih)
            ->patch(route('pelatih.riwayat-latihan.update', $this->log), [
                'attendance_status' => 'present',
                'completion_status' => 'completed',
                'distance_km' => 45.5,
                'duration_minutes' => 110,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('training_logs', [
            'id' => $this->log->id,
            'attendance_status' => 'present',
            'completion_status' => 'completed',
        ]);
    }

    public function test_pelatih_tidak_dapat_update_log_atlet_yang_bukan_binaannya(): void
    {
        $atletPelatihLain = User::factory()->create([
            'role_id' => Role::where('name', 'Atlet')->first()->id,
            'coach_id' => $this->pelatihLain->id,
        ]);

        $logAtletLain = LogLatihan::factory()->create([
            'athlete_id' => $atletPelatihLain->id,
            'date' => now(),
        ]);

        $response = $this->actingAs($this->pelatih)
            ->patch(route('pelatih.riwayat-latihan.update', $logAtletLain), [
                'attendance_status' => 'present',
                'completion_status' => 'completed',
            ]);

        $response->assertStatus(403);
    }

    // ---------------------------------------------------------------
    // Pelatih Hapus Log Latihan Atlet
    // ---------------------------------------------------------------

    public function test_pelatih_dapat_hapus_log_latihan_atletnya(): void
    {
        $response = $this->actingAs($this->pelatih)
            ->delete(route('pelatih.riwayat-latihan.destroy', $this->log));

        $response->assertRedirect();
        $this->assertDatabaseMissing('training_logs', ['id' => $this->log->id]);
    }

    public function test_pelatih_tidak_dapat_hapus_log_atlet_yang_bukan_binaannya(): void
    {
        $atletPelatihLain = User::factory()->create([
            'role_id' => Role::where('name', 'Atlet')->first()->id,
            'coach_id' => $this->pelatihLain->id,
        ]);

        $logAtletLain = LogLatihan::factory()->create([
            'athlete_id' => $atletPelatihLain->id,
            'date' => now(),
        ]);

        $response = $this->actingAs($this->pelatih)
            ->delete(route('pelatih.riwayat-latihan.destroy', $logAtletLain));

        $response->assertStatus(403);
        $this->assertDatabaseHas('training_logs', ['id' => $logAtletLain->id]);
    }
}
