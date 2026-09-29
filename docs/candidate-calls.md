# Candidate browser calling and automatic summaries

Open a candidate profile and choose **Call candidate & call history**. This opens a dedicated page so closing the profile drawer does not interrupt a call. Recruiters need view_job_applications and edit_job_applications permissions to call; users with view permission can read history. Only the recruiter who placed a call can upload or retry its summary.

## Deployment

1. Apply `php artisan migrate` against the intended ATS database.
2. In Telnyx, configure a credential-based SIP connection with an outbound voice profile, allowed destinations, spending limits, and a voice-enabled caller number. The existing Telnyx API key in SMS Settings is used server-side to create a dedicated WebRTC telephony credential for this connection and mint browser tokens; SMS activation is not required for voice. Do not use an unrestricted shared production credential: browser tokens inherit the connection's outbound capabilities.
3. Open **Account Settings → Candidate calling**, check **Enable browser calling**, enter the **Telnyx SIP connection ID** (the Connection ID shown in that connection's Configuration tab) and **Telnyx calling number** (including `+` and country code), then click **Save**. The ATS provisions and stores its separate WebRTC telephony credential on the server at the first call. These values are stored in the database and apply to new calls immediately; no `.env` edit or configuration cache refresh is needed. Only users with `manage_settings` permission can save them. Existing `CANDIDATE_CALLS_ENABLED`, `TELNYX_VOICE_CREDENTIAL_ID`, and `TELNYX_VOICE_FROM_NUMBER` environment settings remain a fallback until the account form is first saved; after that, account settings take precedence, including disabling calling.

4. Summaries reuse the existing `DEEPSEEK_API_KEY` and `DEEPSEEK_MODEL` through `services.deepseek`, just like the ATS resume parser. No separate summary key or model is required. Audio-to-text uses Telnyx's `nvidia/parakeet-v3` model with the existing Telnyx API key; the Telnyx account must have access to its speech-to-text API. There are no OpenAI API calls or OpenAI models in this call workflow. Any old `CALL_TRANSCRIPTION_MODEL` or `CALL_SUMMARY_MODEL` settings are no longer used.
5. Serve the ATS over HTTPS. Allow microphone access and the Telnyx SDK CDN/WebRTC connections in your browser/network/CSP. Use headphones. This initial version uses the existing US/Canada phone normalization.
6. Set PHP `upload_max_filesize` to at least 24M, `post_max_size` to at least 26M, and corresponding web server body limits. Allow at least 240 seconds for the summary HTTP request in PHP and the reverse proxy. No queue worker is required.

## Recording and storage

After connecting, ask the candidate for recording and AI transcription consent, check the confirmation, and click **Start recording**. Both audio streams are mixed in the browser. Calls without recording remain in history without an AI summary. Recording is capped at 30 minutes / approximately 23 MiB; a longer conversation's summary covers only its recorded portion.

Ending a call uploads its audio and automatically generates and saves the transcript and summary. Do not close the page during the call or upload: browser-side audio cannot survive closing the page. An upload failure keeps the recording in memory and offers **Retry saving call**. Once uploaded, recordings survive an AI failure and can be retried from history; interrupted processing can be reclaimed after five minutes. Transcription is retained on summary failure to avoid paying for transcription again.

Audio is stored only under `storage/app/private/candidate-calls`, never in the application's public upload disk. Successful summary generation deletes the audio, keeping the transcript and summary attached to the candidate. Failed/pending audio remains for retries; include this private directory in your candidate-data retention and deletion procedures. AI summaries must be reviewed and contain no automatic hiring decision or score.

## Validation

`php tests/candidate-calls-smoke.php` uses SQLite in memory and fake provider responses. `node tests/candidate-calls-ui.cjs` tests the browser lifecycle with fake audio and telephony. These tests never place calls or send candidate audio.

Before production rollout, make an authorized test call to a test number and verify remote audio, microphone, mute, consent, recording of both participants, hangup, history, and summary retries. Live telephony and transcription need configured credentials and were not exercised by the automated checks.

References: [Telnyx WebRTC quickstart](https://developers.telnyx.com/development/webrtc/js-sdk/quickstart/index), [Telnyx token API](https://developers.telnyx.com/api-reference/access-tokens/create-an-access-token), [Telnyx transcription models](https://developers.telnyx.com/docs/voice/stt/rest-api/parameters/models), [DeepSeek chat API](https://api-docs.deepseek.com/api/create-chat-completion/).
