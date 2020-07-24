<?php
require 'function.php';

const JWT_SECRET_KEY = "AllRoomProject";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "index":
            echo "API Server2";
            break;
        case "ACCESS_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/access.log");
            break;
        case "ERROR_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/errors.log");
            break;
        /*
         * API No. 30
         * API Name : 테스트 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "createCallLog":

            $roomIdx=$req->roomIdx;

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userInfo=getDataByJWToken($jwt,JWT_SECRET_KEY);
            $userIdx=$userInfo->userIdx;

            http_response_code(200);
            createCallLog($userIdx,$roomIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "연락데이터 삽입 완료";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
        * API No. 31
        * API Name : 테스트 API
        * 마지막 수정 날짜 : 19.04.29
        */
        case "createInquireLog":

            $roomIdx=$req->roomIdx;

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userInfo=getDataByJWToken($jwt,JWT_SECRET_KEY);
            $userIdx=$userInfo->userIdx;

            http_response_code(200);
            createInquireLog($userIdx,$roomIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "문의데이터 삽입 완료";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 0
         * API Name : 테스트 Path Variable API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "testDetail":
            http_response_code(200);
            $res->result = testDetail($vars["testNo"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 0
         * API Name : 테스트 Body & Insert API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "testPost":
            http_response_code(200);
            $res->result = testPost($req->name);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
