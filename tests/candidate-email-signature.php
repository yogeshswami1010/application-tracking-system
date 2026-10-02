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
