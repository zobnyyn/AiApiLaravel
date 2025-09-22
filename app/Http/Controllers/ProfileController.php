<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profile;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        // Если профиль не создан — создать пустой
        if (!$profile) {
            $profile = $user->profile()->create([]);
        }

        return response()->json($profile);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        // Если профиль не создан — создать пустой
        if (!$profile) {
            $profile = $user->profile()->create([]);
        }

        $data = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'avatar_url' => 'nullable|string|max:1000',
        ]);

        $profile->update($data);

        return response()->json($profile);
    }
}
