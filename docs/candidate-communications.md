# Candidate bulk communications

Candidate selection and the shared email/SMS composer are available in Job Applications (table and board), Candidate Database, Candidate Marketing, AI Search, Temp Staffing, and Consortium Registrations.

## Setup

Apply the email-template migration against the intended ATS database:

```sh
php artisan migrate
```

Email uses the ATS Email Settings; AI Search preserves its dedicated SMTP configuration. SMS uses the existing Telnyx SMS Settings. Both view_job_applications and edit_job_applications permissions are required to send messages or manage templates.

## Behavior

- Select up to 100 candidates and choose Bulk email or Bulk SMS.
- The composer previews recipients, excludes duplicate contacts, and explains skipped records. Consortium SMS recipients must have recorded SMS consent.
- Messages are sent separately, one recipient per request. Keep the composer open while sending. An interrupted request is marked unconfirmed and the remaining batch stops; check delivery before retrying.
- Email templates belong to the signed-in user and are reusable across these screens. Saving the same template name updates it. Older browser-only AI templates remain available in that browser and can be saved to the account.
- Use [applicant_name] in the subject or message for personalization.
- Select-all applies to the current table page or loaded board cards. The Job Applications table retains selections across pagination; changing its filters clears selection. Other table redraws clear selection.
- Sending success means provider acceptance, not confirmed delivery.

## Checks without live messages

```sh
php tests/candidate-communications-smoke.php
node tests/candidate-communications-ui.cjs
```

The backend smoke checks use SQLite in memory and fake transports. The UI harness checks selection, templates, submit protection, and SMS mode without a browser. PHPUnit equivalents are in tests/Unit/CandidateCommunicationTest.php.
