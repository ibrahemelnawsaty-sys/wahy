<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

/**
 * موحّد ردود الـ API — كل endpoint يجب أن يستخدمه لاتساق الـ JSON.
 *
 * شكل الرد:
 * {
 *   "success": true|false,
 *   "data":    {...} | [...],
 *   "message": "...",
 *   "meta":    {...}  // optional pagination/extra
 * }
 */
class ApiResponse
{
    public static function ok(mixed $data = null, ?string $message = null, array $meta = [], int $status = 200): JsonResponse
    {
        return response()->json(array_filter([
            'success' => true,
            'data' => $data,
            'message' => $message,
            'meta' => $meta ?: null,
        ], fn ($v) => $v !== null), $status);
    }

    public static function created(mixed $data = null, ?string $message = 'تم الإنشاء بنجاح'): JsonResponse
    {
        return self::ok($data, $message, [], 201);
    }

    public static function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    public static function error(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        return response()->json(array_filter([
            'success' => false,
            'message' => $message,
            'errors' => $errors ?: null,
        ], fn ($v) => $v !== null), $status);
    }

    public static function unauthorized(string $message = 'غير مصرح'): JsonResponse
    {
        return self::error($message, 401);
    }

    public static function forbidden(string $message = 'لا تملك صلاحية تنفيذ هذا الإجراء'): JsonResponse
    {
        return self::error($message, 403);
    }

    public static function notFound(string $message = 'العنصر غير موجود'): JsonResponse
    {
        return self::error($message, 404);
    }

    public static function validationError(array $errors, string $message = 'خطأ في البيانات المُدخَلة'): JsonResponse
    {
        return self::error($message, 422, $errors);
    }
}
