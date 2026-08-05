<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $alerts = $request->user()->alerts()
            ->with('vehicle')
            ->latest()
            ->paginate(15);

        return view('client.notifications.index', compact('alerts'));
    }

    public function markRead(Alert $alert)
    {
        $this->authorize('update', $alert);

        $alert->update(['is_read' => true]);

        return back()->with('success', 'Notificación marcada como leída.');
    }
}
