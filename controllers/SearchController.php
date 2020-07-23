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
        * API No. 13
        * API Name :  추천 검색어 API
        * 마지막 수정 날짜 : 20.07.24
        */
        case "searchList":


            $keyWord=$_GET['keyWord'];

            if(!searchList($keyWord)){
                $res->isSuccess = False;
                $res->code = 200;
                $res->message = "검색 결과가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            http_response_code(200);
            $res->result = searchList($keyWord);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "검색어 리스트 출력";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 14
         * API Name : 유저 최근검색 API(회원용)
         * 마지막 수정 날짜 : 2020.07.23
         */
        case "searchRecently":

            $userIdx=$vars['userIdx'];

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $userInfo=getDataByJWToken($jwt,JWT_SECRET_KEY);
            $jwtUserIdx=$userInfo->userIdx;

            if($jwtUserIdx!=$userIdx){
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "권한이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(!isSearchRecently($userIdx)){
                $res->isSuccess = False;
                $res->code = 200;
                $res->message = "최근 검색 없음";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            http_response_code(200);
            $res->result = searchRecently($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "검색어 리스트 출력";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 15
         * API Name : 검색기록 전체 삭제 API(회원용)
         * 마지막 수정 날짜 : 20.07.24
         */
        case "deleteSearchRecord":

            $userIdx=$vars['userIdx'];

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $userInfo=getDataByJWToken($jwt,JWT_SECRET_KEY);
            $jwtUserIdx=$userInfo->userIdx;

            if($jwtUserIdx!=$userIdx){
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "권한이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
//                $errorLogs=(Object)Array();
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(!isSearchRecently($userIdx)){
                $res->isSuccess = False;
                $res->code = 200;
                $res->message = "최근 검색이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            http_response_code(200);
            $res->result = deleteSearchRecord($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "최근검색 리스트 삭제";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;



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
