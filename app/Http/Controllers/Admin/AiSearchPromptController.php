<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiSavedSearchPrompt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiSearchPromptController extends Controller
{
    public function index()
    {
        $prompts = AiSavedSearchPrompt::forUser(Auth::id())
            ->orderByDesc('is_favorite')
            ->orderByDesc('use_count')
            ->orderByDesc('updated_at')
            ->get(['id', 'query_text', 'label', 'use_count', 'is_favorite', 'created_at']);

        return response()->json(['status' => 'success', 'data' => $prompts]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'query_text' => 'required|string|max:500',
            'label'      => 'nullable|string|max:100',
        ]);

        $exists = AiSavedSearchPrompt::forUser(Auth::id())
            ->where('query_text', $validated['query_text'])
            ->exists();

        if ($exists) {
            return response()->json([
                'status'  => 'error',
                'message' => 'This prompt is already saved'
            ], 409);
        }

        $prompt = AiSavedSearchPrompt::create([
            'user_id'    => Auth::id(),
            'query_text' => $validated['query_text'],
            'label'      => $validated['label'],
        ]);

        return response()->json(['status' => 'success', 'data' => $prompt], 201);
    }

    public function use($id)
    {
        $prompt = AiSavedSearchPrompt::forUser(Auth::id())->findOrFail($id);
        $prompt->incrementUseCount();

        return response()->json(['status' => 'success', 'data' => $prompt]);
    }

    public function toggleFavorite($id)
    {
        $prompt = AiSavedSearchPrompt::forUser(Auth::id())->findOrFail($id);
        $prompt->update(['is_favorite' => !$prompt->is_favorite]);

        return response()->json(['status' => 'success', 'data' => $prompt]);
    }

    public function destroy($id)
    {
        $prompt = AiSavedSearchPrompt::forUser(Auth::id())->findOrFail($id);
        $prompt->delete();

        return response()->json(['status' => 'success']);
    }
}