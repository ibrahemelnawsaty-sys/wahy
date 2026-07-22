<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Concept;
use App\Models\Lesson;
use App\Models\Value;
use Illuminate\Http\Request;

/**
 * @group Student
 *
 * مسارات تطبيق الطالب: لوحة التحكم، الأنشطة، الشارات، لوحة الصدارة.
 * كل المسارات تتطلب Bearer Token + دور `student`.
 *
 * @authenticated
 */
class StudentApiController extends Controller
{
    /**
     * إحصائيات لوحة الطالب.
     *
     * يُرجع إجمالي النقاط والعملات والشارات + آخر 5 تسليمات.
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "stats": {
     *       "total_points": 450,
     *       "total_coins": 90,
     *       "badges_count": 3,
     *       "completed_activities": 12,
     *       "pending_activities": 2,
     *       "current_streak": 5
     *     },
     *     "recent_activities": [
     *       {"id": 42, "activity_id": 10, "title": "اختبار الصدق", "status": "approved", "score": 85, "submitted_at": "2026-05-10 14:30"}
     *     ]
     *   }
     * }
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();

        $stats = [
            'total_points' => (int) $user->points()->sum('points'),
            'total_coins' => (int) $user->coins()->sum('coins'),
            'badges_count' => $user->badges()->count(),
            'completed_activities' => $user->activitySubmissions()->whereIn('status', \App\Models\ActivitySubmission::DONE_STATUSES)->count(),
            'pending_activities' => $user->activitySubmissions()->where('status', 'pending')->count(),
            'current_streak' => $user->streak?->current_streak ?? 0,
        ];

        $recentActivities = $user->activitySubmissions()
            ->with(['activity.lesson.concept.value'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($submission) {
                return [
                    'id' => $submission->id,
                    'activity_id' => $submission->activity_id,
                    'title' => $submission->activity->title,
                    'status' => $submission->status,
                    'score' => $submission->score,
                    'submitted_at' => $submission->created_at->format('Y-m-d H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'recent_activities' => $recentActivities,
            ],
        ], 200);
    }

    /**
     * شجرة القيم والمفاهيم والدروس.
     *
     * يُرجع التركيب الهرمي: Value → Concept → Lesson → Activity
     * يُستخدم لرسم الـ values tree في الواجهة.
     *
     * @response 200 scenario="success" {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "الصدق",
     *       "description": "...",
     *       "concepts": [
     *         {"id": 5, "name": "الأمانة", "lessons_count": 3}
     *       ]
     *     }
     *   ]
     * }
     */
    public function valuesTree(Request $request)
    {
        $values = Value::with(['concepts.lessons'])->get();

        $tree = $values->map(function ($value) {
            return [
                'id' => $value->id,
                'title' => $value->name,
                'icon' => $value->icon,
                'image' => $value->image,
                'concepts_count' => $value->concepts->count(),
                'concepts' => $value->concepts->map(function ($concept) {
                    return [
                        'id' => $concept->id,
                        'title' => $concept->name,
                        'lessons_count' => $concept->lessons->count(),
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $tree,
        ], 200);
    }

    /**
     * Get Available Activities
     */
    public function activities(Request $request)
    {
        $user = $request->user();

        // بوّابة النشر: لا تُسرَّب إلا الأنشطة المنشورة مباشرةً لمدرسة الطالب (يستبدل بوّابة الاعتماد)
        $query = Activity::with(['lesson.concept.value'])
            ->where('status', 'active')
            ->visibleToStudent($user->school_id, $user->classrooms->pluck('id')->all());

        // بوّابة القيمة (اتّساقًا مع details/submit والويب): لا تُدرَج أنشطةٌ تحت قيمة أخفتها
        // المدرسة عبر school_active_values — كي لا تظهر بياناتها الوصفيّة في قائمة الجوّال ثم
        // يُرفَض فتحُها/تسليمُها بـ403. أنشطة بلا قيمة (بلا درس/مفهوم/قيمة) تمرّ (لا قيد قيمة).
        if ($user->school_id) {
            $visibleValueIds = \App\Models\Value::visibleForSchool($user->school_id)->pluck('id')->all();
            $query->where(function ($q) use ($visibleValueIds) {
                $q->whereDoesntHave('lesson.concept')
                    ->orWhereHas('lesson.concept', function ($c) use ($visibleValueIds) {
                        $c->whereNull('value_id')->orWhereIn('value_id', $visibleValueIds);
                    });
            });
        }

        // Filter by classroom if student
        if ($user->role === 'student') {
            $classroomIds = $user->classrooms->pluck('id')->toArray();
            $query->whereIn('classroom_id', $classroomIds);
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by difficulty
        if ($request->has('difficulty') && $request->difficulty) {
            $query->where('difficulty', $request->difficulty);
        }

        $activities = $query->latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => [
                'activities' => $activities->map(function ($activity) use ($user) {
                    $submission = $activity->submissions()
                        ->where('student_id', $user->id)
                        ->first();

                    return [
                        'id' => $activity->id,
                        'title' => $activity->title,
                        'description' => $activity->description,
                        'type' => $activity->type,
                        'difficulty' => $activity->difficulty,
                        'points' => $activity->points,
                        'coins' => $activity->coins,
                        'is_team_activity' => $activity->is_team_activity,
                        'lesson' => [
                            'id' => $activity->lesson->id,
                            'title' => $activity->lesson->title,
                            'value' => $activity->lesson->concept->value->title ?? null,
                        ],
                        'submission' => $submission ? [
                            'id' => $submission->id,
                            'status' => $submission->status,
                            'score' => $submission->score,
                            'submitted_at' => $submission->created_at->format('Y-m-d H:i'),
                        ] : null,
                    ];
                }),
                'pagination' => [
                    'total' => $activities->total(),
                    'per_page' => $activities->perPage(),
                    'current_page' => $activities->currentPage(),
                    'last_page' => $activities->lastPage(),
                ],
            ],
        ], 200);
    }

    /**
     * Get Activity Details
     */
    public function activityDetails($id, Request $request)
    {
        $user = $request->user();

        $activity = Activity::with(['lesson.concept.value', 'creator'])->findOrFail($id);

        // بوّابة الوصول الموحّدة (Activity::isAccessibleByStudent — نفس الويب تمامًا): نشط +
        // منشور مباشرةً لمدرسته أو مُسنَد لأحد فصوله + ضمن قيمة مفعّلة لمدرسته. توحيد المصدر
        // يغلق تجاوز الجوّال لبوّابة القيمة (كان يكشف الأسئلة/الإجابات على قيمة أخفتها المدرسة).
        if (! $activity->isAccessibleByStudent($user)) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول لهذا النشاط',
            ], 403);
        }

        $submission = $activity->submissions()
            ->where('student_id', $user->id)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $activity->id,
                'title' => $activity->title,
                'description' => $activity->description,
                'type' => $activity->type,
                'difficulty' => $activity->difficulty,
                'points' => $activity->points,
                'coins' => $activity->coins,
                'instructions' => $activity->instructions,
                'questions' => $activity->questions,
                'attachments' => $activity->attachments,
                'is_team_activity' => $activity->is_team_activity,
                'due_date' => $activity->due_date,
                'lesson' => $activity->lesson ? [
                    'id' => $activity->lesson->id,
                    'title' => $activity->lesson->title,
                    'content' => $activity->lesson->content,
                ] : null,
                'creator' => [
                    'name' => $activity->creator?->name,
                ],
                'submission' => $submission ? [
                    'id' => $submission->id,
                    'status' => $submission->status,
                    'score' => $submission->score,
                    'feedback' => $submission->feedback,
                    'submitted_at' => $submission->created_at->format('Y-m-d H:i'),
                ] : null,
            ],
        ], 200);
    }

    /**
     * Submit Activity
     */
    public function submitActivity($id, Request $request)
    {
        $user = $request->user();
        $activity = Activity::findOrFail($id);

        // بوّابة الوصول الموحّدة (Activity::isAccessibleByStudent — نفس الويب): لا يُقبَل تسليم/
        // منح نقاط إلا على نشاط نشط منشور مباشرةً لمدرسة الطالب أو مُسنَد لأحد فصوله + ضمن قيمة
        // مفعّلة لمدرسته (يُغلق قبول التسليم/منح النقاط على قيمة أخفتها المدرسة عبر الجوّال).
        if (! $activity->isAccessibleByStudent($user)) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول لهذا النشاط',
            ], 403);
        }

        $request->validate([
            'answers' => 'required|array',
            'file' => 'sometimes|file|max:10240', // 10MB
        ]);

        // #13 توحيد مع الويب: احترام حدّ المحاولات ومنع إعادة فتح تسليمٍ نهائيّ. الجوّال لا يُصحّح
        // آليًّا (يضبط pending دائمًا)، فنقصر الإعادة على الحالات غير المُصحَّحة/غير النهائيّة
        // (pending/needs_review/rejected) — لا نُعيد فتح completed (ناجح، سنُنزله بلا تصحيح) ولا
        // approved (معتمَد نهائيًّا). كان يحجب completed فقط ويتجاهل max_attempts (التفاف على الميزة).
        $maxAttempts = max(1, (int) ($activity->max_attempts ?? 1));

        $existing = ActivitySubmission::where('activity_id', $id)
            ->where('student_id', $user->id)
            ->first();

        if ($existing) {
            $resubmittable = in_array($existing->status, ['needs_review', 'rejected', 'pending'], true);
            if (! $resubmittable) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يمكن إعادة تسليم هذا النشاط',
                ], 400);
            }
            if ((int) ($existing->attempts ?? 1) >= $maxAttempts) {
                return response()->json([
                    'success' => false,
                    'message' => 'استنفدت عدد محاولاتك لهذا النشاط (' . $maxAttempts . ').',
                ], 400);
            }
        }

        $data = [
            'activity_id' => $id,
            'student_id' => $user->id,
            // العمود الفعلي 'answer' (مفرد، نصّي) — نُرمّزه JSON بنفس اصطلاح الويب
            'answer' => json_encode($request->answers, JSON_UNESCAPED_UNICODE),
            'status' => 'pending',
            'submitted_at' => now(),
        ];

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('submissions', 'public');
            $data['file_path'] = $path;
        }

        if ($existing) {
            $data['attempts'] = (int) ($existing->attempts ?? 1) + 1;
            $existing->update($data);
            $submission = $existing;
        } else {
            $data['attempts'] = 1;
            $submission = ActivitySubmission::create($data);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تقديم النشاط بنجاح',
            'data' => [
                'id' => $submission->id,
                'status' => $submission->status,
            ],
        ], 200);
    }

    /**
     * Get Badges
     */
    public function badges(Request $request)
    {
        $user = $request->user();

        $userBadges = $user->badges()->get()->map(function ($badge) {
            return [
                'id' => $badge->id,
                'name' => $badge->name,
                'description' => $badge->description,
                'icon' => $badge->icon,
                'earned_at' => $badge->pivot->created_at->format('Y-m-d H:i'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'badges' => $userBadges,
                'total_count' => $userBadges->count(),
            ],
        ], 200);
    }

    /**
     * Get Leaderboard
     */
    public function leaderboard(Request $request)
    {
        $user = $request->user();

        $students = \App\Models\User::where('role', 'student')
            ->where('school_id', $user->school_id)
            ->withSum('points', 'points')
            ->orderBy('points_sum_points', 'desc')
            ->take(50)
            ->get()
            ->map(function ($student, $index) {
                return [
                    'rank' => $index + 1,
                    'id' => $student->id,
                    'name' => $student->name,
                    'avatar' => $student->avatar,
                    'points' => (int) ($student->points_sum_points ?? 0),
                ];
            });

        // Find user's rank
        $allStudents = \App\Models\User::where('role', 'student')
            ->where('school_id', $user->school_id)
            ->withSum('points', 'points')
            ->orderBy('points_sum_points', 'desc')
            ->pluck('id');

        // التعامل الصحيح مع false (المستخدم خارج الترتيب)
        $userIdx = $allStudents->search($user->id, true);
        $userRank = $userIdx === false ? null : $userIdx + 1;

        return response()->json([
            'success' => true,
            'data' => [
                'leaderboard' => $students,
                'user_rank' => $userRank,
                'user_points' => (int) $user->points()->sum('points'),
            ],
        ], 200);
    }
}
