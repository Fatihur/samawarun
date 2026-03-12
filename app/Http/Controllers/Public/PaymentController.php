<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function create(Request $request, Participant $participant, string $token): View
    {
        abort_unless($request->hasValidSignature(), 403);
        abort_unless($participant->payment_token === $token, 404);
        abort_unless($participant->canUploadPaymentProof(), 403);

        return view('public.registrations.payment', [
            'participant' => $participant->load('event'),
        ]);
    }

    public function store(Request $request, Participant $participant, string $token): RedirectResponse
    {
        abort_unless($request->hasValidSignature(), 403);
        abort_unless($participant->payment_token === $token, 404);
        abort_unless($participant->canUploadPaymentProof(), 403);

        $validated = $request->validate([
            'transfer_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $participant->update([
            'transfer_proof' => $request->file('transfer_proof')->store('participants/payments', 'public'),
            'workflow_status' => Participant::WORKFLOW_PAYMENT_SUBMITTED,
            'payment_submitted_at' => now(),
            'status' => Participant::STATUS_PENDING,
        ]);

        return redirect()->route('home')->with('success', 'Bukti pembayaran berhasil diupload dan sedang direview admin.');
    }
}
