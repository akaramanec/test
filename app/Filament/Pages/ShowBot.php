<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ShowBot extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'bot';

    protected static string $view = 'filament.pages.bot';

    public function getBotInfo(): array
    {
        return [
            'webhook' => [
                'ok' => true,
                'result' => [
                    'url' => 'https://fin.boto.kyiv.ua/api/telegram',
                    'has_custom_certificate' => false,
                    'pending_update_count' => 0,
                    'last_error_date' => 1736682996,
                    'last_error_message' => 'Wrong response from the webhook: 500 Internal Server Error',
                    'max_connections' => 40,
                    'ip_address' => '64.226.71.116',
                ],
            ],
            'getMe' => [
                'ok' => true,
                'result' => [
                    'id' => 7043633641,
                    'is_bot' => true,
                    'first_name' => 'Dev Test boto',
                    'username' => 'Vjggf_bot',
                    'can_join_groups' => true,
                    'can_read_all_group_messages' => false,
                    'supports_inline_queries' => false,
                    'can_connect_to_business' => false,
                    'has_main_web_app' => false,
                ],
            ],
        ];
    }

    public function getWebHookUrl(): string
    {
        return config('app.url') . '/api/telegram/set-webhook?token=' . config('token_tm');
    }

    public function getGetMeUrl(): string
    {
        return config('app.url') . '/api/telegram/get-me?token=' . config('token_tm');
    }
}
