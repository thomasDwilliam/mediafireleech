<?php
$update = file_get_contents('php://input');
error_reporting(0);
$update = json_decode($update, TRUE);
$print = print_r($update, true); // Changed $print to store the printed output as a string
$chatId = $update["message"]["chat"]["id"]; // Changed "curlat" to "chat"
$userId = $update["message"]["from"]["id"];
$firstname = $update["message"]["from"]["first_name"];
$username = $update["message"]["from"]["username"];
$message = $update["message"]["text"];
$message_id = $update["message"]["message_id"];
$botToken = getenv('botToken');
echo "Server is running successfully!";
if (strpos($message, "/start") === 0) {
    sendMessage($chatId, "<b>Hello, @$username! Welcome to Mediafire Downloader Bot.</b>", $message_id);
} elseif (strpos($message, "/dl") === 0) {
    $link = substr($message, 4);
    $file = 'data.json';
    $id = rand(0,999999);
    $data = [
        'chatid' => $chatId,
        'url' => $link,
        'message_id' => $message_id,
        'processId' => $id
    ];
    $data = json_encode($data);
    $put = file_put_contents($file, $data);
    if ($put !== false) {
        sendMessage($chatId, "<b>Download Started </b>
<pre>
Download Process ID - $id
Download Server - Mediafire
Download Url - $link
</pre>", $message_id);
        shell_exec('php dlsend.php > /dev/null 2>&1 &'); // Execute dlsend.php in the background
    } else {
        sendMessage($chatId, "<b>Failed to start download.</b>", $message_id);
    }
}elseif(strpos($message, "/server") === 0){
    $loadAvg = sys_getloadavg();
    $uptime = shell_exec('uptime');
    $cpuCores = trim(shell_exec('nproc'));
    $directory = '/root/';
    $rootTotalSpace = disk_total_space($directory);
    $rootFreeSpace = disk_free_space($directory);
    $rootUsedSpace = $rootTotalSpace - $rootFreeSpace;
    $currentMemoryUsage = formatBytes(memory_get_usage());
    $message = "<b>Server Information</b>
<pre>
CPU Cores - $cpuCores
Uptime - $uptime
Total Space - " . formatBytes($rootTotalSpace) . "
Used Space - " . formatBytes($rootUsedSpace) . "
Free Space - " . formatBytes($rootFreeSpace) . "
Memoery Usage - $currentMemoryUsage
</pre>";
    sendMessage($chatId,$message,$message_id);
}elseif(strpos($message, "/speed") === 0){
    $speedtestResult = shell_exec('curl -s https://raw.githubusercontent.com/sivel/speedtest-cli/master/speedtest.py | python -');
    $message = "    <b>Speed Test Result</b>
    <pre>$speedtestResult</pre>";
    sendMessage($chatId,$message,$message_id);
} else {
    // Respond to other messages
    sendMessage($chatId, "Sorry, I don't understand that command.", $message_id);
}

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision) . ' ' . $units[$pow];
}
function sendMessage($chatId, $message, $message_id)
{
    global $botToken;
    $url = "http://172.17.0.2:8081/bot$botToken/sendMessage?chat_id="
    . $chatId . "&text=" . urlencode($message) . "&parse_mode=HTML&reply_to_message_id=" . $message_id;
    file_get_contents($url);
}
?>