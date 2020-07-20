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
         * API No. 0
         * API Name : 테스트 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "roomList":

            http_response_code(200);
            $res->result = test();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 0
         * API Name : 테스트 Path Variable API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "roomDetail":

            $roomIdx=$vars["roomIdx"];

            //유효한 방 인덱스 인지 검사.
            if(!isValidRoomIdx($roomIdx)){
                http_response_code(200);
                $res->result = roomDetail($roomIdx);
                $res->isSuccess = TRUE;
                $res->code = 200;
                $res->message = "검색 결과가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            http_response_code(200);

            //방정보를 보여주기위해 여러 함수를 사용해 합쳐야 하기 때문에 리스트를 만듬.
            $result=[];
            $result['roomInfo'] = roomDetail($roomIdx); //방 정보

            //단지에 포함된 방이라면 정보를 주고 아니라면 null값 반환.
            if(!isValidRoomInComplex($roomIdx)){
                $result['complexInfo']="null";
            } else {
                $result['complexInfo'] = ComplexInRoomDetail($roomIdx);
            }

            //옵션이 없으면 null값 반환
            if(!isValidRoomOption($roomIdx)){
                $result['option']="null";
            } else {
                $result['option'] = roomOption($roomIdx);
            }
            //보안장치가 없으면 null값 반환
            if(!isValidRoomSecurity($roomIdx)){
                $result['security']="null";
            } else {
                $result['security'] = roomSecurity($roomIdx);
            }

            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "방 상세정보";
            echo json_encode($res);
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
