<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\FeedbackReply;
use Illuminate\Http\Request;

class SuperadminFeedbackController extends Controller
{
    public function index(Request $request)
    {
        $rating = $request->input('rating');
        $status = $request->input('status', 'all'); // all|pending|replied

        $query = Feedback::with(['user', 'reply'])->latest();

        if ($rating && is_numeric($rating)) {
            $query->where('rating', $rating);
        }

        if ($status === 'pending') {
            $query->whereDoesntHave('reply');
        } elseif ($status === 'replied') {
            $query->whereHas('reply');
        }

        $feedbacks = $query->get();

        $total = Feedback::count();
        $replied = FeedbackReply::count();
        $pending = $total - $replied;

        return view('admin.superadmin_feedback', [
            'feedbacks' => $feedbacks,
            'filters' => [
                'rating' => $rating,
                'status' => $status,
            ],
            'stats' => [
                'total' => $total,
                'pending' => $pending,
                'replied' => $replied,
            ],
        ]);
    }

    public function reply(Request $request, Feedback $feedback)
    {
        $data = $request->validate([
            'reply_message' => ['required', 'string', 'max:2000'],
        ]);

        $payload = [
            'reply_message' => $data['reply_message'],
            'user_id' => auth()->id(),
        ];

        if ($feedback->reply) {
            $feedback->reply->update($payload);
        } else {
            $payload['feedbacks_id'] = $feedback->id;
            FeedbackReply::create($payload);
        }

        return redirect()->route('superadmin.feedback.index')
            ->with('success', 'Balasan berhasil disimpan.');
    }
}
