<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Mark a specific notification as read and redirect to the participant's detail page.
     */
    public function markAsRead(Request $request, $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        
        $notification->markAsRead();

        if (isset($notification->data['participant_id'])) {
            return redirect()->route('admin.participants.show', $notification->data['participant_id']);
        }

        return redirect()->route('admin.dashboard');
    }
}
