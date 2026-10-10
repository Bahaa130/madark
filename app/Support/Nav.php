<?php

namespace App\Support;

/**
 * Workspace navigation, mirroring src/lib/nav-items.ts.
 *
 * Declared as plain data (icon *names* + dotted label *keys*) so a single
 * definition can drive the sidebar, the bottom nav and the active-state
 * checks. Icons and labels resolve in the view layer.
 */
class Nav
{
    /** Student Space — /student */
    public const STUDENT = [
        ['key' => 'orbit', 'href' => '/student', 'icon' => 'orbit', 'label' => 'nav.student.orbit', 'exact' => true],
        ['key' => 'paths', 'href' => '/student/paths', 'icon' => 'route', 'label' => 'nav.student.paths'],
        ['key' => 'cosmos', 'href' => '/student/cosmos', 'icon' => 'telescope', 'label' => 'nav.student.cosmos'],
        ['key' => 'lighthouse', 'href' => '/student/lighthouse', 'icon' => 'lightbulb', 'label' => 'nav.student.lighthouse'],
        ['key' => 'profile', 'href' => '/student/profile', 'icon' => 'user', 'label' => 'nav.student.profile'],
        ['key' => 'compass', 'href' => '/student/compass', 'icon' => 'compass', 'label' => 'nav.student.compass'],
    ];

    /** Mobile bottom navigation: the five primary destinations. */
    public const STUDENT_BOTTOM = ['orbit', 'paths', 'cosmos', 'lighthouse', 'profile'];

    /** Instructor Space — /instructor */
    public const INSTRUCTOR = [
        ['key' => 'dashboard', 'href' => '/instructor', 'icon' => 'dashboard', 'label' => 'nav.instructor.dashboard', 'exact' => true],
        ['key' => 'journeys', 'href' => '/instructor/journeys', 'icon' => 'route', 'label' => 'nav.instructor.journeys'],
        ['key' => 'bag', 'href' => '/instructor/bag', 'icon' => 'package', 'label' => 'nav.instructor.bag'],
        ['key' => 'quizzes', 'href' => '/instructor/quizzes', 'icon' => 'award', 'label' => 'nav.instructor.quizzes'],
        ['key' => 'council', 'href' => '/instructor/council', 'icon' => 'messages', 'label' => 'nav.instructor.council'],
    ];

    /** Admin Space — /admin */
    public const ADMIN = [
        ['key' => 'control', 'href' => '/admin', 'icon' => 'shield', 'label' => 'nav.admin.control', 'exact' => true],
    ];

    /**
     * Nav for a role, defaulting to the student workspace.
     */
    public static function forRole(string $role): array
    {
        return match ($role) {
            'ADMIN' => self::ADMIN,
            'INSTRUCTOR' => self::INSTRUCTOR,
            default => self::STUDENT,
        };
    }

    /**
     * Mirrors isActivePath(): exact match for root items, prefix otherwise.
     */
    public static function isActive(string $pathname, array $item): bool
    {
        if (! empty($item['exact'])) {
            return $pathname === $item['href'];
        }

        return str_starts_with($pathname, $item['href']);
    }

    public static function bottomFor(string $role): array
    {
        $nav = self::forRole($role);

        if ($role === 'ADMIN' || $role === 'INSTRUCTOR') {
            return $nav;
        }

        return array_values(array_filter(
            $nav,
            fn (array $item) => in_array($item['key'], self::STUDENT_BOTTOM, true)
        ));
    }
}