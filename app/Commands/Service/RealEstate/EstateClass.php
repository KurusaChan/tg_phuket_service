<?php

namespace App\Commands\Service\RealEstate;

use App\Commands\BaseCommand;
use App\Models\ServiceOrder;
use App\Services\Status\UserStatusService;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class EstateClass extends BaseCommand
{

    function processCommand()
    {
        if ($this->user->status === UserStatusService::SELECT_REAL_ESTATE_CLASS) {
            ServiceOrder::where('user_id', $this->user->id)->where('status', 'NEW')->update([
                'class' => $this->update->getMessage()->getText()
            ]);
            $this->triggerCommand(RentPeriod::class);
        } else {
            $this->user->status = UserStatusService::SELECT_REAL_ESTATE_CLASS;
            $this->user->save();

            $buttons[] = ['Эконом', 'Стандарт', 'Премиум'];
            $buttons[] = [$this->text['main_menu']];

            $this->getBot()->sendMessageWithKeyboard($this->user->chat_id, $this->text['select_estate_class'], new ReplyKeyboardMarkup($buttons, false, true));
        }
    }

}