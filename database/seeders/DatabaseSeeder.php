<?php

namespace Database\Seeders;

use App\Enums\InstructorStatus;
use App\Enums\JourneyLevel;
use App\Enums\JourneyStatus;
use App\Enums\LibraryItemType;
use App\Enums\NotificationType;
use App\Enums\Role;
use App\Models\Article;
use App\Models\Enrollment;
use App\Models\ForumPost;
use App\Models\ForumThread;
use App\Models\InstructorProfile;
use App\Models\Journey;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Start from a clean slate so re-seeding is always deterministic.
        \Illuminate\Support\Facades\DB::table('lesson_progress')->delete();
        \Illuminate\Support\Facades\DB::table('enrollments')->delete();
        \Illuminate\Support\Facades\DB::table('notifications')->delete();
        \Illuminate\Support\Facades\DB::table('forum_posts')->delete();
        \Illuminate\Support\Facades\DB::table('forum_threads')->delete();
        \Illuminate\Support\Facades\DB::table('lesson_assets')->delete();
        \Illuminate\Support\Facades\DB::table('lessons')->delete();
        \Illuminate\Support\Facades\DB::table('modules')->delete();
        \Illuminate\Support\Facades\DB::table('articles')->delete();
        \Illuminate\Support\Facades\DB::table('certificates')->delete();
        \Illuminate\Support\Facades\DB::table('achievements')->delete();
        \Illuminate\Support\Facades\DB::table('journeys')->delete();
        \Illuminate\Support\Facades\DB::table('instructor_profiles')->delete();
        \Illuminate\Support\Facades\DB::table('users')->delete();

        $admin = $this->user('admin@madarek.app', 'سارة المديرة', Role::ADMIN, 'مديرة المنصة');
        $instructor = $this->user('instructor@madarek.app', 'أحمد الرشد', Role::INSTRUCTOR, 'مهندس برمجيات');
        $instructor2 = $this->user('layla@madarek.app', 'ليلى بن صالح', Role::INSTRUCTOR, 'محللة بيانات');
        $student = $this->user('student@madarek.app', 'عباس الحداد', Role::STUDENT, 'متعلّم دائم');

        InstructorProfile::create([
            'user_id' => $instructor->id,
            'status' => InstructorStatus::VERIFIED,
            'expertise' => 'JavaScript, React, Node.js, Web Performance',
            'verified_at' => now(),
        ]);

        InstructorProfile::create([
            'user_id' => $instructor2->id,
            'status' => InstructorStatus::VERIFIED,
            'expertise' => 'Python, Data Analysis, Machine Learning',
            'verified_at' => now(),
        ]);

        $web = $this->journey(
            'web-development-orbit',
            'مدار تطوير الويب',
            'Web Development Orbit',
            'من الصفر إلى أول تطبيق ويب كامل',
            JourneyLevel::BEGINNER,
            $instructor,
            320,
            [
                ['التهيئة للفضاء', [
                    ['لماذا نبرمج؟', 12, true],
                    ['إعداد بيئة العمل', 18, false],
                    ['أول سطر في البرمجة', 15, true],
                ]],
                ['أساسيات الويب', [
                    ['HTML — هيكل الصفحة', 22, false],
                    ['CSS — التنسيق والفن', 26, false],
                    ['JavaScript — منطق الصفحة', 30, false],
                ]],
                ['أول مشروع تطبيقي', [
                    ['بناء صفحة شخصية', 35, false],
                    ['متجر مصغّر', 45, false],
                    ['مشروع التخرج', 80, false],
                ]],
            ],
            'رحلة عملية تنقلك من المبتدئ إلى مطوّر ويب واثق، عبر ثلاث محطات قصيرة ومركّزة تنتهي بمشروع حقيقي في محفظتك.'
        );

        $data = $this->journey(
            'data-analysis-orbit',
            'مدار تحليل البيانات',
            'Data Analysis Orbit',
            'حوّل الأرقام إلى قصص تُقنع',
            JourneyLevel::INTERMEDIATE,
            $instructor2,
            260,
            [
                ['أساسيات البيانات', [
                    ['من البيانات الخام إلى الوضوح', 20, true],
                    ['Python للتحليل', 30, false],
                ]],
                ['التحليل والرؤية', [
                    ['تنظيف البيانات', 28, false],
                    ['التحليل الإحصائي', 35, false],
                    ['التصوير البياني', 30, false],
                    ['سرد القصة', 25, false],
                ]],
            ],
            'كيف تتحول الأرقام إلى قرارات؟ تتعلّم المنهجية العملية من التنظيف حتى السرد البصري الذي يقنع.'
        );

        $ux = $this->journey(
            'ux-design-orbit',
            'مدار تجربة المستخدم',
            'UX Design Orbit',
            'صمّم تجارب تُحَبّ وتُستخدم',
            JourneyLevel::ADVANCED,
            $instructor,
            200,
            [
                ['أساسيات التجربة', [
                    ['من فهم المستخدم إلى Problem Space', 22, true],
                    ['أبحاث المستخدمين', 32, false],
                ]],
                ['التصميم والتقييم', [
                    ['Wireframes والنماذج', 28, false],
                    ['أنظمة التصميم', 34, false],
                    ['اختبار الاستخدام', 30, false],
                ]],
            ],
            'من فهم المستخدم إلى تصميم واجهات قابلة للاختبار — دورة تصميم متكاملة.'
        );

        // ── Library ──
        $articles = [
            ['learning-design-101', 'أساسيات تصميم التعلم', 'لماذا تفشل بعض الدورات رغم محتواها الغني؟', 'ARTICLE'],
            ['focus-and-flow', 'التركيز والتدفق في جلسة التعلم', 'كيف تحافظ على انتباه المتعلم دون إرهاق.', 'ARTICLE'],
            ['deep-work-notes', 'ملاحظات العمل العميق', 'خلاصة مختارة حول التعلّم المركّز.', 'SUMMARY'],
            ['clean-code-primer', 'مدخل إلى الكود النظيف', 'مبادئ كتابة كود قابل للصيانة.', 'BOOK'],
            ['sql-for-analysts', 'SQL للمحللين', 'من SELECT إلى JOIN بثقة.', 'ARTICLE'],
        ];

        foreach ($articles as [$slug, $title, $excerpt, $type]) {
            Article::create([
                'slug' => $slug,
                'title' => $title,
                'excerpt' => $excerpt,
                'content' => $excerpt . "\n\n" . str_repeat("محتوى تجريبي لعرض واجهة القراءة. ", 12),
                'type' => $type,
                'language' => 'ar',
                'read_min' => random_int(4, 12),
                'published' => true,
                'author_id' => $instructor->id,
            ]);
        }

        // ── Forum ──
        $t1 = ForumThread::create([
            'title' => 'كيف بدأت رحلةلتك في البرمجة؟',
            'pinned' => true,
            'author_id' => $student->id,
            'journey_id' => $web->id,
        ]);
        ForumPost::create([
            'content' => 'بدأت من الصفر تماماً بعد سنة كاملة من التردد. أهم نقطة كانت أن أبدأ بأي شيء ولو صفحة HTML بسيطة.',
            'thread_id' => $t1->id,
            'author_id' => $student->id,
        ]);
        ForumPost::create([
            'content' => 'نصيحة ممتازة. التردد هو أكبر معوّق — ابدأ اليوم ولو بشيء صغير.',
            'thread_id' => $t1->id,
            'author_id' => $instructor->id,
        ]);

        $t2 = ForumThread::create([
            'title' => 'أفضل مصادر JavaScript للمبتدئين؟',
            'author_id' => $instructor2->id,
            'journey_id' => $web->id,
        ]);
        ForumPost::create([
            'content' => 'أنصح دائماً بالبدء بالكتب ثم التمارين، لا بمشاهدة الفيديو فقط.',
            'thread_id' => $t2->id,
            'author_id' => $instructor2->id,
        ]);

        // ── Student activity: one active enrollment with progress ──
        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'journey_id' => $web->id,
            'enrolled_at' => now()->subDays(6),
        ]);

        $webLessons = Lesson::whereIn('module_id', $web->modules->pluck('id'))->orderBy('id')->get();
        foreach ($webLessons->take(3) as $i => $lesson) {
            LessonProgress::create([
                'user_id' => $student->id,
                'lesson_id' => $lesson->id,
                'completed' => true,
                'watched_seconds' => ($i + 1) * 900,
                'completed_at' => now()->subDays(5 - $i),
            ]);
        }

        Notification::create([
            'user_id' => $student->id,
            'type' => NotificationType::JOURNEY,
            'title' => 'رحلةلتك بانتظارك',
            'body' => 'واصل من حيث توقفت في مدار تطوير الويب.',
            'url' => '/student/paths/web-development-orbit',
            'created_at' => now()->subDays(2),
        ]);
        Notification::create([
            'user_id' => $student->id,
            'type' => NotificationType::FORUM,
            'title' => 'رد جديد على سؤالك',
            'body' => 'أحمد الرشد ردّ على موضوعك في مجلس مدارك.',
            'url' => '/instructor/council',
            'read' => true,
            'created_at' => now()->subDays(1),
        ]);
        Notification::create([
            'user_id' => $admin->id,
            'type' => NotificationType::SYSTEM,
            'title' => 'مراجعة المدرّسين',
            'body' => 'هناك طلبات اعتماد مدرّسين بانتظار المراجعة.',
            'created_at' => now(),
        ]);

        unset($enrollment);
        $this->command?->info('✓ Madarek seeded: 4 users, 3 journeys, ' . Lesson::count() . ' lessons, ' . Article::count() . ' library items.');
    }

    private function user(string $email, string $name, Role $role, string $headline): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password123'),
                'role' => $role,
                'headline' => $headline,
                'email_verified_at' => now(),
            ]
        );
    }

    private function journey(
        string $slug,
        string $title,
        string $titleEn,
        string $tagline,
        JourneyLevel $level,
        User $instructor,
        int $durationMin,
        array $curriculum,
        string $description
    ): Journey {
        $journey = Journey::create([
            'slug' => $slug,
            'title' => $title,
            'title_en' => $titleEn,
            'tagline' => $tagline,
            'description' => $description,
            'level' => $level,
            'status' => JourneyStatus::PUBLISHED,
            'is_free' => true,
            'price' => 0,
            'language' => 'ar',
            'duration_min' => $durationMin,
            'instructor_id' => $instructor->id,
        ]);

        foreach ($curriculum as $moduleIndex => [$moduleTitle, $lessons]) {
            $module = Module::create([
                'title' => $moduleTitle,
                'order' => $moduleIndex,
                'journey_id' => $journey->id,
            ]);

            foreach ($lessons as $lessonIndex => [$lessonTitle, $lessonDuration, $isPreview]) {
                Lesson::create([
                    'title' => $lessonTitle,
                    'description' => 'شرحلة عملي ضمن ' . $moduleTitle . ' — ' . $lessonTitle,
                    'video_url' => null,
                    'duration_min' => $lessonDuration,
                    'order' => $lessonIndex,
                    'is_preview' => $isPreview,
                    'module_id' => $module->id,
                ]);
            }
        }

        return $journey->fresh();
    }
}