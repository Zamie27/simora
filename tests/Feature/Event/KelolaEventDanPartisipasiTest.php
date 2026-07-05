<?php

namespace Tests\Feature\Event;

use App\Models\Event;
use App\Models\JenisEvent;
use App\Models\Kategori;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KelolaEventDanPartisipasiTest extends TestCase
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
        Kategori::create(['name' => 'Junior']);

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
    // UC-13.2: Lihat Event Per Role
    // ---------------------------------------------------------------

    public function test_manajer_dapat_lihat_daftar_event(): void
    {
        $response = $this->actingAs($this->manajer)
            ->get(route('manajemen.acara.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('kelola-event/Manajemen')
        );
    }

    public function test_pelatih_dapat_lihat_daftar_event_miliknya(): void
    {
        $response = $this->actingAs($this->pelatih)
            ->get(route('pelatih.acara.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('kelola-event/Pelatih')
        );
    }

    public function test_atlet_dapat_lihat_event_partisipasinya(): void
    {
        $response = $this->actingAs($this->atlet)
            ->get(route('atlet.acara.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('kelola-event/Atlet')
        );
    }

    // ---------------------------------------------------------------
    // UC-13.1: Buat Event & Menetapkan Partisipasi
    // ---------------------------------------------------------------

    public function test_pelatih_dapat_buat_event_baru(): void
    {
        $response = $this->actingAs($this->pelatih)
            ->post(route('pelatih.acara.store'), [
                'title' => 'Kejuaraan Nasional 2026',
                'description' => 'Event bersepeda tahunan',
                'location' => 'Velodrome Manahan',
                'event_date' => now()->addMonths(2)->toDateString(),
                'requires_license' => true,
                'event_type_id' => null,
                'athletes' => [
                    ['id' => $this->atlet->id, 'event_point_id' => null],
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('events', [
            'coach_id' => $this->pelatih->id,
            'title' => 'Kejuaraan Nasional 2026',
        ]);
    }

    public function test_manajer_dapat_buat_event_baru(): void
    {
        $response = $this->actingAs($this->manajer)
            ->post(route('manajemen.acara.store'), [
                'title' => 'Event Manajer 2026',
                'description' => 'Deskripsi manajer',
                'location' => 'Kantor',
                'event_date' => now()->addMonths(1)->toDateString(),
                'requires_license' => false,
                'event_type_id' => null,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('events', ['title' => 'Event Manajer 2026']);
    }

    public function test_buat_event_gagal_jika_tanggal_tidak_ada(): void
    {
        $response = $this->actingAs($this->pelatih)
            ->post(route('pelatih.acara.store'), [
                'title' => 'Event Tanpa Tanggal',
                // event_date tidak disertakan
            ]);

        $response->assertSessionHasErrors(['event_date']);
    }

    public function test_atlet_tidak_dapat_buat_event(): void
    {
        $response = $this->actingAs($this->atlet)
            ->post(route('pelatih.acara.store'), [
                'title' => 'Event dari Atlet',
                'event_date' => now()->addMonth()->toDateString(),
            ]);

        $response->assertStatus(403);
    }

    // ---------------------------------------------------------------
    // UC-13.3: Hapus Event
    // ---------------------------------------------------------------

    public function test_pelatih_dapat_hapus_event_miliknya(): void
    {
        $event = Event::create([
            'coach_id' => $this->pelatih->id,
            'title' => 'Event untuk Dihapus',
            'event_date' => now()->addMonth(),
            'requires_license' => false,
        ]);

        $response = $this->actingAs($this->pelatih)
            ->delete(route('pelatih.acara.destroy', $event));

        $response->assertRedirect();
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_pelatih_tidak_dapat_hapus_event_milik_pelatih_lain(): void
    {
        $pelatihLain = User::factory()->create([
            'role_id' => Role::where('name', 'Pelatih')->first()->id,
        ]);

        $eventPelatihLain = Event::create([
            'coach_id' => $pelatihLain->id,
            'title' => 'Event Pelatih Lain',
            'event_date' => now()->addMonth(),
            'requires_license' => false,
        ]);

        $response = $this->actingAs($this->pelatih)
            ->delete(route('pelatih.acara.destroy', $eventPelatihLain));

        $response->assertStatus(403);
        $this->assertDatabaseHas('events', ['id' => $eventPelatihLain->id]);
    }

    public function test_manajer_dapat_hapus_event_apapun(): void
    {
        $event = Event::create([
            'coach_id' => $this->pelatih->id,
            'title' => 'Event yang Dihapus Manajer',
            'event_date' => now()->addMonth(),
            'requires_license' => false,
        ]);

        $response = $this->actingAs($this->manajer)
            ->delete(route('manajemen.acara.destroy', $event));

        $response->assertRedirect();
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    // ---------------------------------------------------------------
    // Update Event
    // ---------------------------------------------------------------

    public function test_pelatih_dapat_update_event_miliknya(): void
    {
        $event = Event::create([
            'coach_id' => $this->pelatih->id,
            'title' => 'Event Asli',
            'event_date' => now()->addMonth(),
            'requires_license' => false,
        ]);

        $response = $this->actingAs($this->pelatih)
            ->patch(route('pelatih.acara.update', $event), [
                'title' => 'Event Diperbarui',
                'description' => 'Deskripsi baru',
                'location' => 'Lokasi baru',
                'event_date' => now()->addMonths(2)->toDateString(),
                'requires_license' => true,
                'event_type_id' => null,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Event Diperbarui',
        ]);
    }

    // ---------------------------------------------------------------
    // CRUD Tipe Event (Manajemen)
    // ---------------------------------------------------------------

    public function test_manajer_dapat_tambah_tipe_event(): void
    {
        $response = $this->actingAs($this->manajer)
            ->post(route('manajemen.tipe-acara.store'), [
                'name' => 'Road Race',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('event_types', ['name' => 'Road Race']);
    }

    public function test_manajer_dapat_hapus_tipe_event(): void
    {
        $tipe = JenisEvent::create(['name' => 'Criterium', 'coach_id' => null]);

        $response = $this->actingAs($this->manajer)
            ->delete(route('manajemen.tipe-acara.destroy', $tipe));

        $response->assertRedirect();
        $this->assertDatabaseMissing('event_types', ['id' => $tipe->id]);
    }

    // ---------------------------------------------------------------
    // Update Partisipasi Atlet (Pelatih)
    // ---------------------------------------------------------------

    public function test_pelatih_dapat_update_partisipasi_atlet_pada_eventnya(): void
    {
        $event = Event::create([
            'coach_id' => $this->pelatih->id,
            'title' => 'Event Partisipasi',
            'event_date' => now()->addMonth(),
            'requires_license' => false,
        ]);
        $event->athletes()->attach($this->atlet->id, [
            'status' => 'planned',
        ]);

        $response = $this->actingAs($this->pelatih)
            ->patch(route('pelatih.acara.participation.update', [$event, $this->atlet]), [
                'status' => 'participated',
                'result' => 'Juara 2',
                'notes' => 'Performa sangat baik',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }
}
