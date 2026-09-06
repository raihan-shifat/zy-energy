<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\News;

class NewsController extends Controller
{
    /**
     * News (renamed "Blog") listing page.
     */
    public function index()
    {
        $posts = News::with('translations')
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->paginate(9);

        return view('themes.xylo.news-index', compact('posts'));
    }

    /**
     * Single news post.
     */
    public function show($slug)
    {
        $post = News::with('translations')
            ->where('slug', $slug)
            ->where('status', 1)
            ->firstOrFail();

        return view('themes.xylo.news-show', compact('post'));
    }
}
