<?php 

// Подключение класса
require_once 'vk.php';

// Инициализация класса
$vk = new VK('ТокенСДоступомКСообщениямСообщества', '5.126');
$data = json_decode(file_get_contents('php://input'));

// Если это подтверждение, то отправляет ключ CallBack
if ($data->type == 'confirmation') {
    exit('e7db2992'); 
} 

// Отправка OK. Необходимо каждый раз отправлять OK, чтобы сервер VK не повторял запросы
$vk->SendOK();

// Получаем всю информацию с запроса исходя из его типа
if ($data->type == 'message_new') {
	$from_id = $data->object->message->from_id;
	$message = $data->object->message->text;
	$peer_id = $data->object->message->peer_id;
} 
if ($data->type == 'wall_reply_new') {
	$from_id = $data->object->from_id;
} 
if ($data->type == 'message_event') {
	$from_id = $data->object->user_id;
	$peer_id = $data->object->peer_id;
	$event_id = $data->object->event_id;
	$payload = $data->object->payload;
}
if ($data->type == 'like_add') {
	$from_id = $data->object->liker_id;
}
if ($data->type == 'wall_repost') {
	$from_id = $data->object->from_id;
	$post_id = $data->object->copy_history[0]->id;
}

// Получаем информацию о пользователе
$user = $vk->GetUserInfo($from_id);
$first_name = $user->response[0]->first_name; // Имя
$last_name = $user->response[0]->last_name; // Фамилия
$sex = $user->response[0]->sex; // Пол (1 - женский, 2 - мужской)

// Пример обычной кнопки
$button = ["text", ["command" => "example"], "Пример", "white"];

// Проверяем секретный ключ. Если совпадает, то продолжаем
if ($data->secret == 'YourSecretKeyHere') {
	// Если пришёл запрос с типом message_new
	if ($data->type == 'message_new') {

		// Проверяем доступность клавиаутуры у пользователя. Если старый клиент, то просим обновить.
		if ($data->object->client_info->keyboard == false) {
			$vk->sendMessage($peer_id, "$first_name, обнови клиент ВКонтакте, чтобы получить полный функционал бота.");
		} else {

			// Проверяем, присутствуют ли кнопки. Если да, то получаем с них команду.
			if (isset($data->object->message->payload)) { 
			    $payload = json_decode($data->object->message->payload, true); 
			} else {
			    $payload = null;
			}

			$payload = $payload['command'];

			// Реакция на кнопку "Начать" или же на сообщения, которые содержат текст: начать, меню, бот
			if ($payload == 'start' || mb_strtolower($message) == ('начать' || 'меню' || 'бот')) {
				$vk->SendButton($peer_id, 'Добро пожаловать!', [[$button]], false);
			}

			// Пример ответа на нажатие кнопки
			if ($payload == 'example') {
				$vk->sendMessage($peer_id, 'Вуху!');
			}

		}

	}

	// Если пришёл запрос с типом message_event
	if ($data->type == 'message_event') {
		$vk->SendEvent($from_id, $peer_id, $event_id, $payload);
	}

	// Реакция на комментарий пользователя (необходимо включить: Записи на стене -> Добавление)
	if ($data->type == 'wall_reply_new') {
		$vk->sendMessage($from_id, "$first_name, спасибо за комментарий!");
	}

	// Реакция на лайк записи (необходимо включить: Записи на стене -> Добавление лайка)
	if ($data->type == 'like_add') {
		$vk->sendMessage($from_id, "$first_name, спасибо за лайк!");
	}

	// Реакция на репост записи (необходимо включить: Записи на стене -> Репост)
	if ($data->type == 'wall_repost') {
		$vk->sendMessage($from_id, "$first_name, спасибо за репост!");
	}
}