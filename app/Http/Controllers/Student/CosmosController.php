<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\View\View;

class CosmosController extends Controller
{
    public function __invoke(): View
    {
        $items = Article::published()
            ->with('author')
            ->orderByDesc('created_at')
            ->limit(24)
            ->get();

        return view('student.cosmos', [
            'items' => $items,
            'tabs' => ['ALL', 'ARTICLE', 'BOOK', 'SUMMARY'],
        ]);
    }
}