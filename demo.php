<?php 

// Подключение класса
require_once 'vk.php';

// Настройка класса
$vk = new VK('a0c1c481b534ac034c4ec33696a4e1555839cac682897ee0d7b1a8d63af68e9f93885cb3ed6a056ab3e58', '5.120');
$data = json_decode(file_get_contents('php://input'));

// Если это подтверждение, то отправляет ключ CallBack
if ($data->type == 'confirmation') {
    exit('281c302f'); 
} 

$vk->SendOK();

// Получаем всю информацию с запроса
$type = $data->type;
$message = $data->object->message->text;
$from_id = $data->object->message->from_id;
$peer_id = $data->object->message->peer_id;

// Получаем информацию о пользователе
$user = $vk->GetUserInfo($from_id);
$first_name = $user['response'][0]['first_name'];
$last_name = $user['response'][0]['last_name'];
$sex = $user['response'][0]['sex'];

$button = ["text", ["command" => "example"], "Пример", "white"];

// Если пришёл запрос с типом message_new
if ($type == 'message_new') {

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

		if ($payload == 'start') {
			$vk->SendButton($peer_id, 'Добро пожаловать!', [[$button]], false);
		}

		if ($payload == 'example') {
			$vk->sendMessage($peer_id, 'Вуху!');
		}

	}

}

// Если пришёл запрос с типом message_event
if ($type == 'message_event') {
	$vk->SendEvent($data->object->user_id, $data->object->peer_id, $data->object->event_id, $data->object->payload);
}