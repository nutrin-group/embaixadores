<?php

namespace App\Http\Controllers;

use App\Models\AmbassadorPost;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class AmbassadorPostController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        // Log para debug
        Log::info('Buscando posts', [
            'month' => $month,
            'year' => $year,
            'user_id' => auth()->id()
        ]);

        $posts = AmbassadorPost::where('user_id', auth()->id())
            ->whereYear('post_date', $year)
            ->whereMonth('post_date', $month)
            ->orderBy('post_date', 'desc')
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'type' => $post->type,
                    'post_url' => $post->post_url,
                    'post_date' => $post->post_date->format('Y-m-d'),
                    'created_at' => $post->created_at->format('Y-m-d H:i:s')
                ];
            });

        // Log para debug
        Log::info('Posts encontrados', [
            'count' => $posts->count(),
            'posts' => $posts->toArray()
        ]);

        return Inertia::render('Ambassador/Posts/Index', [
            'posts' => $posts,
            'currentMonth' => $month,
            'currentYear' => $year,
            'debug' => [
                'total_posts' => $posts->count(),
                'query_month' => $month,
                'query_year' => $year,
                'user_id' => auth()->id()
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:reels,feed,stories',
            'post_url' => 'required|url',
            'post_date' => 'required|date',
        ]);

        $validated['user_id'] = auth()->id();

        // Log para debug
        Log::info('Criando novo post', $validated);

        $post = AmbassadorPost::create($validated);

        // Log para debug
        Log::info('Post criado', ['post' => $post->toArray()]);

        return redirect()->back()->with('success', 'Post registrado com sucesso!');
    }

    public function destroy(AmbassadorPost $ambassadorPost)
    {
        if ($ambassadorPost->user_id !== auth()->id()) {
            abort(403);
        }

        $ambassadorPost->delete();

        return redirect()->back()->with('success', 'Post removido com sucesso!');
    }
}
