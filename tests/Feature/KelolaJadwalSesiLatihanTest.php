<?php

namespace Tests\Feature;

use App\Models\JenisLatihan;
use App\Models\Role;
use App\Models\SesiLatihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KelolaJadwalSesiLatihanTest extends TestCase
{
    use RefreshDatabase;

    protected User $pelatih;
    protected User $atlet;
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
    // UC-07.2: Lihat Sesi Latihan
    // ---------------------------------------------------------------

    public function test_pelatih_dapat_lihat_daftar_sesi_latihan(): void
    {
        SesiLatihan::factory()->create([
            'coach_id' => $this->pelatih->id,
            'exercise_type_id' => $this->jenisLatihan->id,
        ]);

        $response = $this->actingAs($this->pelatih)
            ->get(route('pelatih.sesi-latihan.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('jadwal-sesi-latihan/Index')
            ->has('sessions')
            ->has('exerciseTypes')
            ->has('athletes')
        );
    }

    public function test_pelatih_dapat_lihat_detail_sesi_latihan_miliknya(): void
    {
        $sesi = SesiLatihan::factory()->create([
            'coach_id' => $this->pelatih->id,
            'exercise_type_id' => $this->jenisLatihan->id,
        ]);

        $response = $this->actingAs($this->pelatih)
            ->get(route('pelatih.sesi-latihan.show', $sesi));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('jadwal-sesi-latihan/Detail')
        );
    }

    public function test_pelatih_tidak_dapat_lihat_detail_sesi_latihan_pelatih_lain(): void
    {
        $pelatihLain = User::factory()->create([
            'role_id' => Role::where('name', 'Pelatih')->first()->id,
            'is_verified' => true,
        ]);

        $sesiMilikPelatihLain = SesiLatihan::factory()->create([
            'coach_id' => $pelatihLain->id,
            'exercise_type_id' => $this->jenisLatihan->id,
        ]);

        $response = $this->actingAs($this->pelatih)
            ->get(route('pelatih.sesi-latihan.show', $sesiMilikPelatihLain));

        $response->assertStatus(403);
    }

    // ---------------------------------------------------------------
    // UC-07.1: Buat Sesi Latihan
    // ---------------------------------------------------------------

    public function test_pelatih_dapat_buat_sesi_latihan_baru(): void
    {
        $response = $this->actingAs($this->pelatih)
            ->post(route('pelatih.sesi-latihan.store'), [
                'title' => 'Sesi Endurance Pagi',
                'description' => 'Latihan endurance 50km',
                'exercise_type_id' => $this->jenisLatihan->id,
                'scheduled_date' => now()->addDays(3)->toDateString(),
                'scheduled_time' => '07:00',
                'location' => 'Velodrome',
                'repeat_weekly' => false,
                'target_distance_km' => 50,
                'target_duration_minutes' => 120,
                'athlete_ids' => [$this->atlet->id],
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('training_sessions', [
            'coach_id' => $this->pelatih->id,
            'title' => 'Sesi Endurance Pagi',
        ]);
    }

    public function test_buat_sesi_latihan_gagal_jika_data_tidak_lengkap(): void
    {
        $response = $this->actingAs($this->pelatih)
            ->post(route('pelatih.sesi-latihan.store'), [
                'title' => '', // Required field kosong
                'exercise_type_id' => $this->jenisLatihan->id,
            ]);

        $response->assertSessionHasErrors(['title', 'scheduled_date', 'scheduled_time', 'athlete_ids']);
    }

    public function test_atlet_tidak_dapat_buat_sesi_latihan(): void
    {
        $response = $this->actingAs($this->atlet)
            ->post(route('pelatih.sesi-latihan.store'), [
                'title' => 'Test',
                'exercise_type_id' => $this->jenisLatihan->id,
                'scheduled_date' => now()->addDay()->toDateString(),
                'scheduled_time' => '08:00',
                'athlete_ids' => [$this->atlet->id],
            ]);

        $response->assertStatus(403);
    }

    // ---------------------------------------------------------------
    // UC-07.3: Hapus Sesi Latihan
    // ---------------------------------------------------------------

    public function test_pelatih_dapat_hapus_sesi_latihan_miliknya(): void
    {
        $sesi = SesiLatihan::factory()->create([
            'coach_id' => $this->pelatih->id,
            'exercise_type_id' => $this->jenisLatihan->id,
        ]);

        $response = $this->actingAs($this->pelatih)
            ->delete(route('pelatih.sesi-latihan.destroy', $sesi));

        $response->assertRedirect();
        $this->assertDatabaseMissing('training_sessions', ['id' => $sesi->id]);
    }

    public function test_pelatih_tidak_dapat_hapus_sesi_latihan_pelatih_lain(): void
    {
        $pelatihLain = User::factory()->create([
            'role_id' => Role::where('name', 'Pelatih')->first()->id,
        ]);

        $sesiMilikPelatihLain = SesiLatihan::factory()->create([
            'coach_id' => $pelatihLain->id,
            'exercise_type_id' => $this->jenisLatihan->id,
        ]);

        $response = $this->actingAs($this->pelatih)
            ->delete(route('pelatih.sesi-latihan.destroy', $sesiMilikPelatihLain));

        $response->assertStatus(403);
        $this->assertDatabaseHas('training_sessions', ['id' => $sesiMilikPelatihLain->id]);
    }

    // ---------------------------------------------------------------
    // CRUD Jenis Latihan (oleh Manajer)
    // ---------------------------------------------------------------

    public function test_manajer_dapat_lihat_daftar_jenis_latihan(): void
    {
        $response = $this->actingAs($this->manajer)
            ->get(route('manajemen.jenis-latihan.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('jadwal-sesi-latihan/JenisLatihan')
        );
    }

    public function test_manajer_dapat_tambah_jenis_latihan(): void
    {
        $response = $this->actingAs($this->manajer)
            ->post(route('manajemen.jenis-latihan.store'), [
                'name' => 'Interval Training',
                'description' => 'High intensity interval training',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('exercise_types', ['name' => 'Interval Training']);
    }

    public function test_manajer_dapat_perbarui_jenis_latihan(): void
    {
        $response = $this->actingAs($this->manajer)
            ->put(route('manajemen.jenis-latihan.update', $this->jenisLatihan), [
                'name' => 'Nama Diperbarui',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('exercise_types', ['name' => 'Nama Diperbarui']);
    }

    public function test_manajer_dapat_hapus_jenis_latihan(): void
    {
        $jenisToDelete = JenisLatihan::factory()->create();

        $response = $this->actingAs($this->manajer)
            ->delete(route('manajemen.jenis-latihan.destroy', $jenisToDelete));

        $response->assertRedirect();
        $this->assertDatabaseMissing('exercise_types', ['id' => $jenisToDelete->id]);
    }
}
