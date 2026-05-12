<?php

namespace App\Http\Controllers;

use App\Models\Pesan;
use Illuminate\Http\Request;

class PesanNotifikasiController extends Controller
{
    /**
     * Store a new message/notification.
     */
    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => ['required', 'exists:users,id'],
            'content' => ['required', 'string', 'max:1000'],
        ]);

        Pesan::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->input('receiver_id'),
            'content' => $request->input('content'),
            'is_read' => false,
        ]);

        return back()->with('success', 'Catatan/pesan berhasil dikirim.');
    }

    /**
     * Mark a message as read.
     */
    public function markAsRead(Pesan $message)
    {
        if ($message->receiver_id !== auth()->id()) {
            abort(403);
        }

        $message->update(['is_read' => true]);

        return back()->with('success', 'Pesan ditandai telah dibaca.');
    }

    /**
     * Delete a message (sender only).
     */
    public function destroy(Pesan $message)
    {
        if ($message->sender_id !== auth()->id()) {
            abort(403);
        }

        $message->delete();

        return back()->with('success', 'Pesan berhasil dihapus.');
    }
}
