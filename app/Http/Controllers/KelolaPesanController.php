<?php

namespace App\Http\Controllers;

use App\Models\Pesan;
use Illuminate\Http\Request;

class KelolaPesanController extends Controller
{
    /**
     * Store a new message/notification.
     */
    public function simpanPesan(Request $permintaan)
    {
        $permintaan->validate([
            'receiver_id' => ['required', 'exists:users,id'],
            'content' => ['required', 'string', 'max:1000'],
        ]);

        Pesan::create([
            'sender_id' => $permintaan->user()->id,
            'receiver_id' => $permintaan->input('receiver_id'),
            'content' => $permintaan->input('content'),
            'is_read' => false,
        ]);

        return back()->with('success', 'Catatan/pesan berhasil dikirim.');
    }

    /**
     * Mark a message as read.
     */
    public function tandaiSudahDibaca(Pesan $pesan)
    {
        if ($pesan->receiver_id !== auth()->id()) {
            abort(403);
        }

        $pesan->update(['is_read' => true]);

        return back()->with('success', 'Pesan ditandai telah dibaca.');
    }

    /**
     * Delete a message (sender only).
     */
    public function hapusPesan(Pesan $pesan)
    {
        if ($pesan->sender_id !== auth()->id()) {
            abort(403);
        }

        $pesan->delete();

        return back()->with('success', 'Pesan berhasil dihapus.');
    }
}
