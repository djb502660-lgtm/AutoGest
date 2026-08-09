<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Concerns\UpdatesOwnProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use UpdatesOwnProfile;

    public function edit(Request $request)
    {
        return view('client.profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $this->updateOwnProfile($request);

        return redirect()
            ->route('client.profile.edit')
            ->with('success', 'Perfil actualizado correctamente.');
    }
}
