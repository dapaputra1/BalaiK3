<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Models\FeedbackReply;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedbacks = auth()->check()
            ? Feedback::with('reply')->where('user_id', auth()->id())->latest()->get()
            : collect();

        return view('kontak', compact('feedbacks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'message' => 'required|string|max:2000',
            'g-recaptcha-response' => 'required|captcha',
        ]);

        Feedback::create([
            'user_id' => auth()->id(),
            'rating' => $request->rating,
            'message' => $request->message,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feedback berhasil dikirim!'
        ]);

        // return response()->json(['message' => 'Feedback berhasil dikirim!']);
    }

    public function update(Request $request, Feedback $feedback)
    {
        abort_unless(auth()->id() === $feedback->user_id, 403);

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'message' => 'required|string|max:2000',
        ]);

        $feedback->update($data);

        return back()->with('success', 'Feedback berhasil diperbarui.');
    }

    public function destroy(Request $request, Feedback $feedback)
    {
        abort_unless(auth()->id() === $feedback->user_id, 403);

        if (!$request->boolean('confirm')) {
            return back()->withErrors(['hapus' => 'Konfirmasi hapus diperlukan.']);
        }

        $feedback->delete();

        return back()->with('success', 'Feedback berhasil dihapus.');
    }




    public function reply(Request $request, $id)
    {
        $request->validate([
            'reply_message' => 'required|string|max:2000',
            'g-recaptcha-response' => 'required|captcha'
        ]);

        FeedbackReply::create([
            'feedbacks_id' => $id,
            'user_id' => auth()->id(),
            'reply_message' => $request->reply_message,
        ]);

        return back()->with('success', 'Balasan berhasil dikirim!');
    }
}
