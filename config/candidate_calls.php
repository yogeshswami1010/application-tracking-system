<?php

return [
    'enabled' => env('CANDIDATE_CALLS_ENABLED', false),
    'credential_id' => env('TELNYX_VOICE_CREDENTIAL_ID'),
    'from_number' => env('TELNYX_VOICE_FROM_NUMBER'),
    // Telnyx-hosted NVIDIA model supports the browser's WebM and MP4 recordings.
    // Summaries reuse services.deepseek.key/model, like the ATS resume parser.
    'transcription_model' => 'nvidia/parakeet-v3',
];
