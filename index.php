<?php
/*
* Author => Javohir Abdirasulov
* Email  => alienware7x@gmail.com
*/
define('API_KEY', '1395344442:AAGgETt4vy-SzCqYtoBLwYz59KDf02O3WrQ');
define('WELCOME_MSG',"WELCOM MSG HERE");
define('CHANNEL_ID',"-1001202924154");
function makeHTTPRequest($method,$datas=[]){
    $url = "https://api.telegram.org/bot".API_KEY."/".$method;
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$datas);
    $res = curl_exec($ch);
    if(curl_error($ch)){
        var_dump(curl_error($ch));
    }else{
        return json_decode($res);
    }
}


function getFile($ID,$type = null){
      $curl = curl_init();
      curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.telegram.org/bot1395344442:AAGgETt4vy-SzCqYtoBLwYz59KDf02O3WrQ/getFile?file_id=".$ID,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
    )
  );
    $response = curl_exec($curl);
    curl_close($curl);
    $arr =  json_decode($response);
    unlink("saved.".$type);
    file_put_contents("saved.mp4", fopen("https://api.telegram.org/file/bot1395344442:AAGgETt4vy-SzCqYtoBLwYz59KDf02O3WrQ/".$arr->result->file_path, 'r'));
  return $arr;
}

$data     = file_get_contents("php://input");
$update   = json_decode($data, true);

$cb_data    = $update['callback_query']['data'];
$cb_query   = $update['callback_query'];
$cb_qid     = $update['callback_query']['id'];
$cb_mc_id   = $update['callback_query']['message']['chat']['id'];
$cb_mm_id   = $update['callback_query']['message']['message_id'];
$cb_im_id   = $update['callback_query']['inline_message_id'];
$cb_from_id = $update['callback_query']['from']['id'];
$cb_fname   = $update['callback_query']['from']['first_name'];
$cb_lname   = $update['callback_query']['from']['last_name'];
$cb_uname   = $update['callback_query']['from']['username'];

$video_id = $update['message']['video']['file_id'];

$db_con   = 'mysql:dbname=mylog_likebot;host=localhost;charset=utf8';
$password = 'Lv32dm?3g^q9xO02';
$user = 'mylog_likeuser';
$user = 'mylog_likeuser';
$pdo  = new PDO($db_con, $user, $password);

$inline_button1 = array('text'=> "👍",'callback_data'=>'like');
$inline_button2 = array("text"=>"Yaqinlarga yuborish | Поделиться","url" => "https://t.me/share/url?url=👉 https://t.me/joinchat/AAAAAEezKnozjb7i5xU4-A 👈\n **$ TELEGRAM KANAL VIDEO LIKE $**");
$inline_keyboard = [[$inline_button1],[$inline_button2]];
$keyboard = array("inline_keyboard"=>$inline_keyboard);
$menu_001 = json_encode($keyboard); 
  

$inline_keyboard = [[$inline_button5],[$inline_button6]];
$keyboard = array("inline_keyboard"=>$inline_keyboard);
$menu_003 = json_encode($keyboard); 

// Welcome message for new users
if($update['message']['text'] == "/start"){
    makeHTTPRequest('sendMessage',[
        'chat_id' => $update['message']['from']['id'],
        'text'    => WELCOME_MSG,
    ]);
}


if(isset($video_id)){
// Optional request for debugging   
makeHTTPRequest('sendMessage',[
    'chat_id' => $update['message']['from']['id'],
    'text'    => 'VIDEO_ID: '.$video_id,
    'parse_mode'=>'HTML',
  ]);
 
//Downloading file from telegram server   
getFile($video_id,"mp4");

//Creating temporary CURL file object  
$video = curl_file_create('saved.mp4', 'video/mp4');
    
//Forwarding video from bot to channel with inline keyboards
makeHTTPRequest('sendVideo',[
   'chat_id' => CHANNEL_ID,
   'video'   => $video,
   'caption' => "<b>🔻 Bizni kuzatib boring 🔻</b>\n<a href='t.me/videolikeofficial'>Telegram </a> | <a href='tiktok.com/@videolikeofficial'> TikTok </a> | <a href='instagram.com/videolikeofficial'> Instagram </a>", 
   'parse_mode'   => "HTML",
   'reply_markup' => $menu_001
   ]);     
}


