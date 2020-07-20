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

            //필수 쿼리스트링 받기
            $roomType=$_GET['roomType'];
            $maintenanceCostMin=$_GET['maintenanceCostMin'];
            $maintenanceCostMax=$_GET['maintenanceCostMax'];
            $exclusiveAreaMin=$_GET['exclusiveAreaMin'];
            $exclusiveAreaMax=$_GET['exclusiveAreaMax'];

            //지역 or 단지소속, 중개사 소속 방 리스트 쿼리스트링
            $dong=$_GET['dong'];
            $complexIdx=$_GET['complexIdx'];
            $agencyIdx=$_GET['agencyIdx'];

            //범위 쿼리스트링
            $latitude=$_GET['latitude'];
            $longitude=$_GET['longitude'];
            $scale=$_GET['scale'];

            //xx동으로 분류
            if($dong){
                $result=[];
                $result['roomNum'] = dongRoomNum($dong);
                $result['roomList'] = dongRoomList($roomType,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax,$dong);;

                http_response_code(200);
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "방 리스트 출력";
                echo json_encode($res);
                break;
            }

            if($complexIdx){
                //단지내 포함된 방의 수
                $result=[];
                $result['roomNum'] = complexRoomNum($complexIdx);

                //단지에 포함된 방이 없을 경우
                if(complexRoomList($complexIdx)){
                    $result['roomList'] = complexRoomList($complexIdx);;
                } else {
                    $result['roomList'] = "null";
                }

                http_response_code(200);
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "방 리스트 출력";
                echo json_encode($res);
                break;
            }

            if($agencyIdx){
                //단지내 포함된 방의 수
                $result=[];
                $result['roomNum'] = agencyRoomNum($agencyIdx);

                //단지에 포함된 방이 없을 경우
                if(agencyRoomList($agencyIdx)){
                    $result['roomList'] = agencyRoomList($agencyIdx);;
                } else {
                    $result['roomList'] = "null";
                }

                http_response_code(200);
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "방 리스트 출력";
                echo json_encode($res);
                break;
            }

            if($latitude and $longitude and $scale){
                //범위내 포함된 방의 수
                $result=[];
                $result['roomNum'] = rangeRoomNum($roomType,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax,$latitude,$longitude,$scale);

                //범위에 포함된 방이 없을 경우
                if(rangeRoomList($roomType,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax,$latitude,$longitude,$scale)){
                    $result['roomList'] = rangeRoomList($roomType,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax,$latitude,$longitude,$scale);
                } else {
                    $result['roomList'] = "null";
                }

                http_response_code(200);
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "방 리스트 출력";
                echo json_encode($res);
                break;
            }





            http_response_code(200);
            $res->result = roomList();
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
                $res->isSuccess = False;
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
        case "complexDetail":

            $complexIdx=$vars["complexIdx"];

            if(!isValidComplexIdx($complexIdx)){
                http_response_code(200);
                $res->isSuccess = False;
                $res->code = 200;
                $res->message = "검색 결과가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            //단지 정보 쿼리 여러개 쓰기 위해 분할.
            $result=[];
            $result['complexInfo'] = complexDetail($complexIdx); //단지 정보
            $result['sizeInfo'] = complexSizeInfo($complexIdx);
            $result['surroundingRecommendationComplex'] = surroundingRecommendationComplex($complexIdx);


            http_response_code(200);
            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "단지 상세정보";
            echo json_encode($res);
            break;

        case "agencyDetail":

            $agencyIdx=$vars["agencyIdx"];

            if(!isValidAgencyIdx($agencyIdx)){
                http_response_code(200);
                $res->isSuccess = False;
                $res->code = 200;
                $res->message = "검색 결과가 없습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            //단지 정보 쿼리 여러개 쓰기 위해 분할.
            $result=[];
            $result['agencyInfo'] = agencyDetail($agencyIdx); //중개사 정보
            $result['agencyMember'] = agencyMember($agencyIdx); //중개사 멤버

            http_response_code(200);
            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "중개사무소 상세정보보기";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }


} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
