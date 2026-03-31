<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventPoint;
use App\Models\EventType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
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
            ->get(['id', 'name']);

        $eventTypes = EventType::where('coach_id', auth()->id())->get();
        $eventPoints = EventPoint::where('coach_id', auth()->id())->get();

        return Inertia::render('coach/Events/Index', [
            'events' => $events,
            'athletes' => $athletes,
            'eventTypes' => $eventTypes,
            'eventPoints' => $eventPoints,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'event_date' => 'required|date',
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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        Gate::authorize('update', $event);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'event_date' => 'required|date',
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        Gate::authorize('delete', $event);
        $event->delete();

        return back()->with('success', 'Event berhasil dihapus');
    }

    /**
     * Update participation detail for an athlete.
     */
    public function updateParticipation(Request $request, Event $event, User $athlete)
    {
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
