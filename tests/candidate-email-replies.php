<?php
require __DIR__.'/../app/Services/CandidateEmailThread.php';
require __DIR__.'/../app/Services/CandidateEmailContent.php';
use App\Services\CandidateEmailThread as Thread;
use App\Services\CandidateEmailContent as Content;

function checkReply($condition, $message) { if (!$condition) throw new RuntimeException($message); }
$sent = [
    ['job_application_id' => 1772, 'message_id' => 'other@ats.test', 'subject' => 'Other message'],
    ['job_application_id' => 3280, 'message_id' => 'ats-original@ats.test', 'subject' => 'Test template subjest 2'],
];
checkReply(Thread::match($sent, '<ats-original@ats.test>', '<unrelated@ats.test> <ats-original@ats.test>', 'Changed title') === 3280, 'Direct parent must identify the exact application');
checkReply(Thread::match($sent, '', '<other@ats.test> <ats-original@ats.test>', '') === 3280, 'References must be split, and nearest parent checked first');
checkReply(Thread::match($sent, '', '', 'Re: Test template subjest 2') === 3280, 'Older emails without IDs must match the sent subject on the correct profile');
checkReply(Thread::match($sent, '', '', 'Re: re: TEST template subjest 2') === 3280, 'Reply prefixes and case should normalize');
$ambiguous = array_merge($sent, [['job_application_id' => 1772, 'message_id' => null, 'subject' => 'Test template subjest 2']]);
checkReply(Thread::match($ambiguous, '', '', 'Re: Test template subjest 2') === null, 'Ambiguous duplicate profiles must not pick the first application');
checkReply(Thread::match($sent, '', '', '') === null, 'Empty subjects must not guess a profile');
checkReply(Thread::match($sent, '<ats-original@ats.test>', '<other@ats.test>', 'Other message') === 3280, 'Exact parent overrides subject matching');
checkReply(Thread::ids('<one@ats.test>\r\n <two@ats.test>') === ['one@ats.test','two@ats.test'], 'Message IDs normalize angle brackets');
checkReply(Content::decode(base64_encode('just for test'), 3) === 'just for test', 'Decode base64 body parts');
checkReply(Content::decode('just=20for=20test', 4) === 'just for test', 'Decode quoted-printable body parts');
checkReply(Content::decode("caf\xe9", 0, 'ISO-8859-1') === 'café', 'Convert mail charsets to UTF-8');
$structure = json_decode('{"type":1,"parts":[{"type":1,"parts":[{"type":0,"subtype":"PLAIN","encoding":3},{"type":0,"subtype":"HTML","encoding":4}]},{"type":0,"subtype":"PLAIN","disposition":"ATTACHMENT"}]}');
$requested = [];
$body = Content::body($structure, function ($section) use (&$requested) {
    $requested[] = $section;
    return $section === '1.1' ? base64_encode('just for test') : '<p>just for test</p>';
});
checkReply($body === 'just for test', 'Nested multipart Gmail reply must display readable plain text once');
checkReply($requested === ['1.1','1.2'], 'Skip attachments and fetch exact MIME parts');
$htmlOnly = json_decode('{"type":0,"subtype":"HTML","encoding":0}');
checkReply(Content::body($htmlOnly, fn () => '<p>Reply &amp; details</p><script>bad()</script>') === 'Reply & details', 'HTML-only replies become readable, safe text');
echo "PASS: exact threads, legacy subjects, duplicate profiles, nested Gmail MIME, transfer encodings, charsets, and HTML-only replies\n";
