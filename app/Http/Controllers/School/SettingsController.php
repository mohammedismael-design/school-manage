<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(): Response
    {
        $tenant = auth()->user()->tenant;

        return Inertia::render('School/Settings/Index', [
            'tenant' => $tenant,
            'settings' => $tenant->settings ?? [],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $tenant = auth()->user()->tenant;

        $validated = $request->validate([
            'name' => 'sometimes|string|max:200',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'settings' => 'nullable|array',
            'colors' => 'nullable|array',
        ]);

        $tenant->update($validated);
        $tenant->save();

        return back()->with('success', 'Settings updated successfully.');
    }

    public function uploadLogo(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        $tenant = auth()->user()->tenant;

        // Delete old logo
        if ($tenant->logo) {
            Storage::disk('public')->delete($tenant->logo);
        }

        $path = $request->file('logo')->store("logos/{$tenant->id}", 'public');

        $tenant->logo = $path;
        $tenant->save();

        return response()->json([
            'success' => true,
            'logo_url' => Storage::disk('public')->url($path),
        ]);
    }

    public function uploadFavicon(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'favicon' => 'required|image|mimes:ico,png|max:512',
        ]);

        $tenant = auth()->user()->tenant;

        if ($tenant->favicon) {
            Storage::disk('public')->delete($tenant->favicon);
        }

        $path = $request->file('favicon')->store("favicons/{$tenant->id}", 'public');

        $tenant->favicon = $path;
        $tenant->save();

        return response()->json([
            'success' => true,
            'favicon_url' => Storage::disk('public')->url($path),
        ]);
    }
}
