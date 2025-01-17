<?php

/**
 * 
 * To enable this option, send the /setinline command to @BotFather
 * and provide the placeholder text that the user will see 
 * in the input field after typing your bot's name.
 * 
 */

namespace Bot\Command\User;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Spatie\Emoji\Emoji;

/**
 * Start command...
 *
 */
class GamesCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'games';

    /**
     * @var string
     */
    protected $description = 'Play Games';

    /**
     * @var string
     */
    protected $usage = '/games';

    /**
     * @var string
     */
    protected $version = '1.1.0';

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * @return mixed
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        $message = $this->getUpdate()->getMessage();
        $edited_message = $this->getUpdate()->getEditedMessage();
        $callback_query = $this->getUpdate()->getCallbackQuery();

        if ($edited_message) {
            $message = $edited_message;
        }

        $chat_id = null;
        $data_query = null;

        if ($message) {
            if (!$message->getChat()->isPrivateChat()) {
                return Request::emptyResponse();
            }

            $chat_id = $message->getChat()->getId();
        } elseif ($callback_query) {
            $chat_id = $callback_query->getMessage()->getChat()->getId();

            $data_query = [];
            $data_query['callback_query_id'] = $callback_query->getId();
        }

        $text = Emoji::wavingHand() . ' ';
        $text .= '<b>' . __('Hi!') . '</b>' . PHP_EOL;
        $text .= __('To begin, start a message with {USAGE} in any of your chats or click the {BUTTON} button and then select a chat to play in.', ['{USAGE}' => '<b>\'@' . $this->getTelegram()->getBotUsername() . ' ...\'</b>', '{BUTTON}' => '<b>\'' . __('Play') . '\'</b>']);

        $data = [
            'chat_id'                  => $chat_id,
            'text'                     => $text,
            'parse_mode'               => 'HTML',
            'disable_web_page_preview' => true,
            'reply_markup'             => new InlineKeyboard(
                [
                    new InlineKeyboardButton(
                        [
                            'text'                => __('Play') . ' ' . Emoji::gameDie(),
                            'switch_inline_query' => Emoji::gameDie(),
                        ]
                    ),
                ]
            ),
        ];

        if ($message) {
            return Request::sendMessage($data);
        } elseif ($callback_query) {
            $data['message_id'] = $callback_query->getMessage()->getMessageId();
            $result = Request::editMessageText($data);
            Request::answerCallbackQuery($data_query);

            return $result;
        }

        return Request::emptyResponse();
    }
}
