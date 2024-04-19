<?php
$data = file_get_contents('data.json');
$data = json_decode($data, true);
$mediafireUrl = $data['url'];
$chatid = $data['chatid'];
$message_id = $data['message_id'];
$processId = $data['processId'];
shell_exec('rm -rf data.json');
$downloadLink = extractLink($mediafireUrl, $chatid, $message_id);
dlsend($downloadLink, $chatid, $message_id,$processId);

if ($status === false) {
    exit(sendMessage($chatid, "<b>Fail to Download</b>", $message_id));
} else {
    exit(); // Exit after successful download
}

function extractLink($mediafireUrl, $chatid, $message_id) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $mediafireUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    curl_close($ch);

    if (empty($data)) {
        exit(sendMessage($chatid, "<b>Fail to Fetch data</b>", $message_id));
    }

    $dom = new DOMDocument();
    @$dom->loadHTML($data);

    $form = $dom->getElementsByTagName('form')->item(0);
    $div = $form->getElementsByTagName('div')->item(0);
    $a = $div->getElementsByTagName('a')->item(1);
    $href = $a->getAttribute('href');

    if ($href) {
        return $href;
    } else {
        exit(sendMessage($chatid, "<b>Fail to Extract link</b>", $message_id));
    }
}

function dlsend($dlurl, $chatid, $message_id,$processId) {
    $name = $processId; // Name the video file
    $file = $name . ".mp4";
    $img = "image.jpeg";
    $output = shell_exec('wget -q ' . $dlurl . ' -O ' . $name . '.mp4');
    $image = shell_exec("wget -q 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQBMcEZv5mxvSTCgqbAWlE5hyqz5O_FAUy2zVSiqxZ01EUUuGjcqJNP_QgNuMVsomfyfco&usqp=CAU' -O image.jpeg ");
    $file_path = "/root/{$file}";
    $img_path  = "/root/{$img}";
    $ch = curl_init();
    $caption = "<b>Download Complete</b>
    <pre>
Process ID - $processId
Time Taken - 
    </pre>";
    curl_setopt($ch, CURLOPT_URL, 'https://in-ram-grown.ngrok-free.app/bot6794724389:AAFTxaLLVC1ROzCR8FRTpm2oXYDOr_MgjD4/sendVideo');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'chat_id' => $chatid,
        'video' => new CURLFile($file_path),
        'caption' => $caption,
        'thumbnail' => new CURLFile($img_path),
        'supports_streaming' => true, // Lowercase true
        'parse_mode' => 'HTML'
    ]);
    // Execute cURL request
    $response = curl_exec($ch);
    curl_close($ch);
    shell_exec("rm -rf $file");
    shell_exec("rm -rf $img");
    // Check if request was successful
    if (!empty($response)) {
        // Decode JSON response
        $result = json_decode($response, true);

        // Check if 'ok' is true and message_id exists in the result
        if (isset($result['ok']) && $result['ok'] === true && isset($result['result']['message_id'])) {
            return true; // Message was sent successfully
        } else {
            // Log the response for debugging
            error_log("Telegram API response: " . $response, 3, "/root/error.log");
            return false; // Message sending failed
        }
    } else {
        // Log the empty response for debugging
        error_log("Empty response from Telegram API", 3, "/root/error.log");
        return false; // Empty response
    }
}

function sendMessage($chatId, $message, $message_id) {
    $url = "https://in-ram-grown.ngrok-free.app/bot6794724389:AAFTxaLLVC1ROzCR8FRTpm2oXYDOr_MgjD4/sendMessage?chat_id="
    . $chatId . "&text=" . urlencode($message) . "&parse_mode=HTML&reply_to_message_id=" . $message_id; // Reply to original message
    file_get_contents($url);
}
?>
