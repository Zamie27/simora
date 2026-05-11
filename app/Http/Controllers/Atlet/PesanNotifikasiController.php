<?php

namespace App\Http\Controllers\Atlet;

use App\Http\Controllers\Controller;
use App\Models\Pesan;
use Illuminate\Http\Request;

class PesanNotifikasiController extends Controller
{
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

    public function markAsRead(Pesan $message)
    {
        if ($message->receiver_id !== auth()->id()) {
            abort(403);
        }

        $message->update(['is_read' => true]);

        return back()->with('success', 'Pesan ditandai telah dibaca.');
    }

    public function destroy(Pesan $message)
    {
        if ($message->sender_id !== auth()->id()) {
            abort(403);
        }

        $message->delete();

        return back()->with('success', 'Pesan berhasil dihapus.');
    }
}
