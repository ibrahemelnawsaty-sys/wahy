<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    /** Cache TTL — دقيقتان كسقف أمان (الطزاجة الفورية تأتي من رفع الإصدار عند منح النقاط) */
    private const CACHE_TTL = 120;

    /**
     * إصدار كاش الصدارة — يُرفَع في Point::created عند منح نقاط، فتُبطَل كل المفاتيح القديمة فوراً.
     */
    private function lbVersion(): string
    {
        return (string) Cache::get('lb:ver', 1);
    }

    /**
     * لوحة الصدارة الرئيسية - جميع الفئات
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $schoolId = $user?->school_id;
        $tab = $request->get('tab', 'students');

        $data = [
            'students' => $this->getStudentLeaderboard(10, $schoolId),
            'teachers' => $this->getTeacherLeaderboard(10, $schoolId),
            'parents' => $this->getParentLeaderboard(10, $schoolId),
            'schools' => $this->getSchoolLeaderboard(10),
        ];

        $userRank = $this->getCurrentUserRank($user);

        return view('leaderboard.index', compact('data', 'tab', 'userRank'));
    }

    public function students(Request $request)
    {
        $user = Auth::user();
        $schoolId = $request->get('school_id', $user?->school_id);
        $classroomId = $request->get('classroom_id');
        $scope = $request->get('scope', 'school');
        $limit = (int) $request->get('limit', 50);

        $leaderboard = $this->getStudentLeaderboard($limit, $schoolId, $classroomId, $scope);

        $userRank = null;
        if ($user && $user->role === 'student') {
            $userRank = $this->getUserRankInCategory($user->id, 'student', $schoolId);
        }

        if ($request->wantsJson()) {
            return response()->json(['leaderboard' => $leaderboard, 'user_rank' => $userRank]);
        }

        return view('leaderboard.students', compact('leaderboard', 'userRank', 'scope'));
    }

    public function teachers(Request $request)
    {
        $user = Auth::user();
        $schoolId = $request->get('school_id', $user?->school_id);
        $scope = $request->get('scope', 'school');
        $limit = (int) $request->get('limit', 50);

        $leaderboard = $this->getTeacherLeaderboard($limit, $schoolId, $scope);

        $userRank = null;
        if ($user && $user->role === 'teacher') {
            $userRank = $this->getUserRankInCategory($user->id, 'teacher', $schoolId);
        }

        if ($request->wantsJson()) {
            return response()->json(['leaderboard' => $leaderboard, 'user_rank' => $userRank]);
        }

        return view('leaderboard.teachers', compact('leaderboard', 'userRank', 'scope'));
    }

    public function parents(Request $request)
    {
        $user = Auth::user();
        $schoolId = $request->get('school_id', $user?->school_id);
        $scope = $request->get('scope', 'school');
        $limit = (int) $request->get('limit', 50);

        $leaderboard = $this->getParentLeaderboard($limit, $schoolId, $scope);

        $userRank = null;
        if ($user && $user->role === 'parent') {
            $userRank = $this->getUserRankInCategory($user->id, 'parent', $schoolId);
        }

        if ($request->wantsJson()) {
            return response()->json(['leaderboard' => $leaderboard, 'user_rank' => $userRank]);
        }

        return view('leaderboard.parents', compact('leaderboard', 'userRank', 'scope'));
    }

    public function schools(Request $request)
    {
        $user = Auth::user();
        $period = $request->get('period', 'all');
        $limit = (int) $request->get('limit', 50);

        $leaderboard = $this->getSchoolLeaderboard($limit, $period);

        $schoolRank = null;
        if ($user && $user->school_id) {
            $schoolRank = $this->getSchoolRank($user->school_id);
        }

        if ($request->wantsJson()) {
            return response()->json(['leaderboard' => $leaderboard, 'school_rank' => $schoolRank]);
        }

        return view('leaderboard.schools', compact('leaderboard', 'schoolRank', 'period'));
    }

    /**
     * لوحة صدارة الطلاب — استعلام واحد + ORDER BY على SQL + Cache.
     */
    private function getStudentLeaderboard(int $limit, ?int $schoolId = null, ?int $classroomId = null, string $scope = 'school'): array
    {
        $cacheKey = 'lb:students:' . $this->lbVersion() . ':' . md5("$limit|$schoolId|$classroomId|$scope");

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($limit, $schoolId, $classroomId, $scope) {
            $query = User::query()
                ->where('users.role', 'student')
                ->where('users.status', 'active')
                ->withSum('points as total_points', 'points')
                ->with('school:id,name')
                ->select('users.id', 'users.name', 'users.avatar', 'users.school_id');

            if ($scope === 'school' && $schoolId) {
                $query->where('users.school_id', $schoolId);
            }

            if ($classroomId) {
                $query->whereHas('classrooms', fn ($q) => $q->where('classrooms.id', $classroomId));
            }

            $students = $query->orderByDesc('total_points')->limit($limit)->get();

            $rank = 1;

            return $students->map(function ($s) use (&$rank) {
                return [
                    'rank' => $rank++,
                    'id' => $s->id,
                    'name' => $s->name,
                    'avatar' => $this->avatarUrl($s->avatar, $s->name),
                    'points' => (int) ($s->total_points ?? 0),
                    'school' => $s->school?->name ?? '-',
                    'badge' => $this->getRankBadge($rank - 1),
                ];
            })->toArray();
        });
    }

    /**
     * لوحة صدارة المعلمين — استعلام واحد مع students_count subquery.
     */
    private function getTeacherLeaderboard(int $limit, ?int $schoolId = null, string $scope = 'school'): array
    {
        $cacheKey = 'lb:teachers:' . $this->lbVersion() . ':' . md5("$limit|$schoolId|$scope");

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($limit, $schoolId, $scope) {
            // عدد طلاب المعلم عبر subquery (يدعم MySQL)
            $studentsCountSub = DB::table('classroom_student as cs')
                ->join('classrooms as c', 'cs.classroom_id', '=', 'c.id')
                ->whereColumn('c.teacher_id', 'users.id')
                ->selectRaw('COUNT(DISTINCT cs.student_id)');

            // نقاط المعلم من جدول teacher_points (لا من جدول points الخاص بالطلاب) — Issue صدارة صفرية
            $pointsSub = DB::table('teacher_points')
                ->whereColumn('teacher_points.teacher_id', 'users.id')
                ->selectRaw('COALESCE(SUM(points), 0)');

            $query = User::query()
                ->where('users.role', 'teacher')
                ->where('users.status', 'active')
                ->with('school:id,name')
                ->select('users.id', 'users.name', 'users.avatar', 'users.school_id')
                ->selectSub($pointsSub, 'total_points')
                ->selectSub($studentsCountSub, 'students_count');

            if ($scope === 'school' && $schoolId) {
                $query->where('users.school_id', $schoolId);
            }

            $teachers = $query->orderByDesc('total_points')->limit($limit)->get();

            $rank = 1;

            return $teachers->map(function ($t) use (&$rank) {
                return [
                    'rank' => $rank++,
                    'id' => $t->id,
                    'name' => $t->name,
                    'avatar' => $this->avatarUrl($t->avatar, $t->name),
                    'points' => (int) ($t->total_points ?? 0),
                    'school' => $t->school?->name ?? '-',
                    'students_count' => (int) ($t->students_count ?? 0),
                    'badge' => $this->getRankBadge($rank - 1),
                ];
            })->toArray();
        });
    }

    /**
     * لوحة صدارة أولياء الأمور — استعلام واحد مع children_count subquery.
     */
    private function getParentLeaderboard(int $limit, ?int $schoolId = null, string $scope = 'school'): array
    {
        $cacheKey = 'lb:parents:' . $this->lbVersion() . ':' . md5("$limit|$schoolId|$scope");

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($limit, $schoolId, $scope) {
            $childrenCountSub = DB::table('parent_student')
                ->whereColumn('parent_student.parent_id', 'users.id')
                ->selectRaw('COUNT(*)');

            // نقاط ولي الأمر من جدول parent_points (لا من جدول points الخاص بالطلاب)
            $pointsSub = DB::table('parent_points')
                ->whereColumn('parent_points.parent_id', 'users.id')
                ->selectRaw('COALESCE(SUM(points), 0)');

            $query = User::query()
                ->where('users.role', 'parent')
                ->where('users.status', 'active')
                ->select('users.id', 'users.name', 'users.avatar', 'users.school_id')
                ->selectSub($pointsSub, 'total_points')
                ->selectSub($childrenCountSub, 'children_count');

            if ($scope === 'school' && $schoolId) {
                $query->where('users.school_id', $schoolId);
            }

            $parents = $query->orderByDesc('total_points')->limit($limit)->get();

            $rank = 1;

            return $parents->map(function ($p) use (&$rank) {
                return [
                    'rank' => $rank++,
                    'id' => $p->id,
                    'name' => $p->name,
                    'avatar' => $this->avatarUrl($p->avatar, $p->name),
                    'points' => (int) ($p->total_points ?? 0),
                    'children_count' => (int) ($p->children_count ?? 0),
                    'badge' => $this->getRankBadge($rank - 1),
                ];
            })->toArray();
        });
    }

    /**
     * لوحة صدارة المدارس — استعلام واحد مع 3 subqueries (نقاط/طلاب/معلمين).
     */
    private function getSchoolLeaderboard(int $limit, string $scope = 'all'): array
    {
        $cacheKey = 'lb:schools:' . $this->lbVersion() . ':' . md5("$limit|$scope");

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($limit) {
            // SUM(points) عبر join على points + users داخل subquery
            $totalPointsSub = DB::table('points')
                ->join('users', 'users.id', '=', 'points.user_id')
                ->whereColumn('users.school_id', 'schools.id')
                ->where('users.role', 'student')
                ->selectRaw('COALESCE(SUM(points.points), 0)');

            $studentsCountSub = DB::table('users')
                ->whereColumn('users.school_id', 'schools.id')
                ->where('users.role', 'student')
                ->selectRaw('COUNT(*)');

            $teachersCountSub = DB::table('users')
                ->whereColumn('users.school_id', 'schools.id')
                ->where('users.role', 'teacher')
                ->selectRaw('COUNT(*)');

            $schools = School::query()
                ->where('status', 'active')
                ->select('id', 'name', 'logo')
                ->selectSub($totalPointsSub, 'total_points')
                ->selectSub($studentsCountSub, 'students_count')
                ->selectSub($teachersCountSub, 'teachers_count')
                ->orderByDesc('total_points')
                ->limit($limit)
                ->get();

            $rank = 1;

            return $schools->map(function ($s) use (&$rank) {
                return [
                    'rank' => $rank++,
                    'id' => $s->id,
                    'name' => $s->name,
                    'logo' => $s->logo ? asset('storage/app/public/data/' . ltrim($s->logo, '/')) : asset('images/default-school.png'),
                    'points' => (int) ($s->total_points ?? 0),
                    'students_count' => (int) ($s->students_count ?? 0),
                    'teachers_count' => (int) ($s->teachers_count ?? 0),
                    'badge' => $this->getRankBadge($rank - 1),
                ];
            })->toArray();
        });
    }

    private function getCurrentUserRank(?User $user): ?array
    {
        if (! $user) {
            return null;
        }

        return $this->getUserRankInCategory($user->id, $user->role, $user->school_id);
    }

    /**
     * ترتيب المستخدم في فئته — استعلام SQL واحد بدلاً من تحميل كل المستخدمين في PHP.
     */
    private function getUserRankInCategory(int $userId, string $role, ?int $schoolId = null): array
    {
        $cacheKey = 'lb:rank:' . $this->lbVersion() . ":{$role}:{$userId}:" . ($schoolId ?? 'all');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $role, $schoolId) {
            $userPoints = (int) DB::table('points')->where('user_id', $userId)->sum('points');

            // نختار العمود المُجمَّع فقط (users.id) لتفادي خطأ ONLY_FULL_GROUP_BY (1055)
            // على MySQL الصارم عند دمج SELECT * مع GROUP BY.
            $query = DB::table('users')
                ->select('users.id')
                ->leftJoin('points', 'points.user_id', '=', 'users.id')
                ->where('users.role', $role)
                ->where('users.status', 'active')
                ->groupBy('users.id')
                ->havingRaw('COALESCE(SUM(points.points), 0) > ?', [$userPoints]);

            if ($schoolId) {
                $query->where('users.school_id', $schoolId);
            }

            $rank = $query->get()->count() + 1;

            $user = User::select('id', 'name')->find($userId);

            return [
                'rank' => $rank,
                'points' => $userPoints,
                'name' => $user->name ?? '-',
                'badge' => $this->getRankBadge($rank),
            ];
        });
    }

    private function getSchoolRank(int $schoolId): array
    {
        $cacheKey = 'lb:school_rank:' . $this->lbVersion() . ":{$schoolId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($schoolId) {
            $school = School::select('id', 'name')->find($schoolId);

            $schoolPoints = (int) DB::table('points')
                ->join('users', 'users.id', '=', 'points.user_id')
                ->where('users.school_id', $schoolId)
                ->where('users.role', 'student')
                ->sum('points.points');

            $rank = School::where('status', 'active')
                ->whereRaw(
                    '(SELECT COALESCE(SUM(p.points), 0) FROM points p JOIN users u ON u.id = p.user_id WHERE u.school_id = schools.id AND u.role = ?) > ?',
                    ['student', $schoolPoints],
                )
                ->count() + 1;

            return [
                'rank' => $rank,
                'points' => $schoolPoints,
                'name' => $school->name ?? '-',
                'badge' => $this->getRankBadge($rank),
            ];
        });
    }

    /**
     * Helper: إرجاع URL الـ avatar مع fallback آمن لـ SVG data URI.
     * (يستخدم نفس آلية User::defaultAvatarDataUri لمنع broken-image).
     */
    private function avatarUrl(?string $path, ?string $name = null): string
    {
        if ($path) {
            // رابط خارجي مباشر
            if (str_starts_with($path, 'http')) {
                return $path;
            }

            // المسار المحفوظ نسبي لجذر قرص public المخصّص (storage/app/public/data/)
            // — نفس اصطلاح User::getAvatarUrlAttribute، وإلا كسرت كل الصور المرفوعة.
            return asset('storage/app/public/data/' . ltrim($path, '/'));
        }

        // fallback إلى SVG data URI (لا يحتاج ملف فعلي على القرص)
        $letter = mb_substr(trim((string) $name), 0, 1, 'UTF-8') ?: '؟';
        $letter = htmlspecialchars($letter, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">'
             . '<rect width="100" height="100" fill="#667eea"/>'
             . '<text x="50" y="62" font-family="Arial,Tahoma" font-size="50" fill="white" text-anchor="middle" font-weight="700">' . $letter . '</text>'
             . '</svg>';

        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }

    private function getRankBadge(int $rank): array
    {
        return match (true) {
            $rank === 1 => ['icon' => '🥇', 'color' => '#FFD700', 'label' => 'الأول'],
            $rank === 2 => ['icon' => '🥈', 'color' => '#C0C0C0', 'label' => 'الثاني'],
            $rank === 3 => ['icon' => '🥉', 'color' => '#CD7F32', 'label' => 'الثالث'],
            $rank <= 10 => ['icon' => '⭐', 'color' => '#10b981', 'label' => 'العشرة الأوائل'],
            $rank <= 50 => ['icon' => '🌟', 'color' => '#6366f1', 'label' => 'الخمسون الأوائل'],
            default => ['icon' => '💫', 'color' => '#64748b', 'label' => "#{$rank}"],
        };
    }
}
