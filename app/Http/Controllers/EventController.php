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

class EventController extends Controller
{
    /**
     * Display a listing of the events.
     */
    public function index(Request $request): Response
    {
        $role = $request->user()->role->name ?? '';

        if ($role === 'Atlet') {
            return $this->athleteIndex($request);
        }

        if ($role === 'Manajemen') {
            return $this->managementIndex($request);
        }

        if ($role === 'Pelatih') {
            return $this->coachIndex($request);
        }

        abort(403, 'Akses ditolak.');
    }

    /**
     * -----------------------------------------------------------------
     * ATLET METHODS
     * -----------------------------------------------------------------
     */
    private function athleteIndex(Request $request): Response
    {
        $user = $request->user();

        $upcomingEvents = Event::whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->with(['type', 'participants.user', 'participants.point'])
            ->whereDate('event_date', '>=', now())
            ->orderBy('event_date', 'asc')
            ->get();

        $pastEvents = Event::whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->with(['type', 'participants.user', 'participants.point'])
            ->whereDate('event_date', '<', now())
            ->orderBy('event_date', 'desc')
            ->get();

        $hydrate = function ($event) use ($user) {
            $myParticipation = $event->participants->filter(fn ($p) => $p->user_id === $user->id)->first();
            $event->setRelation('pivot', $myParticipation);

            $event->setRelation('athletes', $event->participants->map(function ($p) {
                $u = $p->user;
                if ($u) {
                    $u->setRelation('pivot', $p);
                }

                return $u;
            })->filter());

            return $event;
        };

        $upcomingEvents->transform($hydrate);
        $pastEvents->transform($hydrate);

        return Inertia::render('athlete/Events/Index', [
            'upcomingEvents' => $upcomingEvents,
            'pastEvents' => $pastEvents,
        ]);
    }

    /**
     * -----------------------------------------------------------------
     * MANAJEMEN METHODS
     * -----------------------------------------------------------------
     */
    private function managementIndex(Request $request): Response
    {
        return Inertia::render('management/EventSettings/Index', [
            'eventTypes' => JenisEvent::with('coach')->latest()->get(),
            'eventPoints' => PoinEvent::with('coach')->latest()->get(),
        ]);
    }

    public function storeType(Request $request)
    {
        if ($request->user()->role->name !== 'Manajemen') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        JenisEvent::create([
            'coach_id' => null, // Global type
            'name' => $validated['name'],
        ]);

        return back()->with('success', 'Jenis event global berhasil ditambahkan');
    }

    public function updateType(Request $request, JenisEvent $type)
    {
        if ($request->user()->role->name !== 'Manajemen') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $type->update($validated);

        return back()->with('success', 'Jenis event berhasil diperbarui');
    }

    public function destroyType(Request $request, JenisEvent $type)
    {
        if ($request->user()->role->name !== 'Manajemen') {
            abort(403);
        }

        $type->delete();

        return back()->with('success', 'Jenis event berhasil dihapus');
    }

    public function storePoint(Request $request)
    {
        if ($request->user()->role->name !== 'Manajemen') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        PoinEvent::create([
            'coach_id' => null, // Global point
            'name' => $validated['name'],
        ]);

        return back()->with('success', 'Poin kejuaraan global berhasil ditambahkan');
    }

    public function updatePoint(Request $request, PoinEvent $point)
    {
        if ($request->user()->role->name !== 'Manajemen') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $point->update($validated);

        return back()->with('success', 'Poin kejuaraan berhasil diperbarui');
    }

    public function destroyPoint(Request $request, PoinEvent $point)
    {
        if ($request->user()->role->name !== 'Manajemen') {
            abort(403);
        }

        $point->delete();

        return back()->with('success', 'Poin kejuaraan berhasil dihapus');
    }

    /**
     * -----------------------------------------------------------------
     * COACH (PELATIH) METHODS
     * -----------------------------------------------------------------
     */
    private function coachIndex(Request $request): Response
    {
        $events = Event::where('coach_id', auth()->id())
            ->with(['type', 'participants.user', 'participants.point'])
            ->orderBy('event_date', 'desc')
            ->get();

        $events->each(function ($event) {
            $event->setRelation('athletes', $event->participants->map(function ($p) {
                $user = $p->user;
                if ($user) {
                    $user->setRelation('pivot', $p);
                }

                return $user;
            })->filter());
            $event->athletes_count = $event->participants->count();
        });

        $athletes = User::whereRole('atlet')
            ->where('coach_id', auth()->id())
            ->with('athleteProfile')
            ->get(['id', 'name'])
            ->map(function ($athlete) {
                return [
                    'id' => $athlete->id,
                    'name' => $athlete->name,
                    'has_valid_license' => $athlete->hasValidLicense(),
                ];
            });

        $eventTypes = JenisEvent::where('coach_id', auth()->id())
            ->orWhereNull('coach_id')
            ->get();
        $eventPoints = PoinEvent::where('coach_id', auth()->id())
            ->orWhereNull('coach_id')
            ->get();

        return Inertia::render('coach/Events/Index', [
            'events' => $events,
            'athletes' => $athletes,
            'eventTypes' => $eventTypes,
            'eventPoints' => $eventPoints,
        ]);
    }

    public function store(Request $request)
    {
        if ($request->user()->role->name !== 'Pelatih') {
            abort(403);
        }

        $validated = $request->validate([
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

        $event = Event::create([
            'coach_id' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'location' => $validated['location'],
            'event_date' => $validated['event_date'],
            'requires_license' => $validated['requires_license'] ?? false,
            'event_type_id' => $validated['event_type_id'],
        ]);

        if (! empty($validated['athletes'])) {
            foreach ($validated['athletes'] as $athleteData) {
                $event->athletes()->attach($athleteData['id'], [
                    'event_point_id' => $athleteData['event_point_id'] ?? null,
                ]);
            }
        }

        return back()->with('success', 'Event berhasil dibuat');
    }

    public function update(Request $request, Event $event)
    {
        if ($request->user()->role->name !== 'Pelatih') {
            abort(403);
        }

        Gate::authorize('update', $event);

        $validated = $request->validate([
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

        $event->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'location' => $validated['location'],
            'event_date' => $validated['event_date'],
            'requires_license' => $validated['requires_license'] ?? false,
            'event_type_id' => $validated['event_type_id'],
        ]);

        if (isset($validated['athletes'])) {
            $syncData = [];
            foreach ($validated['athletes'] as $athleteData) {
                $syncData[$athleteData['id']] = [
                    'event_point_id' => $athleteData['event_point_id'] ?? null,
                ];
            }
            $event->athletes()->sync($syncData);
        }

        return back()->with('success', 'Event berhasil diperbarui');
    }

    public function destroy(Request $request, Event $event)
    {
        if ($request->user()->role->name !== 'Pelatih') {
            abort(403);
        }

        Gate::authorize('delete', $event);
        $event->delete();

        return back()->with('success', 'Event berhasil dihapus');
    }

    public function updateParticipation(Request $request, Event $event, User $athlete)
    {
        if ($request->user()->role->name !== 'Pelatih') {
            abort(403);
        }

        Gate::authorize('update', $event);

        $validated = $request->validate([
            'status' => 'required|string|in:planned,participated,cancelled',
            'result' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $event->athletes()->updateExistingPivot($athlete->id, $validated);

        return back()->with('success', 'Partisipasi berhasil diperbarui');
    }
}
