<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Settings/Index', [
            'settings' => Setting::all()
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|exists:settings,key',
            'settings.*.value' => 'nullable',
        ]);

        foreach ($data['settings'] as $item) {
            Setting::where('key', $item['key'])->update(['value' => $item['value']]);
        }

        Cache::forget('system_settings');

        return back()->with('success', 'Đã lưu cấu hình hệ thống thành công!');
    }
}
