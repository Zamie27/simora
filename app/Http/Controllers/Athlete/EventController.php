<?php

namespace App\Http\Controllers\Athlete;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

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

        // Hydrate pivot for legacy compat and participant awareness
        $hydrate = function ($event) use ($user) {
            $myParticipation = $event->participants->filter(fn ($p) => $p->user_id === $user->id)->first();
            $event->setRelation('pivot', $myParticipation);

            // Also set athletes relationship for consistency with coach view if needed
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
}
