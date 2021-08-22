<?php
/*
* Author => Javohir Abdirasulov
* Email  => alienware7x@gmail.com
*/

// For debugging errors
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


define('API_KEY', '1395344442:AAGgETt4vy-SzCqYtoBLwYz59KDf02O3WrQ');
define('WELCOME_MSG',"WELCOM MSG HERE");
define('CHANNEL_ID',"@qwer342r"); //@channelusername or ID. Add -100 prefix before ID if your channel is private
define('CHANNEL_URL',"https://t.me/joinchat/AAABBBCCCDDDXXX");

// Generate CURL request and execute
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


// Save image file to server
function getFile($ID,$type = null){
      $curl = curl_init();
      curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.telegram.org/bot".API_KEY."/getFile?file_id=".$ID,
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
    file_put_contents("saved.".$type, fopen("https://api.telegram.org/file/bot".API_KEY."/".$arr->result->file_path, 'r'));
  return $arr;
}


// Inline keyboard for post
 function getKeyboard($likes_count=0){
    $inline_button1 = ['text'=> "👍 ".$likes_count ,'callback_data'=>'like'];
    $inline_button2 = [
        "text"=>"Share with friends",
        "url" => "https://t.me/share/url?url=👉 ".CHANNEL_URL." 👈\n ** SHARE_TEXT **"
      ];

    $inline_keyboard = [[$inline_button1],[$inline_button2]];
    $keyboard = array("inline_keyboard"=>$inline_keyboard);
    return json_encode($keyboard); 
 }


$data     = file_get_contents("php://input");
$update   = json_decode($data, true);

// write logs to file for debugging
file_put_contents('logs.txt', print_r($update, true));

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
$photo_id   = $update['message']['photo'][3]['file_id'];

$db_con     = 'mysql:dbname=u0904844_likebot;host=localhost;charset=utf8';
$password   = '123456Qwer#';
$user       = 'u0904_likebot';
$pdo        = new PDO($db_con, $user, $password);


// Welcome message for new users
if($update['message']['text'] == "/start"){
    makeHTTPRequest('sendMessage',[
        'chat_id' => $update['message']['from']['id'],
        'text'    => WELCOME_MSG,
    ]);
}


// if image file sent to bot
if(isset($photo_id)){

    // Optional request for debugging   
    makeHTTPRequest('sendMessage',[
        'chat_id' => $update['message']['from']['id'],
        'text'    => 'Photo_ID: '.$photo_id,
        'parse_mode'=>'HTML',
      ]);
     
    //Downloading file from telegram server   
    getFile($photo_id,"jpg");

    //Creating temporary CURL file object  
    $photo = curl_file_create('saved.jpg', 'image/jpeg');
        
    //Forwarding video from bot to channel with inline keyboards
    makeHTTPRequest('sendPhoto',[
       'chat_id' => CHANNEL_ID,
       'photo'   => $photo,
       'caption' => "<b>🔻 Follow us 🔻</b>\n<a href='t.me/mychannel'>Telegram </a> | <a href='tiktok.com/@mychannel'> TikTok </a> | <a href='instagram.com/mychannel'> Instagram </a>", 
       'parse_mode'   => "HTML",
       'reply_markup' => getKeyboard()
       ]);     
}


//Optional condition for /setinline mode enabled case
if(isset($update['inline_query'])){
    $chat_id = $update['inline_query']['from']['id'];
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
if(isset($cb_query) && $cb_data=="like"){
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
       // $score = 0;
    } else {
    // dislike/delete record if already liked
    $st1 = $pdo->prepare("DELETE FROM likes WHERE `user_id` = :user_id AND `message_id` = :message_id");

    $st1->bindParam(":user_id",    $userID);
    $st1->bindParam(":message_id", $callBackQueryMessageID);
    $exec = $st1->execute();
    //$score = 0;
        $alert = "You Diskliked this !";
    } 
        //select likes count
        $st= $pdo->prepare("select count(*) as like_count from likes where message_id=:message_id");
        $st->bindParam(":message_id", $callBackQueryMessageID);
        $st->execute();
    
        // update inline keyboard value
        makeHTTPRequest("editMessageReplyMarkup",[
            'chat_id'      => CHANNEL_ID,
            'message_id'   => $callBackQueryMessageID,
            'reply_markup' => getKeyboard($st->fetch()['like_count']),
        ]);
    
    // show alert message when like button clicked
    makeHTTPRequest("answerCallbackQuery", [
        'callback_query_id' => $callBackQueryID,
        'text' => $alert,
        //'show_alert' => true,
    ] );
}
?>
