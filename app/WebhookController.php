<?php

namespace App;

use App\Commands\CreateRecord\SelectMedia;
use App\Commands\MainMenu;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Update;

class WebhookController
{

    public function handle()
    {
        $client = new Client(getenv('TELEGRAM_BOT_TOKEN'));

        $client->on(function (Update $update) {
            $handler_class_name = MainMenu::class;

            if ($update->getCallbackQuery()) {
                $config = include(__DIR__ . '/config/callback_commands.php');
                $action = \json_decode($update->getCallbackQuery()->getData(), true)['a'];

                if (isset($config[$action])) {
                    $handler_class_name = $config[$action];
                }
            } else {
                // checking commands -> keyboard commands -> mode -> exit
                if ($update->getMessage()) {
                    $text = $update->getMessage()->getText();

                    if (!$text) {
                        $handler_class_name = SelectMedia::class;
                    } else {
                        if (strpos($text, '/') === 0) {
                            $handlers = include(__DIR__ . '/config/slash_commands.php');
                        }

                        if (isset($handlers[$text])) {
                            $handler_class_name = $handlers[$text];
                        } else {
                            $key = $this->processKeyboardCommand($text);
                            $handlers = include(__DIR__ . '/config/keyboard_сommands.php');
                            if ($key && $handlers[$key]) {
                                $handler_class_name = $handlers[$key];
                            } else {
                                $handlers = include(__DIR__ . '/config/status_сommands.php');

                                // first check if user exists, then check his status
                                $user = \App\Models\User::where('chat_id', $update->getMessage()->getFrom()->getId())->first();
                                if ($user && $handlers[$user->status]) {
                                    $handler_class_name = $handlers[$user->status];
                                }
                            }
                        }
                    }
                }
            }

            (new $handler_class_name($update))->handle();
        }, function (Update $update) {
            return true;
        });

        $client->run();

    }

    protected function processKeyboardCommand($text): ?string
    {
        $config = include('config/lang/bot.php');
        $translations = @array_flip($config);
        if (isset($translations[$text])) {
            return $translations[$text];
        }

        return null;
    }

}
