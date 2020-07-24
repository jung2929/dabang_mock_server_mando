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
         * API No. 16
         * API Name : 유저 최근 본 방 조회 API(회원용)
         * 마지막 수정 날짜 : 19.07.24
         */
        case "userRoomView":

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

            http_response_code(200);
            $res->result = userRoomView($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "최근 본 방 리스트 출력";
            echo json_encode($res);
            break;
        /*
         * API No. 17
         * API Name : 유저 최근 본 단지 조회 API(회원용)
         * 마지막 수정 날짜 : 20.07.25
         */
        case "userComplexView":

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


            http_response_code(200);
            $res->result = userComplexView($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "최근 본 단지 리스트 출력";
            echo json_encode($res);
            break;

        /*
        * API No. 18
        * API Name : 유저 찜한 방 조회 API
        * 마지막 수정 날짜 : 20.07.25
        */
        case "userRoomLike":

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


            http_response_code(200);
            $res->result = userRoomLike($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "찜한 방 리스트 출력";
            echo json_encode($res);
            break;

        /*
        * API No. 19
        * API Name : 방 끼리 비교하기 API
        * 마지막 수정 날짜 : 20.07.25
        */
        case "roomCompare":

            $roomIdx1=$_GET['roomIdx1'];
            $roomIdx2=$_GET['roomIdx2'];
            $roomIdx3=$_GET['roomIdx3'];


            //방이 최소 2개 있어야 함.
            if(!isset($roomIdx1) or !isset($roomIdx2)){
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "비교할 방 인덱스를 입력해 주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            //존재하는 방인지 검사
            if (!isValidRoomIdx($roomIdx1) or !isValidRoomIdx($roomIdx2)) {
                $res->isSuccess = FALSE;
                $res->code = 210;
                $res->message = "존재하지 않는 방입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            //3번째 방이 있다면 방이 존재하는지 검사.
            if(!isValidRoomIdx($roomIdx3) and isset($roomIdx3)){
                $res->isSuccess = FALSE;
                $res->code = 210;
                $res->message = "존재하지 않는 방입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            //겹치는 방이 있는지 검사
            if(($roomIdx1==$roomIdx2) or ($roomIdx2==$roomIdx3) or ($roomIdx1==$roomIdx3)){
                $res->isSuccess = FALSE;
                $res->code = 211;
                $res->message = "같은 방이 존재합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            http_response_code(200);
            $res->result = roomCompare($roomIdx1,$roomIdx2,$roomIdx3);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "비교하기 리스트";
            echo json_encode($res);
            break;


        /*
        * API No. 20
        * API Name : 유저 찜한 단지 조회 API(회원용)
        * 마지막 수정 날짜 : 20.07.25
        */

        case "userComplexLike":

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


            http_response_code(200);
            $res->result = userComplexLike($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "찜한 단지 리스트 출력";
            echo json_encode($res);
            break;


        /*
        * API No. 21
        * API Name : 유저 문의한 방 조회 API(회원용)
        * 마지막 수정 날짜 : 20.07.25
        */

        case "userRoomInquiry":

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

            http_response_code(200);
            $res->result = userRoomInquiry($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "문의한 방 리스트 출력";
            echo json_encode($res);
            break;

        /*
         * API No. 22
         * API Name : 유저 문의한 방 조회 API(회원용)
         * 마지막 수정 날짜 : 20.07.25
         */

        case "userAgencyCall":

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

            http_response_code(200);
            $res->result = userAgencyCall($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "연락한 부동산 리스트 출력";
            echo json_encode($res);
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
