<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PageBuilder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class PageBuilderController extends Controller
{
    public function index()
    {
        // جلب الحقول المطلوبة فقط لتسريع الأداء
        $pages = PageBuilder::select('id', 'page_name', 'slug', 'meta_title', 'is_active', 'updated_at')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.create-pro');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page_name' => 'required|string|max:255|unique:page_builder,page_name',
            'slug' => 'required|string|max:255|unique:page_builder,slug|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'json_data' => 'required|json',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // تحويل JSON وفحصه
            $jsonData = json_decode($request->json_data, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()->back()
                    ->with('error', 'بيانات JSON غير صالحة!')
                    ->withInput();
            }

            $page = PageBuilder::create([
                'page_name' => $request->page_name,
                'slug' => $request->slug,
                'json_data' => $jsonData,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'og_image' => $request->og_image,
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('admin.pages.index')
                ->with('success', 'تم إنشاء الصفحة بنجاح!');
                
        } catch (\Exception $e) {
            \Log::error('Error creating page: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إنشاء الصفحة!')
                ->withInput();
        }
    }

    public function edit($id)
    {
        $page = PageBuilder::findOrFail($id);
        return view('admin.pages.edit-pro', compact('page'));
    }

    public function update(Request $request, $id)
    {
        $page = PageBuilder::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'page_name' => 'required|string|max:255|unique:page_builder,page_name,' . $id,
            'slug' => 'required|string|max:255|unique:page_builder,slug,' . $id . '|regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            'json_data' => 'required|json',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // تحويل JSON وفحصه
            $jsonData = json_decode($request->json_data, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return redirect()->back()
                    ->with('error', 'بيانات JSON غير صالحة!')
                    ->withInput();
            }

            $page->update([
                'page_name' => $request->page_name,
                'slug' => $request->slug,
                'json_data' => $jsonData,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'og_image' => $request->og_image,
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('admin.pages.index')
                ->with('success', 'تم تحديث الصفحة بنجاح!');
                
        } catch (\Exception $e) {
            \Log::error('Error updating page: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء تحديث الصفحة!')
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $page = PageBuilder::findOrFail($id);
            $pageName = $page->page_name;
            $page->delete();

            return redirect()->route('admin.pages.index')
                ->with('success', "تم حذف صفحة '{$pageName}' بنجاح!");
                
        } catch (\Exception $e) {
            \Log::error('Error deleting page: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء حذف الصفحة!');
        }
    }
    
    /**
     * معاينة الصفحة قبل الحفظ (AJAX)
     */
    public function preview(Request $request)
    {
        try {
            $jsonData = json_decode($request->json_data, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'بيانات JSON غير صالحة!'
                ], 400);
            }

            // حفظ في الجلسة مؤقتاً
            session(['page_preview' => [
                'page_name' => $request->page_name ?? 'معاينة',
                'json_data' => $jsonData,
                'meta_title' => $request->meta_title,
            ]]);

            return response()->json([
                'success' => true,
                'preview_url' => route('admin.pages.preview.show')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء المعاينة!'
            ], 500);
        }
    }
    
    /**
     * عرض المعاينة
     */
    public function showPreview()
    {
        $pageData = session('page_preview');
        
        if (!$pageData) {
            abort(404, 'لا توجد معاينة متاحة');
        }
        
        // إنشاء object مؤقت للعرض
        $page = (object) $pageData;
        
        return view('pages.show', compact('page'));
    }
}

