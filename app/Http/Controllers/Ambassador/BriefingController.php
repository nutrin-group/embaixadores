<?php

namespace App\Http\Controllers\Ambassador;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BriefingController extends Controller
{
    public function index()
    {
        $briefings = [
            [
                'id' => 1,
                'title' => 'Como Fazer Stories Efetivos',
                'description' => 'Guia completo para criar stories que convertem',
                'content' => 'Conteúdo detalhado do briefing...',
                'type' => 'stories'
            ],
            [
                'id' => 2,
                'title' => 'Roteiro para Reels',
                'description' => 'Template de roteiro para criar reels engajadores',
                'content' => 'Conteúdo detalhado do briefing...',
                'type' => 'reels'
            ],
            [
                'id' => 3,
                'title' => 'Guia de Fotos para Feed',
                'description' => 'Como fazer fotos profissionais para o feed',
                'content' => 'Conteúdo detalhado do briefing...',
                'type' => 'feed'
            ]
        ];

        return Inertia::render('Ambassador/Briefings', [
            'briefings' => $briefings
        ]);
    }
}
