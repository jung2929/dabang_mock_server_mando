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
//            $res->result = test();
//            $res->isSuccess = TRUE;
//            $res->code = 100;
//            $res->message = "테스트 성공";
//            echo json_encode($res, JSON_NUMERIC_CHECK);
//            break;
            echo "xxxx";

            echo "{
    \"result\": [
        {
            \"roomIdx\": \"1\",
            \"complexName\": \"씨티라이프61\",
            \"price\": \"전세 1억9000\",
            \"kindOfRoom\": \"투룸\",
            \"thisFloor\": \"3층\"
            \"exclusiveArea\": \"26.4㎡\",
            \"maintenanceCost\": \"관리비 8만\",
            \"roomSummary\": \"대치동 인테리어 특급 원룸\"
            \"hashTag\": [
                \"전세가능\",
                \"분리형\"
            ],
            \"roomImg\":  [
                \"image경로\",
                \"image경로\"
            ],
            \"agencyIdx\": \"1\",
            \"agencyName\": \"택스앤리얼티세무사부동산중개\"
            \"agencyComment\": \"프리미엄 since 20.02.10\",
            \"agencyBossPicture\": \"image경로\",
            \"agencyRoomNum\": \"306개의 방\",
            \"quickInquiry\": \"Y\",
            \"roomNum\": \"225\",
            \"checkedRoom\": \"20.07.04\"
            \"plus\": \"Y\",
            \"heart\": \"N\"
        },
        {
            \"roomIdx\": \"2\",
            \"price\": \"전세 1억9000\",
            \"kindOfRoom\": \"투룸\",
            \"thisFloor\": \"3층\"
            \"exclusiveArea\": \"26.4㎡\",
            \"maintenanceCost\": \"관리비 8만\",
            \"roomSummary\": \"대치동 인테리어 특급 원룸\"
            \"hashTag\": [
                \"전세가능\",
                \"분리형\"
            ],
            \"roomImg\":  [
                \"image경로\",
                \"image경로\"
            ],
            \"agencyIdx\": \"2\",
            \"agencyName\": \"택스앤리얼티세무사부동산중개\"
            \"agencyComment\": \"프리미엄 since 20.02.10\",
            \"agencyBossPicture\": \"image경로\",
            \"agencyRoomNum\": \"306개의 방\",
            \"roomNum\": \"225\"
            \"checkedRoom\": \"20.07.04\"
            \"plus\": \"Y\",
            \"heart\": \"N\"
     }
    ],
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"방 리스트 출력\"
}";
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
