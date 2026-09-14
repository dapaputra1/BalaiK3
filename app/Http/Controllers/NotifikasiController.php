<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = Notifikasi::where('user_id', $request->user()->id);

        $notifs = (clone $baseQuery)
            ->latest()
            ->get();

        $unreadCount = (clone $baseQuery)
            ->whereNull('read_at')
            ->count();

        return view('notifikasi', compact('notifs', 'unreadCount'));
    }

    public function open(Request $request, Notifikasi $notifikasi)
    {
        if ($notifikasi->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($notifikasi->read_at === null) {
            $notifikasi->update(['read_at' => now()]);
        }

        return redirect($notifikasi->url ?: url('/'));
    }

    public function markAllRead(Request $request)
    {
        Notifikasi::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Semua notifikasi ditandai dibaca.',
                'unreadCount' => 0,
            ]);
        }

        return back()->with('status', 'Semua notifikasi ditandai dibaca.');
    }

    public function clearAll(Request $request)
    {
        Notifikasi::query()
            ->where('user_id', $request->user()->id)
            ->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Semua notifikasi dihapus.',
                'unreadCount' => 0,
            ]);
        }

        return back()->with('status', 'Semua notifikasi dihapus.');
    }
}
