<?php
$data = file_get_contents('data.json');
$data = json_decode($data, true);
$mediafireUrl = $data['url'];
$chatid = $data['chatid'];
$message_id = $data['message_id'];
$processId = $data['processId'];
$botToken = getenv('botToken');
shell_exec('rm -rf data.json');
$downloadLink = extractLink($mediafireUrl, $chatid, $message_id);
dlsend($downloadLink, $chatid, $message_id, $processId);

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

function dlsend($dlurl, $chatid, $message_id, $processId) {
    $name = $processId; // Name the video file
    $fileExtension = substr(strrchr($dlurl, '.'), 1);
    $file = $name . "." . $fileExtension;
    $img = "image.jpeg";
    $output = shell_exec('wget -q ' . $dlurl . ' -O ' . $file);
    $image = shell_exec("wget -q 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQBMcEZv5mxvSTCgqbAWlE5hyqz5O_FAUy2zVSiqxZ01EUUuGjcqJNP_QgNuMVsomfyfco&usqp=CAU' -O image.jpeg ");
    $file_path = "/root/{$file}";
    $img_path = "/root/{$img}";
    $fileSizeInMB = getFileSizeInMB($file_path);
    if ($fileSizeInMB <= 2000) {
        if ($fileExtension === 'mp4') {
            return sendVideo($chatid, $message_id, $processId, $file_path, $img_path);
        } else {
            return sendFile($chatid, $message_id, $processId, $file_path, $img_path);
        }
    } else {
        exit(sendMessage($chatid, "<b>File Size Larger than 2GB.</b>", $message_id));
    }
}

function getVideoDuration($videoPath) {
    $cmd = "ffmpeg -i $videoPath 2>&1 | grep Duration | cut -d ' ' -f 4 | sed s/,//";
    $output = shell_exec($cmd);
    sscanf($output, "%d:%d:%f", $hours, $minutes, $seconds);
    $duration = $hours * 3600 + $minutes * 60 + $seconds;
    return $duration;
}

function sendMessage($chatId, $message, $message_id) {
    global $botToken;
    $url = "http://172.17.0.2:8081/bot$botToken/sendMessage?chat_id="
    . $chatId . "&text=" . urlencode($message) . "&parse_mode=HTML&reply_to_message_id=" . $message_id; // Reply to original message
    file_get_contents($url);
}

function sendVideo($chatid, $message_id, $processId, $file_path, $img_path) {
    global $botToken;
    $duration = getVideoDuration($file_path);
    $ch = curl_init();
    $caption = "<b>Download Complete</b>
    <pre>
Process ID - $processId
    </pre>";
    curl_setopt($ch, CURLOPT_URL, 'http://172.17.0.2:8081/bot' . $botToken . '/sendVideo');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'chat_id' => $chatid,
        'video' => new CURLFile($file_path),
        'caption' => $caption,
        'thumbnail' => new CURLFile($img_path),
        'duration' => $duration,
        'supports_streaming' => true, // Lowercase true
        'parse_mode' => 'HTML'
    ]);
    // Execute cURL request
    $response = curl_exec($ch);
    curl_close($ch);
    shell_exec("rm -rf $file_path");
    shell_exec("rm -rf $img_path");
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

function sendFile($chatid, $message_id, $processId, $file_path, $img_path) {
    global $botToken;
    $caption = "<b>Download Complete</b>
    <pre>
Process ID - $processId
    </pre>";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://172.17.0.2:8081/bot' . $botToken . '/sendDocument');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'chat_id' => $chatid,
        'document' => new CURLFile($file_path),
        'caption' => $caption,
        'thumbnail' => new CURLFile($img_path),
        'parse_mode' => 'HTML'
    ]);
    // Execute cURL request
    $response = curl_exec($ch);
    curl_close($ch);
    shell_exec("rm -rf $file_path");
    shell_exec("rm -rf $img_path");
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

function getFileSizeInMB($filePath) {
    $fileSizeInBytes = filesize($filePath);
    $fileSizeInMB = $fileSizeInBytes / (1024 * 1024);
    $fileSizeInMB = round($fileSizeInMB, 2);

    return $fileSizeInMB;
}
?>
