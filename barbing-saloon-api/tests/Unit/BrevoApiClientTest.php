<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Notification\Services\BrevoApiClient;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class BrevoApiClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_throws_exception_when_credentials_are_unconfigured(): void
    {
        $client = new BrevoApiClient;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Brevo SMTP host, port, login, password, and sender email must be configured');

        $client->send('client@example.com', 'Test Subject', '<p>Hello</p>');
    }

    public function test_it_sends_via_rest_api_when_api_key_is_provided(): void
    {
        Http::fake([
            'https://api.brevo.com/v3/smtp/email' => Http::response(['messageId' => 'brevo-rest-12345'], 201),
        ]);

        Setting::updateOrCreate(['key' => 'brevo_api_key'], ['value' => 'xkeysib-test-key-1234']);
        Setting::updateOrCreate(['key' => 'mail_from'], ['value' => 'noreply@candycutz.com']);
        Setting::updateOrCreate(['key' => 'mail_from_name'], ['value' => 'CandyCutz']);

        $client = new BrevoApiClient;
        $client->send('client@example.com', 'Welcome to CandyCutz', '<p>Your appointment is set.</p>');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.brevo.com/v3/smtp/email'
                && $request->header('api-key')[0] === 'xkeysib-test-key-1234'
                && $request['subject'] === 'Welcome to CandyCutz'
                && $request['to'][0]['email'] === 'client@example.com';
        });
    }

    public function test_it_falls_back_to_rest_api_when_smtp_fails_and_api_key_is_set(): void
    {
        Http::fake([
            'https://api.brevo.com/v3/smtp/email' => Http::response(['messageId' => 'brevo-rest-fallback-999'], 201),
        ]);

        // Configure invalid SMTP server that causes connection failure
        Setting::updateOrCreate(['key' => 'mail_host'], ['value' => '127.0.0.1']);
        Setting::updateOrCreate(['key' => 'mail_port'], ['value' => '9999']);
        Setting::updateOrCreate(['key' => 'mail_username'], ['value' => 'bad_user']);
        Setting::updateOrCreate(['key' => 'mail_password'], ['value' => 'bad_pass']);
        Setting::updateOrCreate(['key' => 'mail_from'], ['value' => 'noreply@candycutz.com']);
        Setting::updateOrCreate(['key' => 'brevo_api_key'], ['value' => 'xkeysib-fallback-key']);

        $client = new BrevoApiClient;
        $client->send('client@example.com', 'Fallback Test', '<p>Tested</p>');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.brevo.com/v3/smtp/email'
                && $request->header('api-key')[0] === 'xkeysib-fallback-key';
        });
    }
}
