<?php

namespace App\Http\Controllers;

use App\Models\Event;

use App\Models\JenisEvent;
use App\Models\PoinEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class KelolaEventDanPartisipasiController extends Controller
{
    /**
     * Display a listing of the events.
     */
    public function tampilData(Request $permintaan): Response
    {
        $role = $permintaan->user()->role->name ?? '';

        if ($role === 'Atlet') {
            return $this->athleteIndex($permintaan);
        }

        if ($role === 'Manajemen') {
            return $this->managementIndex($permintaan);
        }

        if ($role === 'Pelatih') {
            return $this->coachIndex($permintaan);
        }

        abort(403, 'Akses ditolak.');
    }

    /**
     * -----------------------------------------------------------------
     * ATLET METHODS
     * -----------------------------------------------------------------
     */
    private function athleteIndex(Request $permintaan): Response
    {
        $pengguna = $permintaan->user();

        $upcomingEvents = Event::whereHas('participants', function ($q) use ($pengguna) {
            $q->where('user_id', $pengguna->id);
        })
            ->with(['type', 'participants.user', 'participants.point'])
            ->whereDate('event_date', '>=', now())
            ->orderBy('event_date', 'asc')
            ->get();

        $pastEvents = Event::whereHas('participants', function ($q) use ($pengguna) {
            $q->where('user_id', $pengguna->id);
        })
            ->with(['type', 'participants.user', 'participants.point'])
            ->whereDate('event_date', '<', now())
            ->orderBy('event_date', 'desc')
            ->get();

        $hydrate = function ($acara) use ($pengguna) {
            $myParticipation = $acara->participants->filter(fn ($p) => $p->user_id === $pengguna->id)->first();
            $acara->setRelation('pivot', $myParticipation);

            $acara->setRelation('athletes', $acara->participants->map(function ($p) {
                $u = $p->user;
                if ($u) {
                    $u->setRelation('pivot', $p);
                }

                return $u;
            })->filter());

            return $acara;
        };

        $upcomingEvents->transform($hydrate);
        $pastEvents->transform($hydrate);

        return Inertia::render('atlet/Events/Index', [
            'upcomingEvents' => $upcomingEvents,
            'pastEvents' => $pastEvents,
        ]);
    }

    /**
     * -----------------------------------------------------------------
     * MANAJEMEN METHODS
     * -----------------------------------------------------------------
     */
    private function managementIndex(Request $permintaan): Response
    {
        return Inertia::render('manajemen/EventSettings/Index', [
            'eventTypes' => JenisEvent::with('coach')->latest()->get(),
            'eventPoints' => PoinEvent::with('coach')->latest()->get(),
        ]);
    }

    public function simpanTipe(Request $permintaan)
    {
        if ($permintaan->user()->role->name !== 'Manajemen') {
            abort(403);
        }

        $dataTervalidasi = $permintaan->validate([
            'name' => 'required|string|max:255',
        ]);

        JenisEvent::create([
            'coach_id' => null, // Global type
            'name' => $dataTervalidasi['name'],
        ]);

        return back()->with('success', 'Jenis event global berhasil ditambahkan');
    }

    public function perbaruiTipe(Request $permintaan, JenisEvent $tipe)
    {
        if ($permintaan->user()->role->name !== 'Manajemen') {
            abort(403);
        }

        $dataTervalidasi = $permintaan->validate([
            'name' => 'required|string|max:255',
        ]);

        $tipe->update($dataTervalidasi);

        return back()->with('success', 'Jenis event berhasil diperbarui');
    }

    public function hapusTipe(Request $permintaan, JenisEvent $tipe)
    {
        if ($permintaan->user()->role->name !== 'Manajemen') {
            abort(403);
        }

        $tipe->delete();

        return back()->with('success', 'Jenis event berhasil dihapus');
    }

    public function simpanPoin(Request $permintaan)
    {
        if ($permintaan->user()->role->name !== 'Manajemen') {
            abort(403);
        }

        $dataTervalidasi = $permintaan->validate([
            'name' => 'required|string|max:255',
        ]);

        PoinEvent::create([
            'coach_id' => null, // Global point
            'name' => $dataTervalidasi['name'],
        ]);

        return back()->with('success', 'Poin kejuaraan global berhasil ditambahkan');
    }

    public function perbaruiPoin(Request $permintaan, PoinEvent $poin)
    {
        if ($permintaan->user()->role->name !== 'Manajemen') {
            abort(403);
        }

        $dataTervalidasi = $permintaan->validate([
            'name' => 'required|string|max:255',
        ]);

        $poin->update($dataTervalidasi);

        return back()->with('success', 'Poin kejuaraan berhasil diperbarui');
    }

    public function hapusPoin(Request $permintaan, PoinEvent $poin)
    {
        if ($permintaan->user()->role->name !== 'Manajemen') {
            abort(403);
        }

        $poin->delete();

        return back()->with('success', 'Poin kejuaraan berhasil dihapus');
    }

    /**
     * -----------------------------------------------------------------
     * COACH (PELATIH) METHODS
     * -----------------------------------------------------------------
     */
    private function coachIndex(Request $permintaan): Response
    {
        $daftarAcara = Event::where('coach_id', auth()->id())
            ->with(['type', 'participants.user', 'participants.point'])
            ->orderBy('event_date', 'desc')
            ->get();

        $daftarAcara->each(function ($acara) {
            $acara->setRelation('athletes', $acara->participants->map(function ($p) {
                $pengguna = $p->user;
                if ($pengguna) {
                    $pengguna->setRelation('pivot', $p);
                }

                return $pengguna;
            })->filter());
            $acara->athletes_count = $acara->participants->count();
        });

        $daftarAtlet = User::whereRole('atlet')
            ->where('coach_id', auth()->id())
            ->with('athleteProfile')
            ->get(['id', 'name'])
            ->map(function ($atlet) {
                return [
                    'id' => $atlet->id,
                    'name' => $atlet->name,
                    'has_valid_license' => $atlet->hasValidLicense(),
                ];
            });

        $acaraTypes = JenisEvent::where('coach_id', auth()->id())
            ->orWhereNull('coach_id')
            ->get();
        $acaraPoints = PoinEvent::where('coach_id', auth()->id())
            ->orWhereNull('coach_id')
            ->get();

        return Inertia::render('pelatih/Events/Index', [
            'events' => $daftarAcara,
            'athletes' => $daftarAtlet,
            'eventTypes' => $acaraTypes,
            'eventPoints' => $acaraPoints,
        ]);
    }

    public function simpanData(Request $permintaan)
    {
        if ($permintaan->user()->role->name !== 'Pelatih') {
            abort(403);
        }

        $dataTervalidasi = $permintaan->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'event_date' => 'required|date',
            'requires_license' => 'boolean',
            'event_type_id' => 'nullable|exists:event_types,id',
            'athletes' => 'nullable|array',
            'athletes.*.id' => 'required|exists:users,id',
            'athletes.*.event_point_id' => 'nullable|exists:event_points,id',
        ]);

        $acara = Event::create([
            'coach_id' => auth()->id(),
            'title' => $dataTervalidasi['title'],
            'description' => $dataTervalidasi['description'],
            'location' => $dataTervalidasi['location'],
            'event_date' => $dataTervalidasi['event_date'],
            'requires_license' => $dataTervalidasi['requires_license'] ?? false,
            'event_type_id' => $dataTervalidasi['event_type_id'],
        ]);

        if (! empty($dataTervalidasi['athletes'])) {
            foreach ($dataTervalidasi['athletes'] as $atletData) {
                $acara->athletes()->attach($atletData['id'], [
                    'event_point_id' => $atletData['event_point_id'] ?? null,
                ]);
            }
        }

        return back()->with('success', 'Event berhasil dibuat');
    }

    public function perbaruiData(Request $permintaan, Event $acara)
    {
        if ($permintaan->user()->role->name !== 'Pelatih') {
            abort(403);
        }

        Gate::authorize('update', $acara);

        $dataTervalidasi = $permintaan->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'event_date' => 'required|date',
            'requires_license' => 'boolean',
            'event_type_id' => 'nullable|exists:event_types,id',
            'athletes' => 'nullable|array',
            'athletes.*.id' => 'required|exists:users,id',
            'athletes.*.event_point_id' => 'nullable|exists:event_points,id',
        ]);

        $acara->update([
            'title' => $dataTervalidasi['title'],
            'description' => $dataTervalidasi['description'],
            'location' => $dataTervalidasi['location'],
            'event_date' => $dataTervalidasi['event_date'],
            'requires_license' => $dataTervalidasi['requires_license'] ?? false,
            'event_type_id' => $dataTervalidasi['event_type_id'],
        ]);

        if (isset($dataTervalidasi['athletes'])) {
            $syncData = [];
            foreach ($dataTervalidasi['athletes'] as $atletData) {
                $syncData[$atletData['id']] = [
                    'event_point_id' => $atletData['event_point_id'] ?? null,
                ];
            }
            $acara->athletes()->sync($syncData);
        }

        return back()->with('success', 'Event berhasil diperbarui');
    }

    public function hapusData(Request $permintaan, Event $acara)
    {
        if ($permintaan->user()->role->name !== 'Pelatih') {
            abort(403);
        }

        Gate::authorize('delete', $acara);
        $acara->delete();

        return back()->with('success', 'Event berhasil dihapus');
    }

    public function perbaruiPartisipasi(Request $permintaan, Event $acara, User $atlet)
    {
        if ($permintaan->user()->role->name !== 'Pelatih') {
            abort(403);
        }

        Gate::authorize('update', $acara);

        $dataTervalidasi = $permintaan->validate([
            'status' => 'required|string|in:planned,participated,cancelled',
            'result' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $acara->athletes()->updateExistingPivot($atlet->id, $dataTervalidasi);

        return back()->with('success', 'Partisipasi berhasil diperbarui');
    }

    /**
     * UC-13: Kelola Event & Partisipasi
     * Turunan: Menyimpan setelan tipe event baru
     */
    public function simpanTipeEvent(Request $permintaan)
    {
        $dataTervalidasi = $permintaan->validate([
            'name' => 'required|string|max:255|unique:event_types,name',
            'description' => 'nullable|string',
        ]);

        JenisEvent::create($dataTervalidasi);

        return back()->with('success', 'Tipe Event berhasil ditambahkan.');
    }

    /**
     * UC-13: Kelola Event & Partisipasi
     * Turunan: Memperbarui setelan tipe event
     */
    public function perbaruiTipeEvent(Request $permintaan, JenisEvent $tipe)
    {
        $dataTervalidasi = $permintaan->validate([
            'name' => 'required|string|max:255|unique:event_types,name,'.$tipe->id,
            'description' => 'nullable|string',
        ]);

        $tipe->update($dataTervalidasi);

        return back()->with('success', 'Tipe Event berhasil diperbarui.');
    }

    /**
     * UC-13: Kelola Event & Partisipasi
     * Turunan: Menghapus setelan tipe event
     */
    public function hapusTipeEvent(JenisEvent $tipe)
    {
        $tipe->delete();

        return back()->with('success', 'Tipe Event berhasil dihapus.');
    }

    /**
     * UC-13: Kelola Event & Partisipasi
     * Turunan: Menyimpan setelan poin event baru
     */
    public function simpanPoinEvent(Request $permintaan)
    {
        $dataTervalidasi = $permintaan->validate([
            'rank' => 'required|integer|min:1',
            'points' => 'required|integer|min:0',
        ]);

        PoinEvent::updateOrCreate(
            ['rank' => $dataTervalidasi['rank']],
            ['points' => $dataTervalidasi['points']]
        );

        return back()->with('success', 'Poin Event berhasil disimpan.');
    }

    /**
     * UC-13: Kelola Event & Partisipasi
     * Turunan: Memperbarui setelan poin event
     */
    public function perbaruiPoinEvent(Request $permintaan, PoinEvent $poin)
    {
        $dataTervalidasi = $permintaan->validate([
            'rank' => 'required|integer|min:1',
            'points' => 'required|integer|min:0',
        ]);

        $poin->update($dataTervalidasi);

        return back()->with('success', 'Poin Event berhasil diperbarui.');
    }

    /**
     * UC-13: Kelola Event & Partisipasi
     * Turunan: Menghapus setelan poin event
     */
    public function hapusPoinEvent(PoinEvent $poin)
    {
        $poin->delete();

        return back()->with('success', 'Poin Event berhasil dihapus.');
    }
}
