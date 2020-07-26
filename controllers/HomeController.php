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
         * API No. 9
         * API Name : 나의 관심지역 조회 API (회원용)
         * 마지막 수정 날짜 : 20.07.23
         */
        case "homeRoomInterest":

            $userIdx=$vars['userIdx'];

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            //존재하는 회원인지 검사
            if (!isValidUserIdx($userIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "존재하지 않는 회원입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            //jwt 토큰 검사
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            //jwt 토큰에서 userIdx확인
            $userInfo=getDataByJWToken($jwt,JWT_SECRET_KEY);
            $jwtUserIdx=$userInfo->userIdx;

            //jwt 토큰의 userIdx 와 pass variable로 들어온 userIdx 일치하는지 검사
            if($jwtUserIdx!=$userIdx){
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "권한이 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(!isExistUserRegionView($userIdx)){
                echo $homeInterestRoomDefault;
                return;
            }

            http_response_code(200);
            $res->result = homeRoomInterest($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "관심지역 모든 방 리스트";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
        * API No. 10
        * API Name : 나의 관심 단지 조회 API (회원용)
        * 마지막 수정 날짜 : 20.07.23
        */
        case "homeComplexInterest":

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

            if(!isExistUserComplexView($userIdx)){
                echo $homeInterestComplexDefault;
                return;
            }

            http_response_code(200);
            $res->result = homeComplexInterest($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "관심지역 모든 단지 리스트";
            echo json_encode($res);
            break;
        /*
         * API No. 11
         * API Name : 추천 콘텐츠 조회 API
         * 마지막 수정 날짜 : 20.07.24
         */
        case "homeContent":
            http_response_code(200);
            $res->result = homeContent();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "나를 위한 추천 콘텐츠 리스트";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
        * API No. 12
        * API Name : 홈 이벤트 광고 조회 API
        * 마지막 수정 날짜 : 20.07.24
        */

        case "homeEvent":
            http_response_code(200);
            $res->result = homeEvent();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "홈 광고 공지";
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
