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
         * API No. 1
         * API Name : 방 리스트 조회 API
         * 마지막 수정 날짜 : 20.07.24
         */
        case "roomList":

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userInfo=getDataByJWToken($jwt,JWT_SECRET_KEY);
            $userIdx=$userInfo->userIdx;


            //필수 쿼리스트링 받기
            $roomType=$_GET['roomType'];
            $maintenanceCostMin=$_GET['maintenanceCostMin'];
            $maintenanceCostMax=$_GET['maintenanceCostMax'];
            $exclusiveAreaMin=$_GET['exclusiveAreaMin'];
            $exclusiveAreaMax=$_GET['exclusiveAreaMax'];

            //지역 쿼리스트링
            $dong=$_GET['dong'];

            //범위 쿼리스트링
            $latitude=$_GET['latitude'];
            $longitude=$_GET['longitude'];
            $scale=$_GET['scale'];


            //필수 쿼리스트링이 들어왔는지 검사
            if(empty($roomType) or !isset($maintenanceCostMin) or !isset($maintenanceCostMax) or !isset($exclusiveAreaMin) or !isset($exclusiveAreaMax)){
                http_response_code(200);
                $res->isSuccess = TRUE;
                $res->code = 210;
                $res->message = "필수 쿼리스트링이 누락 되었습니다.";
                echo json_encode($res);
                break;
            }

            //방유형 검사
            $pattern_01 = "/원룸|투쓰리룸|오피스텔|아파트/";
            if (!preg_match($pattern_01, $roomType)) {
                $res->isSuccess = FALSE;
                $res->code = 211;
                $res->message = "방유형 양식이 틀렸습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            //위도 검사
            $pattern_02 = "/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)/";
            if(isset($latitude)){
                if (!preg_match($pattern_02, $latitude)) {
                    $res->isSuccess = FALSE;
                    $res->code = 212;
                    $res->message = "위도 양식이 틀렸습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }}


            //경도 검사
            $pattern_03 = "/\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/";
            if(isset($longitude)){
                if (!preg_match($pattern_03, $longitude)) {
                    $res->isSuccess = FALSE;
                    $res->code = 213;
                    $res->message = "경도 양식이 틀렸습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }

            //최소최대 관리비 검사
            $pattern_04 = "/^(0|[1-9][0-9]*)$/";
            if(isset($maintenanceCostMin)){
                if (!preg_match($pattern_04, $maintenanceCostMin)) {
                    $res->isSuccess = FALSE;
                    $res->code = 214;
                    $res->message = "관리비 양식이 틀렸습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }

            if(isset($maintenanceCostMax)){
                if (!preg_match($pattern_04, $maintenanceCostMax)) {
                    $res->isSuccess = FALSE;
                    $res->code = 214;
                    $res->message = "관리비 양식이 틀렸습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }


            //전용면적 검사
            $pattern_04 = "/^(0|[1-9][0-9]*)$/";
            if(isset($exclusiveAreaMin)){
                if (!preg_match($pattern_04, $exclusiveAreaMin)) {
                    $res->isSuccess = FALSE;
                    $res->code = 215;
                    $res->message = "면적 양식이 틀렸습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }

            if(isset($exclusiveAreaMax)){
                if (!preg_match($pattern_04, $exclusiveAreaMax)) {
                    $res->isSuccess = FALSE;
                    $res->code = 215;
                    $res->message = "면적 양식이 틀렸습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }

            //scale검사
            $pattern_06 = "/^([1-9][0-9]*)$/";
            if(isset($scale)){
                if (!preg_match($pattern_06, $scale)) {
                    $res->isSuccess = FALSE;
                    $res->code = 216;
                    $res->message = "scale 양식이 틀렸습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }

            //동 검사
            $pattern_05 = "/동$/";
            if(isset($dong)){
                if (!preg_match($pattern_05, $dong)) {
                    $res->isSuccess = FALSE;
                    $res->code = 217;
                    $res->message = "지역 양식이 틀렸습니다.(~동)";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }

            $roomType=str_replace('투쓰리룸','투룸|쓰리룸',$roomType);

            //xx동으로 분류
            if($dong){
                $result=[];
                $result['roomNum'] = dongRoomNum($dong);

                //범위에 포함된 방이 없을 경우
                if($result['roomNum']==0){
                    $result['roomList'] = "null";
                } else {
                    $result['roomList'] = dongRoomList($roomType,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax,$dong,$userIdx);
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
                if(rangeRoomList($roomType,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax,$latitude,$longitude,$scale,$userIdx)){
                    $result['roomList'] = rangeRoomList($roomType,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax,$latitude,$longitude,$scale,$userIdx);
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

            break;
        /*
        * API No. 2
        * API Name : 단지 리스트 조회 API
        * 마지막 수정 날짜 : 20.07.24
        */

        case "complexList":

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userInfo=getDataByJWToken($jwt,JWT_SECRET_KEY);
            $userIdx=$userInfo->userIdx;

            //필수 쿼리스트링 받기
            $roomType=$_GET['roomType'];

            //지역 쿼리스트링
            $dong=$_GET['dong'];

            //범위 쿼리스트링
            $latitude=$_GET['latitude'];
            $longitude=$_GET['longitude'];
            $scale=$_GET['scale'];


            //필수 쿼리스트링이 들어왔는지 검사
            if(empty($roomType)){
                http_response_code(200);
                $res->isSuccess = TRUE;
                $res->code = 210;
                $res->message = "필수 쿼리스트링이 누락 되었습니다.";
                echo json_encode($res);
                break;
            }

            //방유형 검사
            $pattern_01 = "/원룸|투쓰리룸|오피스텔|아파트/";
            if (!preg_match($pattern_01, $roomType)) {
                $res->isSuccess = FALSE;
                $res->code = 211;
                $res->message = "방유형 양식이 틀렸습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            //위도 검사
            $pattern_02 = "/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)/";
            if(isset($latitude)){
                if (!preg_match($pattern_02, $latitude)) {
                    $res->isSuccess = FALSE;
                    $res->code = 212;
                    $res->message = "위도 양식이 틀렸습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }}


            //경도 검사
            $pattern_03 = "/\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/";
            if(isset($longitude)){
                if (!preg_match($pattern_03, $longitude)) {
                    $res->isSuccess = FALSE;
                    $res->code = 213;
                    $res->message = "경도 양식이 틀렸습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }


            //scale검사
            $pattern_06 = "/^([1-9][0-9]*)$/";
            if(isset($exclusiveAreaMax)){
                if (!preg_match($pattern_06, $scale)) {
                    $res->isSuccess = FALSE;
                    $res->code = 216;
                    $res->message = "scale 양식이 틀렸습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }

            //동 검사
            $pattern_05 = "/동$/";
            if(isset($dong)){
                if (!preg_match($pattern_05, $dong)) {
                    $res->isSuccess = FALSE;
                    $res->code = 217;
                    $res->message = "지역 양식이 틀렸습니다.(~동)";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }

            $roomType=str_replace('투쓰리룸','투룸|쓰리룸',$roomType);

            //xx동으로 분류
            if($dong){
                $result=[];
                $result['complexNum'] = dongComplexNum($dong);

                //범위에 포함된 방이 없을 경우
                if($result['complexNum']==0){
                    $result['complexList'] = "null";
                } else {
                    $result['complexList'] = dongComplexList($roomType,$dong);
                }

                http_response_code(200);
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "단지 리스트 출력";
                echo json_encode($res);
                break;
            }

            //위경도로 분류
            if($latitude and $longitude and $scale){
                //범위내 포함된 방의 수
                $result=[];
                $result['complexNum'] = rangeComplexNum($roomType,$latitude,$longitude,$scale,$userIdx);

                //범위에 포함된 방이 없을 경우
                if(rangeComplexList($roomType,$latitude,$longitude,$scale,$userIdx)){
                    $result['complexList'] = rangeComplexList($roomType,$latitude,$longitude,$scale,$userIdx);
                } else {
                    $result['complexList'] = "null";
                }

                http_response_code(200);
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "단지 리스트 출력";
                echo json_encode($res);
                break;
            }

            break;

        /*
        * API No. 3
        * API Name : 중개사무소 조회 API
        * 마지막 수정 날짜 : 20.07.24
        */


        case "agencyList":

            //지역 쿼리스트링
            $dong=$_GET['dong'];

            //범위 쿼리스트링
            $latitude=$_GET['latitude'];
            $longitude=$_GET['longitude'];
            $scale=$_GET['scale'];


            //위도 검사
            $pattern_02 = "/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)/";
            if(isset($latitude)){
                if (!preg_match($pattern_02, $latitude)) {
                    $res->isSuccess = FALSE;
                    $res->code = 212;
                    $res->message = "위도 양식이 틀렸습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }}


            //경도 검사
            $pattern_03 = "/\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/";
            if(isset($longitude)){
                if (!preg_match($pattern_03, $longitude)) {
                    $res->isSuccess = FALSE;
                    $res->code = 213;
                    $res->message = "경도 양식이 틀렸습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }


            //scale검사
            $pattern_06 = "/^([1-9][0-9]*)$/";
            if(isset($exclusiveAreaMax)){
                if (!preg_match($pattern_06, $scale)) {
                    $res->isSuccess = FALSE;
                    $res->code = 216;
                    $res->message = "scale 양식이 틀렸습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }

            //동 검사
            $pattern_05 = "/동$/";
            if(isset($dong)){
                if (!preg_match($pattern_05, $dong)) {
                    $res->isSuccess = FALSE;
                    $res->code = 217;
                    $res->message = "지역 양식이 틀렸습니다.(~동)";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }

            //xx동으로 분류
            if($dong){
                $result=[];
                $result['agencyNum'] = dongAgencyNum($dong);

                //범위에 포함된 방이 없을 경우
                if(dongAgencyList($dong)){
                    $result['agencyList'] = dongAgencyList($dong);
                } else {
                    $result['agencyList'] = "null";
                }

                http_response_code(200);
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "중개사 리스트 출력";
                echo json_encode($res);
                break;
            }

            //위경도로 분류
            if($latitude and $longitude and $scale){
                //범위내 포함된 방의 수
                $result=[];
                $result['agencyNum'] = rangeAgencyNum($latitude,$longitude,$scale);

                //범위에 포함된 방이 없을 경우
                if(rangeAgencyList($latitude,$longitude,$scale)){
                    $result['agencyList'] = rangeAgencyList($latitude,$longitude,$scale);
                } else {
                    $result['agencyList'] = "null";
                }

                http_response_code(200);
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "중개사 리스트 출력";
                echo json_encode($res);
                break;
            }

            break;

        /*
        * API No. 7
        * API Name : 단지에 포함된 방 조회 API
        * 마지막 수정 날짜 : 20.07.24
        */


        case "complexRoomList":

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userInfo=getDataByJWToken($jwt,JWT_SECRET_KEY);
            $userIdx=$userInfo->userIdx;

            $complexIdx=$vars['complexIdx'];


            //단지가 변수로 들어왔을 때 존재한는 단지인지 검사
            if(!isValidComplexIdx($complexIdx)){
                http_response_code(200);
                $res->isSuccess = False;
                $res->code = 200;
                $res->message = "존재하지 않는 단지";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            http_response_code(200);
            $res->result = complexRoomList($complexIdx,$userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "방 리스트 출력";
            echo json_encode($res);
            break;

        /*
        * API No. 8
        * API Name : 중개사무소에 포함된 방 조회 API
        * 마지막 수정 날짜 : 20.07.24
        */

        case "agencyRoomList":

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userInfo=getDataByJWToken($jwt,JWT_SECRET_KEY);
            $userIdx=$userInfo->userIdx;


            $agencyIdx=$vars['agencyIdx'];


            //중개사가 변수로 들어왔을 때 존재하는 중개사인지 검사
            if(!isValidAgencyIdx($agencyIdx)) {
                http_response_code(200);
                $res->isSuccess = False;
                $res->code = 200;
                $res->message = "존재하지 않는 중개사";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            //중개사내 포함된 방의 수
            $result=[];
            $result['roomNum'] = agencyRoomNum($agencyIdx);

            //중개사에 포함된 방이 없을 경우
            if(agencyRoomList($agencyIdx,$userIdx)){
                $result['roomList'] = agencyRoomList($agencyIdx,$userIdx);;
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

        /*
        * API No. 4
        * API Name : 방 상세정보 조회 API
        * 마지막 수정 날짜 : 20.07.24
        */

        case "roomDetail":

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userInfo=getDataByJWToken($jwt,JWT_SECRET_KEY);
            $userIdx=$userInfo->userIdx;

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
            $result['roomInfo'] = roomDetail($roomIdx,$userIdx); //방 정보

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
        * API No. 5
        * API Name : 단지 상세정보 조회 API
        * 마지막 수정 날짜 : 20.07.24
        */

        case "complexDetail":

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userInfo=getDataByJWToken($jwt,JWT_SECRET_KEY);
            $userIdx=$userInfo->userIdx;

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
            $result['complexInfo'] = complexDetail($complexIdx,$userIdx); //단지 정보
            $result['sizeInfo'] = complexSizeInfo($complexIdx);
            $result['surroundingRecommendationComplex'] = surroundingRecommendationComplex($complexIdx);


            http_response_code(200);
            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "단지 상세정보";
            echo json_encode($res);
            break;

        /*
        * API No. 6
        * API Name : 중개사무소 상세정보 조회 API
        * 마지막 수정 날짜 : 20.07.24
        */

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
