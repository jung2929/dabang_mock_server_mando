<?php

require 'MailPwd.php';

use Firebase\JWT\JWT;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Google\Auth\ApplicationDefaultCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;



function getSQLErrorException($errorLogs, $e, $req)
{
    $res = (Object)Array();
    http_response_code(500);
    $res->code = 500;
    $res->message = "SQL Exception -> " . $e->getTraceAsString();
    echo json_encode($res);
    addErrorLogs($errorLogs, $res, $req);
}

function isValidHeader($jwt, $key)
{
    try {
        $data = getDataByJWToken($jwt, $key);
        //로그인 함수 직접 구현 요함
        return isValidUser($data->userEmail);
    } catch (\Exception $e) {
        return false;
    }
}

function sendFcm($fcmToken, $data, $key, $deviceType)
{
    $url = 'https://fcm.googleapis.com/fcm/send';

    $headers = array(
        'Authorization: key=' . $key,
        'Content-Type: application/json'
    );

    $fields['data'] = $data;

    if ($deviceType == 'IOS') {
        $notification['title'] = $data['title'];
        $notification['body'] = $data['body'];
        $notification['sound'] = 'default';
        $fields['notification'] = $notification;
    }

    $fields['to'] = $fcmToken;
    $fields['content_available'] = true;
    $fields['priority'] = "high";

    $fields = json_encode($fields, JSON_NUMERIC_CHECK);

//    echo $fields;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

    $result = curl_exec($ch);
    if ($result === FALSE) {
        //die('FCM Send Error: ' . curl_error($ch));
    }
    curl_close($ch);
    return $result;
}

function getTodayByTimeStamp()
{
    return date("Y-m-d H:i:s");
}

function getJWToken($userIdx, $userEmail, $secretKey)
{
    $data = array(
        'date' => (string)getTodayByTimeStamp(),
        'userIdx' => (string)$userIdx,
        'userEmail' => (string)$userEmail
    );

//    echo json_encode($data);

    return $jwt = JWT::encode($data, $secretKey);

//    echo "encoded jwt: " . $jwt . "n";
//    $decoded = JWT::decode($jwt, $secretKey, array('HS256'))
//    print_r($decoded);
}

function getDataByJWToken($jwt, $secretKey)
{
    try{
        $decoded = JWT::decode($jwt, $secretKey, array('HS256'));
    }catch(\Exception $e){
        return "";
    }

//    print_r($decoded);
    return $decoded;

}


function checkAndroidBillingReceipt($credentialsPath, $token, $pid)
{

    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope("https://www.googleapis.com/auth/androidpublisher");
    $client->setSubject("USER_ID.iam.gserviceaccount.com");


    $service = new Google_Service_AndroidPublisher($client);
    $optParams = array('token' => $token);

    return $service->purchases_products->get("PACKAGE_NAME", $pid, $token);
}


function addAccessLogs($accessLogs, $body)
{
    if (isset($_SERVER['HTTP_X_ACCESS_TOKEN']))
        $logData["JWT"] = getDataByJWToken($_SERVER['HTTP_X_ACCESS_TOKEN'], JWT_SECRET_KEY);
    $logData["GET"] = $_GET;
    $logData["BODY"] = $body;
    $logData["REQUEST_METHOD"] = $_SERVER["REQUEST_METHOD"];
    $logData["REQUEST_URI"] = $_SERVER["REQUEST_URI"];
//    $logData["SERVER_SOFTWARE"] = $_SERVER["SERVER_SOFTWARE"];
    $logData["REMOTE_ADDR"] = $_SERVER["REMOTE_ADDR"];
    $logData["HTTP_USER_AGENT"] = $_SERVER["HTTP_USER_AGENT"];
    $accessLogs->addInfo(json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

}

function addErrorLogs($errorLogs, $res, $body)
{
    if (isset($_SERVER['HTTP_X_ACCESS_TOKEN']))
        $req["JWT"] = getDataByJWToken($_SERVER['HTTP_X_ACCESS_TOKEN'], JWT_SECRET_KEY);
    $req["GET"] = $_GET;
    $req["BODY"] = $body;
    $req["REQUEST_METHOD"] = $_SERVER["REQUEST_METHOD"];
    $req["REQUEST_URI"] = $_SERVER["REQUEST_URI"];
//    $req["SERVER_SOFTWARE"] = $_SERVER["SERVER_SOFTWARE"];
    $req["REMOTE_ADDR"] = $_SERVER["REMOTE_ADDR"];
    $req["HTTP_USER_AGENT"] = $_SERVER["HTTP_USER_AGENT"];

    $logData["REQUEST"] = $req;
    $logData["RESPONSE"] = $res;

    $errorLogs->addError(json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    $content=["Error : " . $req["REQUEST_METHOD"] . " " . $req["REQUEST_URI"] , "<pre>" . json_encode($logData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "</pre>"];

    mailer("SkyTeam","jsungmin6@naver.com","jsungmin6@naver.com","ALLRoomRealServerError",implode($content) );

    //sendDebugEmail("Error : " . $req["REQUEST_METHOD"] . " " . $req["REQUEST_URI"] , "<pre>" . json_encode($logData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "</pre>");
}


function getLogs($path)
{
    $fp = fopen($path, "r", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$fp) echo "error";

    while (!feof($fp)) {
        $str = fgets($fp, 10000);
        $arr[] = $str;
    }
    for ($i = sizeof($arr) - 1; $i >= 0; $i--) {
        echo $arr[$i] . "<br>";
    }
//        fpassthru($fp);
    fclose($fp);
}

function getComplexNameFromComplexIdx($complexIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT complexName FROM Complex where complexIdx = :complexIdx;";

    $st = $pdo->prepare($query);
    $st->bindParam(':complexIdx',$complexIdx,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_NUM);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0][0];
}

function getPwdFromEmail($userEmail)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT userPwd FROM User where userEmail = :userEmail;";

    $st = $pdo->prepare($query);
    $st->bindParam(':userEmail',$userEmail,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_NUM);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0][0];
}

