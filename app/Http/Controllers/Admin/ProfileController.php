<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\UpdatesOwnProfile;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use UpdatesOwnProfile;

    public function edit(Request $request)
    {
        return view('admin.profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $this->updateOwnProfile($request);

        ActivityLog::record('profile.updated', 'El administrador actualizó su perfil.', user: $user);

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Perfil actualizado correctamente.');
    }
}
