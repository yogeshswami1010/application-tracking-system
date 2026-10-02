<?php
require __DIR__.'/../app/Services/EmailSignatureHtml.php';
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

$design = '<table cellpadding="0"><tr><td><img src="https://example.com/logo.png" width="150"></td><td style="color:#123456;font-size:14px">Jordan<a href="mailto:hello@example.com">Email</a><img src="https://example.com/social.png" width="22"></td></tr></table>';
$rendered = CandidateEmailBody::render('Hello', 'OLD FOOTER', 'https://example.com/old.png', $design);
check(substr_count($rendered, '<img') === 2, 'HTML preserves multiple images without appending uploaded image');
check(str_contains($rendered, 'width="150"') && str_contains($rendered, 'width="22"'), 'Preserve individual image sizes');
check(str_contains($rendered, '<table') && str_contains($rendered, 'mailto:hello@example.com'), 'Preserve table layout and email links');
check(!str_contains($rendered, 'OLD FOOTER') && !str_contains($rendered, 'old.png'), 'HTML replaces legacy signature');
$unsafe = \App\Services\EmailSignatureHtml::clean('<script>alert(1)</script><img src="javascript:alert(1)" onerror="alert(2)"><a href="javascript:alert(3)" onclick="alert(4)">link</a><span style="position:fixed;background-image:url(https://example.com);color:red">Safe</span>');
check(!preg_match('/script|onerror|onclick|position|url\(/i', $unsafe), 'Strip executable HTML and unsafe CSS');
check(str_contains($unsafe, 'color:red'), 'Keep safe inline styling');
check(CandidateEmailBody::render('Hello', 'Fallback', null, '') === CandidateEmailBody::render('Hello', 'Fallback'), 'Clearing HTML restores legacy signature');
echo "PASS: HTML layout, multiple image sizes, no duplicate footer, HTML sanitization, fallback\n";
