<?php

class VK {
	
	function __construct($token, $version) {
		$this->token = $token;
		$this->version = $version;
		$this->endpoint = "https://api.vk.com/method";
		$this->random_id = random_int(1, 9999999);
	}

	public function GetUserInfo($user_id) {
		$user = $this->Request("users.get", array("user_ids" => $user_id, "fields" => "sex"));
		return $user;
	}

	public function SendMessage($peer_id, $message, $attachment = null) {
		$this->Request("messages.send", array("peer_id" => $peer_id, "message" => $message, "attachment" => $attachment, "random_id" => $this->random_id));
	}

	public function SendMessages($user_ids, $message, $attachment = null) {
		$this->Request("messages.send", array("user_ids" => $user_ids, "message" => $message, "attachment" => $attachment, "random_id" => $this->random_id));
	}

	public function SendButton($peer_id, $message, $gl_massiv=array(), $inline, $attachment = null) {
        $buttons = [];
        $i = 0;
        foreach ($gl_massiv as $button_str) {
            $j = 0;
            foreach ($button_str as $button) {
                if ($button[0] == 'text') {
                    $color = $this->replaceColor($button[3]);
                    $buttons[$i][$j]["action"]["type"] = "text";
                    if ($button[1] != null)
                        $buttons[$i][$j]["action"]["payload"] = json_encode($button[1], JSON_UNESCAPED_UNICODE);
                    $buttons[$i][$j]["action"]["label"] = $button[2];
                    $buttons[$i][$j]["color"] = $color;
                } if ($button[0] == 'link') {
                    $buttons[$i][$j]["action"]["type"] = "open_link";
                    $buttons[$i][$j]["action"]["label"] = $button[1];
                    $buttons[$i][$j]["action"]["link"] = $button[2];
                } if ($button[0] == 'location') {
                	$buttons[$i][$j]["action"]["type"] = "location";
                } if ($button[0] == 'callback') {
                	$buttons[$i][$j]["action"]["type"] = "callback";
					if ($button[1] != null)
                        $buttons[$i][$j]["action"]["payload"] = json_encode($button[1], JSON_UNESCAPED_UNICODE);
                	$buttons[$i][$j]["action"]["label"] = $button[2];
                	$color = $this->replaceColor($button[3]);
                	$buttons[$i][$j]["color"] = $color;
                }
                $j++;
            }
            $i++;
        }
        if ($inline == true) {
        	$one_time = false;
        } else {
        	$one_time = true;
        }
        $buttons = array(
        	"one_time" => $one_time,
            "inline" => $inline,
            "buttons" => $buttons);
        $buttons = json_encode($buttons, JSON_UNESCAPED_UNICODE);
		$this->Request("messages.send", array("peer_id" => $peer_id, "message" => $message, "attachment" => $attachment, "random_id" => $this->random_id, "keyboard" => $buttons));
	}

	public function SendEvent($user_id, $peer_id, $event_id, $payload) {
		$this->Request("messages.sendMessageEventAnswer", array("user_id" => $user_id, "peer_id" => $peer_id, "event_id" => $event_id, "event_data" => json_encode($payload)));
	}

    private function replaceColor($color) {
        switch ($color) {
            case 'red':
                $color = 'negative';
                break;
            case 'green':
                $color = 'positive';
                break;
            case 'white':
                $color = 'secondary';
                break;
            case 'blue':
                $color = 'primary';
                break;
        }
        return $color;
    }

	private function Request($method, $params=array()) {
		$request = json_decode(file_get_contents($this->endpoint."/$method?".http_build_query($params)."&access_token=".$this->token."&v=".$this->version), true);
		return $request;
	}

}