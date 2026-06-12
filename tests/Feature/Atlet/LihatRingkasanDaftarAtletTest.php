<?php

namespace Tests\Feature\Atlet;

use App\Models\Kategori;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LihatRingkasanDaftarAtletTest extends TestCase
{
    use RefreshDatabase;

    protected User $manajer;

    protected User $pelatih;

    protected User $atlet;

    protected Kategori $kategori;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Manajemen']);
        Role::create(['name' => 'Pelatih']);
        Role::create(['name' => 'Atlet']);

        $this->kategori = Kategori::create(['name' => 'Junior', 'description' => 'Kategori Junior']);

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
        ]);
    }

    // ---------------------------------------------------------------
    // UC-05.1: Lihat Daftar Seluruh Atlet
    // ---------------------------------------------------------------

    public function test_manajer_dapat_lihat_daftar_semua_atlet(): void
    {
        $response = $this->actingAs($this->manajer)
            ->get(route('manajemen.atlet.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('ringkasan-atlet/DaftarAtletManajemen')
            ->has('athletes')
        );
    }

    public function test_pelatih_dapat_lihat_daftar_atlet_binaannya(): void
    {
        $response = $this->actingAs($this->pelatih)
            ->get(route('pelatih.atlet.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('ringkasan-atlet/DaftarAtletPelatih')
            ->has('athletes', 1) // only 1 athlete assigned to this coach
        );
    }

    public function test_pelatih_tidak_melihat_atlet_pelatih_lain(): void
    {
        $pelatihLain = User::factory()->create([
            'role_id' => Role::where('name', 'Pelatih')->first()->id,
            'is_verified' => true,
        ]);

        $response = $this->actingAs($pelatihLain)
            ->get(route('pelatih.atlet.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('athletes', 0) // no athletes for this coach
        );
    }

    // ---------------------------------------------------------------
    // UC-05.2: Lihat Detail Data Atlet
    // ---------------------------------------------------------------

    public function test_manajer_dapat_lihat_detail_atlet(): void
    {
        $response = $this->actingAs($this->manajer)
            ->get(route('manajemen.atlet.show', $this->atlet));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('ringkasan-atlet/DetailAtletManajemen')
            ->has('athlete')
        );
    }

    public function test_pelatih_dapat_lihat_detail_atlet_binaannya(): void
    {
        $response = $this->actingAs($this->pelatih)
            ->get(route('pelatih.atlet.show', $this->atlet));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('ringkasan-atlet/DetailAtletPelatih')
            ->has('athlete')
        );
    }

    public function test_pelatih_tidak_dapat_lihat_detail_atlet_yang_bukan_binaannya(): void
    {
        $atletLain = User::factory()->create([
            'role_id' => Role::where('name', 'Atlet')->first()->id,
            'is_verified' => true,
            'coach_id' => null, // not assigned to any coach
        ]);

        $response = $this->actingAs($this->pelatih)
            ->get(route('pelatih.atlet.show', $atletLain));

        $response->assertStatus(403);
    }

    // ---------------------------------------------------------------
    // UC-05.3: Ubah Pelatih Pembina Atlet
    // ---------------------------------------------------------------

    public function test_manajer_dapat_ubah_pelatih_pembina_atlet(): void
    {
        $pelatihBaru = User::factory()->create([
            'role_id' => Role::where('name', 'Pelatih')->first()->id,
            'is_verified' => true,
        ]);

        $response = $this->actingAs($this->manajer)
            ->patch(route('manajemen.atlet.coach.update', $this->atlet), [
                'coach_id' => $pelatihBaru->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $this->atlet->id,
            'coach_id' => $pelatihBaru->id,
        ]);
    }

    public function test_pelatih_tidak_dapat_ubah_pelatih_pembina(): void
    {
        $response = $this->actingAs($this->pelatih)
            ->patch(route('manajemen.atlet.coach.update', $this->atlet), [
                'coach_id' => null,
            ]);

        $response->assertStatus(403);
    }

    // ---------------------------------------------------------------
    // UC-05.4: Update Kategori Atlet (oleh Pelatih)
    // ---------------------------------------------------------------

    public function test_pelatih_dapat_update_kategori_atlet_binaannya(): void
    {
        $kategoriBaru = Kategori::create(['name' => 'Senior', 'description' => 'Kategori Senior']);

        $response = $this->actingAs($this->pelatih)
            ->patch(route('pelatih.atlet.category.update', $this->atlet), [
                'category_id' => $kategoriBaru->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $this->atlet->id,
            'category_id' => $kategoriBaru->id,
        ]);
    }

    public function test_pelatih_tidak_dapat_update_kategori_atlet_yang_bukan_binaannya(): void
    {
        $atletLain = User::factory()->create([
            'role_id' => Role::where('name', 'Atlet')->first()->id,
            'coach_id' => null,
        ]);

        $response = $this->actingAs($this->pelatih)
            ->patch(route('pelatih.atlet.category.update', $atletLain), [
                'category_id' => $this->kategori->id,
            ]);

        $response->assertStatus(403);
    }

    // ---------------------------------------------------------------
    // Kelola Kategori (CRUD oleh Manajer)
    // ---------------------------------------------------------------

    public function test_manajer_dapat_lihat_daftar_kategori(): void
    {
        $response = $this->actingAs($this->manajer)
            ->get(route('manajemen.kategori.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('ringkasan-atlet/Kategori')
        );
    }

    public function test_manajer_dapat_tambah_kategori(): void
    {
        $response = $this->actingAs($this->manajer)
            ->post(route('manajemen.kategori.store'), [
                'name' => 'Elite',
                'description' => 'Kategori Elite',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('categories', ['name' => 'Elite']);
    }

    public function test_manajer_dapat_perbarui_kategori(): void
    {
        $response = $this->actingAs($this->manajer)
            ->put(route('manajemen.kategori.update', $this->kategori), [
                'name' => 'Junior Updated',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['name' => 'Junior Updated']);
    }

    public function test_manajer_dapat_hapus_kategori(): void
    {
        $kategoriToDelete = Kategori::create(['name' => 'ToDelete']);

        $response = $this->actingAs($this->manajer)
            ->delete(route('manajemen.kategori.destroy', $kategoriToDelete));

        $response->assertRedirect();
        $this->assertDatabaseMissing('categories', ['id' => $kategoriToDelete->id]);
    }
}
