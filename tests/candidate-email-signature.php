<?php
require __DIR__.'/../app/Services/CandidateEmailBody.php';
use App\Services\CandidateEmailBody;
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
check(CandidateEmailBody::render('Hello', null) === '<div>Hello</div>', 'Null signature must preserve email body');
check(CandidateEmailBody::render('Hello', '   ') === '<div>Hello</div>', 'Blank signature must not add a footer');
$html = CandidateEmailBody::render("Hello\nCandidate", "Jordan\nRecruiter <script>alert(1)</script>");
check(str_contains($html, 'Hello<br />'), 'Message line breaks preserved');
check(str_contains($html, 'Jordan<br />'), 'Signature line breaks preserved');
check(!str_contains($html, '<script>'), 'Signature HTML must be escaped');
check(substr_count($html, 'Jordan') === 1, 'Signature appended once');
check(!str_contains(CandidateEmailBody::render('Hello', 'Casey'), 'Jordan'), 'Signatures must not leak between senders');
check(!str_contains(CandidateEmailBody::render('Hello', null), 'border-top'), 'Removing signature removes footer');
echo "PASS: optional signatures, line breaks, escaping, single append, and sender separation\n";

$imageHtml = CandidateEmailBody::render('Hello', null, 'https://example.com/signature.png');
check(str_contains($imageHtml, '<img src="https://example.com/signature.png"'), 'Image-only signatures supported');
check(!str_contains(CandidateEmailBody::render('Hello', null, 'javascript:alert(1)'), '<img'), 'Reject unsafe image URLs');
check(str_contains(CandidateEmailBody::render('Hello', 'Jordan', 'https://example.com/image.png?a=1&b=2'), '&amp;b=2'), 'Image URL attributes escaped');
check(str_contains(CandidateEmailBody::render('Hello', 'Jordan', 'https://example.com/image.png'), 'Jordan'), 'Text and image signatures supported together');
echo "PASS: signature images, safe URLs, attribute escaping, combined signatures\n";
