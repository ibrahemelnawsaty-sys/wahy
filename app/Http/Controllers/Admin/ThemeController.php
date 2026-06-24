<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class ThemeController extends Controller
{
    public function index()
    {
        // جلب كل الإعدادات دفعة واحدة لتسريع الأداء
        $allSettings = Setting::whereIn('key', [
            'site_theme', 'primary_color', 'secondary_color', 'text_color',
            'background_color', 'font_family', 'site_logo', 'site_favicon',
            'hero_background', 'layout_style'
        ])->pluck('value', 'key');
        
        $settings = [
            'site_theme' => $allSettings['site_theme'] ?? 'light',
            'primary_color' => $allSettings['primary_color'] ?? '#3CCB8A',
            'secondary_color' => $allSettings['secondary_color'] ?? '#3B82F6',
            'text_color' => $allSettings['text_color'] ?? '#334155',
            'background_color' => $allSettings['background_color'] ?? '#ffffff',
            'font_family' => $allSettings['font_family'] ?? 'IBM Plex Sans Arabic',
            'site_logo' => $allSettings['site_logo'] ?? null,
            'site_favicon' => $allSettings['site_favicon'] ?? null,
            'hero_background' => $allSettings['hero_background'] ?? null,
            'layout_style' => $allSettings['layout_style'] ?? 'wide',
        ];

        return view('admin.theme', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_theme' => 'nullable|in:light,dark,custom',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
            'text_color' => 'nullable|string|max:7',
            'background_color' => 'nullable|string|max:7',
            'font_family' => 'nullable|string|max:100',
            'layout_style' => 'nullable|in:full-width,boxed,wide',
        ]);

        foreach ($validated as $key => $value) {
            if ($value !== null) {
                Setting::set($key, $value);
            }
        }

        // مسح الكاش
        Setting::clearCache();

        return redirect()->route('admin.theme')
            ->with('success', 'تم حفظ إعدادات الثيم بنجاح!');
    }

    public function upload(Request $request)
    {
        $type = $request->input('type');
        
        // Validation rules based on type
        $rules = [
            'file' => 'required',
            'type' => 'required|in:logo,favicon,hero_background,image,video,icon',
        ];
        
        if ($type === 'video') {
            $rules['file'] = 'required|mimes:mp4,mov,avi,wmv|max:51200'; // 50MB max
        } elseif (in_array($type, ['icon', 'image'])) {
            $rules['file'] = 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120'; // 5MB max
        } else {
            $rules['file'] = 'required|image|mimes:jpeg,png,jpg,svg|max:2048'; // 2MB max
        }
        
        $request->validate($rules);

        $file = $request->file('file');
        
        // Determine storage folder
        $folder = match($type) {
            'video' => 'videos',
            'icon' => 'icons',
            'image' => 'images',
            default => 'theme'
        };
        
        // حذف الملف القديم إذا كان theme file
        if (in_array($type, ['logo', 'favicon', 'hero_background'])) {
            $oldFile = setting("site_{$type}");
            if ($oldFile && Storage::disk('public')->exists($oldFile)) {
                Storage::disk('public')->delete($oldFile);
            }
        }

        // حفظ الملف الجديد
        $path = $file->store($folder, 'public');
        
        // حفظ المسار في الإعدادات للـ theme files فقط
        if (in_array($type, ['logo', 'favicon', 'hero_background'])) {
            Setting::set("site_{$type}", $path, 'string', "Path to {$type}");
            Setting::clearCache();
        }

        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => asset('storage/app/public/data/' . $path),
            'message' => 'تم رفع الملف بنجاح!'
        ]);
    }
}

