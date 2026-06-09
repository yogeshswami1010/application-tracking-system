<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Reply;
use App\AiApiKey;
use App\Http\Requests\StoreSkill;
use App\JobCategory;
use App\Skill;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\Admin\Skill\AiGenerateSkillsRequest;
use Illuminate\Support\Str;

class AdminSkillsController extends AdminBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('menu.skills');
        $this->pageIcon = 'icon-grid';
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        abort_if(! $this->user->cans('view_skills'), 403);

        $this->skills = Skill::with('category')->orderBy('name')->get();
        $this->jobCategories = JobCategory::orderBy('name')->get();

        $byCategory = $this->skills->groupBy('category_id');
        $topGroup = $byCategory->sortByDesc(fn ($g) => $g->count())->first();
        $this->skStatCategoryCount = $this->skills->pluck('category_id')->filter()->unique()->count();
        $topCat = $topGroup && $topGroup->first() ? $topGroup->first()->category : null;
        $this->skMostUsedCategoryName = $topCat ? $topCat->name : '—';
        $this->skMostUsedCategoryCount = $topGroup ? $topGroup->count() : 0;

        return view('admin.skills.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort_if(! $this->user->cans('add_skills'), 403);

        return redirect()->route('admin.skills.index', ['open' => 'create']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSkill $request)
    {
        abort_if(! $this->user->cans('add_skills'), 403);

        $names = $request->name;
        $categoryId = $request->category_id;
        if (trim($names[0]) == '') {
            return Reply::error(__('errors.addSkills'));
        }

        foreach ($names as $name) {
            if (! is_null($name)) {
                Skill::create(['name' => $name, 'category_id' => $categoryId]);
            }
        }

        return Reply::redirect(route('admin.skills.index'), __('menu.skills').' '.__('messages.createdSuccessfully'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        abort_if(! $this->user->cans('edit_skills'), 403);

        Skill::findOrFail($id);

        return redirect()->route('admin.skills.index', ['open' => 'edit', 'id' => $id]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreSkill $request, $id)
    {
        abort_if(! $this->user->cans('edit_skills'), 403);

        $skill = Skill::find($id);
        $skill->name = $request->name;
        $skill->category_id = $request->category_id;
        $skill->save();

        return Reply::redirect(route('admin.skills.index'), __('menu.skills').' '.__('messages.updatedSuccessfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        abort_if(! $this->user->cans('delete_skills'), 403);

        Skill::destroy($id);
        return Reply::success(__('messages.recordDeleted'));
    }

    public function aiGenerateSkills(AiGenerateSkillsRequest $request)
    {
        abort_if(! ($this->user?->cans('add_skills') || $this->user?->cans('edit_skills')), 403);
        $validated = $request->validated();

        $categoryIds = array_values(array_map('intval', (array) ($validated['category_ids'] ?? [])));

        $configuredKeys = AiApiKey::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $apiCandidates = [];
        foreach ($configuredKeys as $row) {
            $plainKey = $this->resolvePlainApiKey((string) $row->api_key);
            if ($plainKey === '') {
                continue;
            }

            $apiCandidates[] = [
                'provider' => $this->normalizeProvider((string) $row->provider),
                'api_key' => $plainKey,
            ];
        }

        if (empty($apiCandidates)) {
            return Reply::error(__('messages.aiApiKeyRequired'));
        }

        $totalCreated = 0;
        $totalSkipped = 0;
        $categoryErrors = [];

        foreach ($categoryIds as $categoryId) {
            $category = JobCategory::find($categoryId);
            if (! $category) {
                continue;
            }

            $existingSkills = Skill::query()
                ->where('category_id', (int) $categoryId)
                ->limit(80)
                ->pluck('name')
                ->map(fn ($name) => trim((string) $name))
                ->filter()
                ->values()
                ->all();

            $existingLower = collect($existingSkills)
                ->map(fn ($n) => mb_strtolower((string) $n))
                ->flip()
                ->all();

            $systemPrompt = 'You are a senior recruitment content strategist. '
                .'Return ONLY valid minified JSON with keys: skills. '
                .'Rules: skills must be an array of 8 to 12 short, unique skill names (no numbering, no duplicates). '
                .'Each skill name should be 1 to 3 words. Avoid any skills present in the provided existing_skills list. '
                .'Do NOT include markdown fences or any extra keys.';

            $userPrompt = 'Job category: '.(string) $category->name."\n"
                .'existing_skills: '.json_encode(array_values($existingSkills), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $lastError = 'AI generation failed for category.';
            $generatedSkills = [];

            foreach ($apiCandidates as $candidate) {
                try {
                    $content = $this->generateChatByProvider(
                        (string) $candidate['provider'],
                        (string) $candidate['api_key'],
                        $systemPrompt,
                        [['role' => 'user', 'content' => $userPrompt]]
                    );

                    $generatedSkills = $this->extractSkillsFromAiContent($content);
                    if (empty($generatedSkills)) {
                        throw new \RuntimeException('Empty skills array returned from AI.');
                    }

                    $lastError = '';
                    break;
                } catch (\Throwable $e) {
                    $lastError = $e->getMessage();
                }
            }

            if (empty($generatedSkills)) {
                $categoryErrors[] = [
                    'category_id' => $categoryId,
                    'category' => $category->name,
                    'error' => $lastError,
                ];
                continue;
            }

            $createdThis = 0;
            $skippedThis = 0;
            $seenLower = [];

            foreach ($generatedSkills as $name) {
                $skillName = trim((string) $name);
                if ($skillName === '') {
                    continue;
                }

                // Keep it reasonably short to match how skills are displayed in the UI.
                if (mb_strlen($skillName) > 60) {
                    $skillName = mb_substr($skillName, 0, 60);
                }

                $key = mb_strtolower($skillName);
                if (isset($seenLower[$key])) {
                    continue;
                }
                $seenLower[$key] = true;

                if (isset($existingLower[$key])) {
                    $skippedThis++;
                    continue;
                }

                Skill::create([
                    'name' => $skillName,
                    'category_id' => (int) $categoryId,
                ]);
                $createdThis++;
                $existingLower[$key] = true;
            }

            $totalCreated += $createdThis;
            $totalSkipped += $skippedThis;
        }

        if ($totalCreated === 0 && ! empty($categoryErrors)) {
            return Reply::error(__('messages.aiSkillsGenerationFailed'));
        }

        return Reply::successWithData(__('messages.aiSkillsGeneratedSuccessfully'), [
            'created' => $totalCreated,
            'skipped' => $totalSkipped,
            'errors' => $categoryErrors,
        ]);
    }

    private function extractSkillsFromAiContent(string $content): array
    {
        $content = trim($content);
        if ($content === '') {
            return [];
        }

        // Try to isolate JSON object first.
        $start = strpos($content, '{');
        $end = strrpos($content, '}');
        $jsonCandidate = $content;

        if ($start !== false && $end !== false && $end > $start) {
            $jsonCandidate = substr($content, $start, $end - $start + 1);
        }

        $data = json_decode($jsonCandidate, true);
        if (! is_array($data)) {
            // Fallback: maybe the AI returned a JSON array.
            $startArr = strpos($content, '[');
            $endArr = strrpos($content, ']');
            if ($startArr !== false && $endArr !== false && $endArr > $startArr) {
                $arrCandidate = substr($content, $startArr, $endArr - $startArr + 1);
                $arrData = json_decode($arrCandidate, true);
                return is_array($arrData) ? $arrData : [];
            }

            return [];
        }

        if (isset($data['skills']) && is_array($data['skills'])) {
            return $data['skills'];
        }

        // If the JSON itself is a list.
        if (array_values($data) === $data) {
            return $data;
        }

        return [];
    }

    private function resolvePlainApiKey(string $key): string
    {
        $key = trim($key);
        if ($key === '') {
            return '';
        }

        if (Str::startsWith($key, 'eyJpdiI6')) {
            try {
                $decrypted = Crypt::decryptString($key);
                if (is_string($decrypted) && trim($decrypted) !== '') {
                    return trim($decrypted);
                }
            } catch (\Throwable $e) {
                // Ignore and fall back to raw value.
            }
        }

        return $key;
    }

    private function normalizeProvider(string $provider): string
    {
        $provider = strtolower(trim($provider));

        if ($provider === '' || Str::contains($provider, 'openai') || Str::contains($provider, 'gpt')) {
            return 'openai';
        }
        if (Str::contains($provider, 'groq') || Str::contains($provider, 'llama')) {
            return 'groq';
        }
        if (Str::contains($provider, 'anthropic') || Str::contains($provider, 'claude')) {
            return 'anthropic';
        }
        if (Str::contains($provider, 'gemini') || Str::contains($provider, 'google')) {
            return 'gemini';
        }

        return 'openai';
    }

    private function extractChatMessageTextFromResponse(array $payload): string
    {
        $content = data_get($payload, 'choices.0.message.content');

        if (is_string($content)) {
            return trim($content);
        }

        if (is_array($content)) {
            $text = collect($content)
                ->map(function ($part) {
                    if (is_string($part)) {
                        return $part;
                    }

                    if (! is_array($part)) {
                        return '';
                    }

                    return (string) ($part['text'] ?? $part['content'] ?? '');
                })
                ->filter(fn ($chunk) => trim((string) $chunk) !== '')
                ->implode("\n");

            if (trim($text) !== '') {
                return trim($text);
            }
        }

        $choiceText = data_get($payload, 'choices.0.text');
        if (is_string($choiceText) && trim($choiceText) !== '') {
            return trim($choiceText);
        }

        $outputText = data_get($payload, 'output_text');
        if (is_string($outputText) && trim($outputText) !== '') {
            return trim($outputText);
        }

        $outputPartText = data_get($payload, 'output.0.content.0.text');
        if (is_string($outputPartText) && trim($outputPartText) !== '') {
            return trim($outputPartText);
        }

        return '';
    }

    private function generateChatByProvider(string $provider, string $apiKey, string $systemPrompt, array $conversation): string
    {
        $provider = $this->normalizeProvider($provider);

        if ($provider === 'openai' || $provider === 'groq') {
            $url = $provider === 'groq'
                ? 'https://api.groq.com/openai/v1/chat/completions'
                : 'https://api.openai.com/v1/chat/completions';

            $model = $provider === 'groq'
                ? env('AI_JOB_GENERATOR_GROQ_MODEL', 'llama-3.1-8b-instant')
                : env('AI_JOB_GENERATOR_MODEL', 'gpt-5-nano');

            $messages = array_merge(
                [['role' => 'system', 'content' => $systemPrompt]],
                array_map(function (array $m) {
                    return [
                        'role' => $m['role'],
                        'content' => (string) $m['content'],
                    ];
                }, $conversation)
            );

            $payload = [
                'model' => $model,
                'messages' => $messages,
            ];

            // OpenAI newer models can reject custom temperature values.
            if ($provider !== 'openai') {
                $payload['temperature'] = 0.35;
            }

            // GPT-5 family expects max_completion_tokens instead of max_tokens.
            if ($provider === 'openai' && Str::startsWith(strtolower($model), 'gpt-5')) {
                $payload['max_completion_tokens'] = 1200;
            } else {
                $payload['max_tokens'] = 800;
            }

            $response = Http::timeout(45)->withToken($apiKey)->post($url, $payload);

            if (! $response->successful()) {
                $message = (string) data_get($response->json(), 'error.message', '');
                throw new \RuntimeException('AI request failed'.($message ? ': '.$message : '.'));
            }

            $responseJson = $response->json();
            $this->recordAiUsage($provider, $model, is_array($responseJson) ? $responseJson : []);
            $content = $this->extractChatMessageTextFromResponse(is_array($responseJson) ? $responseJson : []);
            $content = trim($content);

            // GPT-5 family can intermittently return empty visible text on first attempt.
            if ($content === '' && $provider === 'openai') {
                $retryPayload = $payload;
                if (isset($retryPayload['max_completion_tokens'])) {
                    $retryPayload['max_completion_tokens'] = max((int) $retryPayload['max_completion_tokens'], 2200);
                } else {
                    $retryPayload['max_tokens'] = max((int) ($retryPayload['max_tokens'] ?? 800), 1200);
                }

                $retryResponse = Http::timeout(45)->withToken($apiKey)->post($url, $retryPayload);
                if ($retryResponse->successful()) {
                    $retryJson = $retryResponse->json();
                    $this->recordAiUsage($provider, $model, is_array($retryJson) ? $retryJson : []);
                    $content = $this->extractChatMessageTextFromResponse(is_array($retryJson) ? $retryJson : []);
                    $content = trim($content);
                }
            }

            if ($content === '') {
                throw new \RuntimeException('Empty AI response.');
            }

            return $content;
        }

        if ($provider === 'anthropic') {
            $model = env('AI_JOB_GENERATOR_ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022');

            $messages = array_map(function (array $m) {
                return [
                    'role' => $m['role'],
                    'content' => (string) $m['content'],
                ];
            }, $conversation);

            $response = Http::timeout(45)->withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model' => $model,
                'max_tokens' => 800,
                'temperature' => 0.35,
                'system' => $systemPrompt,
                'messages' => $messages,
            ]);

            if (! $response->successful()) {
                $message = (string) data_get($response->json(), 'error.message', '');
                throw new \RuntimeException('AI request failed'.($message ? ': '.$message : '.'));
            }

            $responseJson = (array) $response->json();
            $this->recordAiUsage($provider, $model, $responseJson);
            $content = collect((array) data_get($responseJson, 'content', []))
                ->map(function ($part) {
                    if (! is_array($part) || ($part['type'] ?? '') !== 'text') {
                        return '';
                    }
                    return (string) ($part['text'] ?? '');
                })
                ->implode("\n")
                ->trim();

            if ($content === '') {
                throw new \RuntimeException('Empty AI response.');
            }

            return $content;
        }

        if ($provider === 'gemini') {
            $model = env('AI_JOB_GENERATOR_GEMINI_MODEL', 'gemini-2.0-flash');
            $history = collect($conversation)
                ->map(function (array $m) {
                    return ucfirst((string) $m['role']).': '.trim((string) $m['content']);
                })
                ->implode("\n");

            $prompt = $systemPrompt."\n\n".$history."\nAssistant:";
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/'.$model.':generateContent?key='.$apiKey;

            $response = Http::timeout(45)->post($url, [
                'contents' => [[
                    'parts' => [[
                        'text' => $prompt,
                    ]],
                ]],
                'generationConfig' => [
                    'temperature' => 0.35,
                    'maxOutputTokens' => 800,
                ],
            ]);

            if (! $response->successful()) {
                $message = (string) data_get($response->json(), 'error.message', '');
                throw new \RuntimeException('AI request failed'.($message ? ': '.$message : '.'));
            }

            $responseJson = (array) $response->json();
            $this->recordAiUsage($provider, $model, $responseJson);
            $content = (string) data_get($responseJson, 'candidates.0.content.parts.0.text', '');
            $content = trim($content);
            if ($content === '') {
                throw new \RuntimeException('Empty AI response.');
            }

            return $content;
        }

        throw new \RuntimeException('Unsupported AI provider: '.$provider);
    }
    public function quickCreate(Request $request)
    {
        abort_if(! $this->user->cans('add_skills'), 403);

        $name = trim($request->input('name', ''));

        if ($name === '') {
            return response()->json(['status' => 'error', 'message' => 'Skill name is required.']);
        }

        $skill = Skill::firstOrCreate(['name' => $name]);

        return response()->json([
            'status' => 'success',
            'id'     => $skill->id,
            'name'   => $skill->name,
        ]);
    }
}