//Optional condition for /setinline mode enabled case
if(isset($update['inline_query'])){
    $chat_id = $update['inline_query']['from']['id'];
    makeHTTPRequest('sendMessage',[
        'chat_id'=>"@unnamed0002",
        'text'=>json_encode($update),
        'parse_mode'=>'HTML',
    ]);
    $inlineQueryID = $update['inline_query']['id'];
    makeHTTPRequest('answerInlineQuery',[
        'inline_query_id'=>$inlineQueryID,
        'results' => json_encode([[
            'type' => 'article',
            'id' => base64_encode(1),
            'title' => 'Send?',
            'input_message_content' => ['parse_mode' => 'HTML', 'message_text' => $update['inline_query']['query']],
            'reply_markup' => [
                'inline_keyboard'=>[
                    [
                        ['text'=> "👍",'callback_data'=>'like']
                    ]
                ]]
        ]])
    ]);
}


// Listening for inline buttons on click
if(isset($cb_query) && $cb_data="like"){
    $alert = "You ❤ this !";
    $callBackQueryID = $cb_qid;
    $callBackQueryChatID = $cb_mc_id;

    //if inline callbackquery
    if($callBackQueryChatID==""){ 
        $callBackQueryChatID=$cb_from_id;
    }

    //if inline callbackquery
    $callBackQueryMessageID = $cb_mm_id;
    if($callBackQueryMessageID==""){
        $callBackQueryMessageID=$cb_im_id;
    }

    $userID    = $cb_from_id;
    $firstName = $cb_fname;
    $lastName  = $cb_lname;
    $userName  = $cb_uname;

   
    $score = 0;
    $pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );

    //check if user already liked this post
    $st= $pdo->prepare("select count(id) as row_count from likes where message_id=:message_id and user_id=:user_id");
    $st->bindParam(":message_id", $callBackQueryMessageID);
    $st->bindParam(":user_id", $userID);
    $st->execute();
    
    // insert like
    if($st->fetch()['row_count'] < 1 ) {
        $st = $pdo->prepare("INSERT INTO likes(`chat_id`,`query_id`,`message_id`,`user_id`) VALUES(:chat_id,:query_id,:message_id,:user_id)");
        $st->bindParam(":chat_id",   $callBackQueryChatID);
        $st->bindParam(":query_id",  $callBackQueryID);
        $st->bindParam(":message_id",$callBackQueryMessageID);
        $st->bindParam(":user_id",   $userID);
        $exec = $st->execute();
    } else {
    // dislike/delete record if already liked
    $st1 = $pdo->prepare("DELETE FROM likes WHERE `user_id` = :user_id AND `message_id` = :message_id");

    $st1->bindParam(":user_id",    $userID);
    $st1->bindParam(":message_id", $callBackQueryMessageID);
    $exec = $st1->execute();
    $score = 0;
        $alert = "You Diskliked this !";
    } 
        //select likes count
        $st= $pdo->prepare("select count(*) as like_count from likes where message_id=:message_id");
        $st->bindParam(":message_id", $callBackQueryMessageID);
        $st->execute();
        $like_count = $st->fetch()['like_count']+$score; // +-1 for hearts

        $likes = $like_count . ' ' . '👍';
    
        // update inline keyboard value
        makeHTTPRequest("editMessageReplyMarkup",[
            'chat_id'      => '-1001202924154',
            'message_id'   => $callBackQueryMessageID,
            'reply_markup' => json_encode([
                'inline_keyboard'=>[
                    [
                        ['text'=> $likes,'callback_data'=>'like']
                    ],
                    [
                        ["text"=>"Yaqinlarga yuborish | Поделиться","url" => "https://t.me/share/url?url=👉 https://t.me/joinchat/AAAAAEezKnozjb7i5xU4-A 👈\n\n ** $$$ TELEGRAM KANAL VIDEO LIKE $$$ **"]
                    ]
                ]]),
        ]);
    
    // show alert to user
    makeHTTPRequest("answerCallbackQuery", [
        'callback_query_id' => $callBackQueryID,
        'text' => $alert.': '.$callBackQueryMessageID.' : '.$likes,
        //'show_alert' => true,
    ] );
}
?>