function getUserIdxFromEmail($userEmail)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT userIdx FROM User where userEmail = :userEmail;";

    $st = $pdo->prepare($query);
    $st->bindParam(':userEmail',$userEmail,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_NUM);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0][0];
}


function getRegionLatitudeFromAddress($address)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT latitude FROM Region where dongAddress = :address;";

    $st = $pdo->prepare($query);
    $st->bindParam(':address',$address,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_NUM);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0][0];
}

function getRegionLongitudeFromAddress($address)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT longitude FROM Region where dongAddress = :address;";

    $st = $pdo->prepare($query);
    $st->bindParam(':address',$address,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_NUM);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0][0];
}

function getStationLatitudeFromStationName($station)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT latitude FROM Station where stationName = :stationName;";

    $st = $pdo->prepare($query);
    $st->bindParam(':stationName',$station,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_NUM);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0][0];
}

function getStationLongitudeFromStationName($station)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT longitude FROM Station where stationName = :stationName;";

    $st = $pdo->prepare($query);
    $st->bindParam(':stationName',$station,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_NUM);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0][0];
}



$homeInterestRoomDefault="{
    \"result\": [
        {
            \"searchLog\": \"서울특별시 송파구 잠실동\",
            \"roomNum\": \"2개의 방\",
            \"dongImg\": \"https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/region%2F%EC%A7%80%EC%97%AD1.PNG?alt=media&token=7b6042e5-70c9-47e0-b773-410123114ee5\",
            \"roomType\": \"원룸,투ㆍ쓰리룸,오피스텔\"
        },
        {
            \"searchLog\": \"서울특별시 강남구 삼성동\",
            \"roomNum\": \"1개의 방\",
            \"dongImg\": \"https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/region%2F%EC%A7%80%EC%97%AD4.PNG?alt=media&token=a70413c4-c85e-4254-983f-db54dec20739\",
            \"roomType\": \"원룸,투ㆍ쓰리룸,오피스텔\"
        }
    ],
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"관심지역 모든 방 리스트\"
}";

$homeInterestComplexDefault="{
    \"result\": [
        {
            \"complexIdx\": \"5\",
            \"complexName\": \"하이데어\",
            \"complexImg\": \"https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/complex%2Fmain%2Fc2.PNG?alt=media&token=da998b88-84c5-4586-9271-a270c67bdec2\",
            \"roomNum\": \"0개의 방\",
            \"kindOfBuilding\": \"오피스텔\",
            \"householdNum\": \"175세대\",
            \"completionDate\": \"2015.01\"
        },
        {
            \"complexIdx\": \"1\",
            \"complexName\": \"파로스타워\",
            \"complexImg\": \"https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/complex%2F1%2FcomplexImg%2Fc1-2.PNG?alt=media&token=2583154a-27b1-4b61-85ab-6948e731a17d\",
            \"roomNum\": \"2개의 방\",
            \"kindOfBuilding\": \"오피스텔\",
            \"householdNum\": \"16세대\",
            \"completionDate\": \"2020.06\"
        },
        {
            \"complexIdx\": \"2\",
            \"complexName\": \"위너스\",
            \"complexImg\": \"https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/complex%2Fmain%2Fc2.PNG?alt=media&token=da998b88-84c5-4586-9271-a270c67bdec2\",
            \"roomNum\": \"2개의 방\",
            \"kindOfBuilding\": \"오피스텔\",
            \"householdNum\": \"175세대\",
            \"completionDate\": \"1991.01\"
        }
    ],
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"관심지역 모든 단지 리스트\"
}";
function getHospital($latitude,$longitude)
{
    $url ="http://apis.data.go.kr/B552657/HsptlAsembySearchService/getHsptlMdcncLcinfoInqire?WGS84_LON=$longitude&WGS84_LAT=$latitude&pageNo=1&numOfRows=1&ServiceKey=84FQuuCUDfMgaTG0o0l6pgq%2BzKhYMcMnID33w1LkgOpXXPXW%2B9qG7Mddz%2BUo6nLs%2F0SFwuCDr2YU%2BK77VQWCIQ%3D%3D";


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);    //요청 결과를 문자열로 반환
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);      //connection timeout 10초

    $response = curl_exec($ch);

    curl_close($ch);

    $object = simplexml_load_string($response);

    $dutyName=$object->body->items->item->dutyName;
    $distance=$object->body->items->item->distance;
    $dutyName = (array) $dutyName;
    $distance = (array) $distance;

    $res->amenityName=$dutyName[0];
    $res->distance=$distance[0];
    $res->amenityType='병원';

    return $res;

}


function getUniversityLatitudeFromUniversityName($university)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT latitude FROM University where universityName = :universityName;";

    $st = $pdo->prepare($query);
    $st->bindParam(':universityName',$university,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_NUM);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0][0];
}

function getUniversityLongitudeFromUniversityName($university)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT longitude FROM University where universityName = :universityName;";

    $st = $pdo->prepare($query);
    $st->bindParam(':universityName',$university,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_NUM);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0][0];
}
