<?php

/**
 * Scribe API Documentation Generator.
 *
 * تثبيت:
 *   composer require --dev knuckleswtf/scribe
 *   php artisan vendor:publish --tag=scribe-config
 *   php artisan scribe:generate
 *
 * الناتج يُولَّد في public/docs/ ويُصبح متاحاً على /docs.
 *
 * الاستخدام:
 *   - Annotate controller methods بـ @group, @bodyParam, @response
 *   - أو استخدم Form Request rules() — Scribe يُولّد body params تلقائياً
 */

use Knuckles\Scribe\Extracting\Strategies;

return [

    'theme' => 'default',

    'title' => 'منصة قيمّ — API Documentation',

    'description' => 'توثيق API لتطبيق منصة قيمّ (mobile + integrations).',

    'base_url' => env('APP_URL'),

    'routes' => [
        [
            'match' => [
                'prefixes' => ['api/*'],
                'domains' => ['*'],
                'versions' => ['v1'],
            ],
            'include' => [
                // فقط API endpoints — استبعاد web routes
            ],
            'exclude' => [
                // استبعاد debug endpoints
                'GET api/v1/internal/*',
            ],
            'apply' => [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'authentication' => [
                    'enabled' => true,
                    'in_query' => false,
                    'name' => 'Authorization',
                    'use_value' => env('SCRIBE_AUTH_KEY'),
                    'placeholder' => 'Bearer {YOUR_AUTH_TOKEN}',
                ],
            ],
        ],
    ],

    'type' => 'static',

    'static' => [
        'output_path' => 'public/docs',
    ],

    'try_it_out' => [
        'enabled' => true,
        'base_url' => null,
        'use_csrf' => false,
    ],

    'auth' => [
        'enabled' => true,
        'default' => false,
        'in' => 'bearer',
        'name' => 'Authorization',
        'use_value' => env('SCRIBE_AUTH_KEY'),
        'placeholder' => '{YOUR_AUTH_TOKEN}',
        'extra_info' => 'احصل على الـ token عبر `POST /api/v1/login`.',
    ],

    'intro_text' => <<<MARKDOWN
        مرحباً بك في توثيق API لمنصة قيمّ.

        ## معلومات عامة

        - كل المسارات تحت `/api/v1/`
        - المصادقة عبر Laravel Sanctum (Bearer Token)
        - الردود بصيغة JSON موحّدة (انظر `App\Support\ApiResponse`)
        - Rate limit افتراضي: 60 طلب/دقيقة (راجع `throttle:api`)

        ## شكل الردود الموحّد

        ```json
        {
          "success": true|false,
          "data": {...} | [...] | null,
          "message": "...",
          "meta": {...}
        }
        ```

        ## رموز الحالة (HTTP Status Codes)

        | الكود | المعنى |
        |---|---|
        | 200 | نجاح |
        | 201 | تم الإنشاء |
        | 204 | لا محتوى |
        | 401 | غير مصادق |
        | 403 | لا تملك صلاحية |
        | 404 | غير موجود |
        | 422 | خطأ في بيانات الإدخال |
        | 429 | تجاوز Rate Limit |
        | 500 | خطأ سيرفر |
        MARKDOWN,

    'example_languages' => ['bash', 'javascript', 'php'],

    'postman' => [
        'enabled' => true,
        'overrides' => [],
    ],

    'openapi' => [
        'enabled' => true,
        'overrides' => [
            'info.version' => '1.0.0',
            'info.contact.email' => env('MAIL_FROM_ADDRESS'),
        ],
    ],

    'groups' => [
        'default' => 'General',
        'order' => [
            'Authentication',
            'Student',
            'Landing Page Content',
            'Health',
        ],
    ],

    'logo' => false,

    'last_updated' => 'Last updated: {date:F j Y}',

    'examples' => [
        'faker_seed' => 1234,
        'models_source' => ['factoryCreate', 'factoryMake', 'databaseFirst'],
    ],

    'strategies' => [
        'metadata' => [
            Strategies\Metadata\GetFromDocBlocks::class,
            Strategies\Metadata\GetFromMetadataAttributes::class,
        ],
        'urlParameters' => [
            Strategies\UrlParameters\GetFromLaravelAPI::class,
            Strategies\UrlParameters\GetFromUrlParamAttribute::class,
            Strategies\UrlParameters\GetFromUrlParamTag::class,
        ],
        'queryParameters' => [
            Strategies\QueryParameters\GetFromFormRequest::class,
            Strategies\QueryParameters\GetFromInlineValidator::class,
            Strategies\QueryParameters\GetFromQueryParamAttribute::class,
            Strategies\QueryParameters\GetFromQueryParamTag::class,
        ],
        'headers' => [
            Strategies\Headers\GetFromHeaderAttribute::class,
            Strategies\Headers\GetFromHeaderTag::class,
            [
                'override',
                ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
            ],
        ],
        'bodyParameters' => [
            Strategies\BodyParameters\GetFromFormRequest::class,
            Strategies\BodyParameters\GetFromInlineValidator::class,
            Strategies\BodyParameters\GetFromBodyParamAttribute::class,
            Strategies\BodyParameters\GetFromBodyParamTag::class,
        ],
        'responses' => [
            Strategies\Responses\UseResponseAttributes::class,
            Strategies\Responses\UseTransformerTags::class,
            Strategies\Responses\UseApiResourceTags::class,
            Strategies\Responses\UseResponseTag::class,
            Strategies\Responses\UseResponseFileTag::class,
            Strategies\Responses\ResponseCalls::class,
        ],
        'responseFields' => [
            Strategies\ResponseFields\GetFromResponseFieldAttribute::class,
            Strategies\ResponseFields\GetFromResponseFieldTag::class,
        ],
    ],
];
