🔴 Critical (must fix before go-live)
1. Admin routes have NO middleware protection
routes/admin.php — the entire admin route group has no isAdmin check. Only AdminVerificationController::serveVideo() checks manually. AdminController::index() and every method in AdminUserController (delete user, deactivate, unverify) are wide open to any authenticated user. You need a middleware or abort_unless(auth()->user()->isAdmin(), 403) in every action.

2. Hardcoded personal data in PaymobService
app/Services/PaymobService.php:155-165 — billing data has hardcoded name, email (omar@email.com), and phone number (+966562338328). This data is sent to Paymob for every payment. Must be replaced with actual user data from $data parameter.

3. Hardcoded Paymob iframe ID
app/Services/PaymobService.php:42 — iframe ID 12267 is hardcoded in the payment URL. This should be in config/payment.php → .env as PAYMOB_IFRAME_ID.

4. storage:link symlink is missing
public/storage symlink does not exist. Profile images and any publicly stored files will return 404. Run php artisan storage:link on the production server.

🟠 High (strongly recommended before go-live)
5. Payment env vars missing from .env.example
.env.example has no PAYMOB_API_KEY, PAYMOB_INTEGRATION_ID, PAYMOB_HMAC_SECRET, or PAYMOB_IFRAME_ID. Any new deployment will silently fail for payments.

6. No rate limiting on auth routes
Login and signup routes have no throttle middleware. Brute-force and credential-stuffing attacks are trivially easy.

7. No forgot-password email flow
The ResetPasswordController only works for logged-in users. There is no email-based "forgot password" flow for locked-out users.

8. APP_DEBUG=true and LOG_LEVEL=debug in .env.example
If someone deploys from .env.example without changing these, stack traces will be exposed publicly.

🟡 Medium (clean up before or shortly after)
9. Commented-out dd() calls left in production code

app/Services/PaymobService.php:34,37,146 — //dd($authToken), //dd($order), //dd($response->json())
app/Http/Controllers/MessageController.php:26 — //dd('asdasd')
10. MAIL_MAILER=log in .env.example
Emails are silently swallowed. This needs to be a real mailer (SMTP/Mailgun/SES) in production.

11. MAIL_FROM_ADDRESS="hello@example.com" placeholder
Any system email (if you add transactional email later) will come from a placeholder address.

12. Test seeder creates a real user
database/seeders/DatabaseSeeder.php — creates test@example.com user. Fine for dev, but don't run db:seed on production unless intentional.

13. Empty CityController and CountryController
Both controllers are registered imports in routes but have no implementation. Either implement or remove.

🔵 Nice-to-have / Pre-launch checklist items
No tests beyond ExampleTest — only default scaffold tests exist; no coverage of auth, payment, or admin flows
REVERB_APP_ID/KEY/SECRET empty in .env.example — realtime features will silently fail without these
APP_NAME=Laravel in .env.example — should be Al-Mithaq
Meta description in _header.blade.php still contains Metronic/KeenThemes boilerplate text — update for SEO/brand
og:title / og:url meta tags reference keenthemes.com/metronic — should be your app
Want me to fix any of these? The critical admin middleware issue and the hardcoded payment billing data are the two most urgent.