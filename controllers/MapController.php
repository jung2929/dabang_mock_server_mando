<?php
require 'function.php';

const JWT_SECRET_KEY = "AllRoomProject";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
//방유형 검사
$patternRoomType = "/원룸|투쓰리룸|오피스텔|아파트/";
//위도 검사
$patternLatitude = "/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?)/";
//경도 검사
$patternLongitude = "/\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/";
//scale검사
$patternScale = "/^([1-9][0-9]*)$/";
//동 검사
$patternAddress = "/동$|면$|읍$/";
//모든지역 검사
$patternRegion = "/동$|면$|읍$|역$|교$/";
//역 검사
$patternStation = "/역$/";
//대학 검사
$patternUniversity = "/교$/";
//최소최대 관리비 검사
$patternMaintenanceCost = "/^(0|[1-9][0-9]*)$/";
//전용면적 검사
$patternArea = "/^(0|[1-9][0-9]*)$/";

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

            //주소,역 쿼리스트링
            $address=$_GET['address'];

            //범위 쿼리스트링
            $latitude=$_GET['latitude'];
            $longitude=$_GET['longitude'];
            $scale=$_GET['scale'];

            //기록을 위해 jwt토큰에서 유저인덱스 받아 냄
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userInfo=getDataByJWToken($jwt,JWT_SECRET_KEY);
            $jwtUserIdx=$userInfo->userIdx;


            //필수 쿼리스트링이 들어왔는지 검사
            if(empty($roomType) or !isset($maintenanceCostMin) or !isset($maintenanceCostMax) or !isset($exclusiveAreaMin) or !isset($exclusiveAreaMax)){
                http_response_code(200);
                $res->isSuccess = TRUE;
                $res->code = 210;
                $res->message = "조건을 입력해 주십시오.";
                echo json_encode($res);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(!isset($latitude) and !isset($address)){
                $res->isSuccess = FALSE;
                $res->code = 219;
                $res->message = "좌표 or address를 입력해 주십시오.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isset($latitude) and isset($address)){
                $res->isSuccess = FALSE;
                $res->code = 219;
                $res->message = "좌표 or address를 입력해 주십시오.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!preg_match($patternRoomType, $roomType)) {
                $res->isSuccess = FALSE;
                $res->code = 211;
                $res->message = "방유형 양식이 틀렸습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            if(isset($latitude) and !preg_match($patternLatitude, $latitude)) {
                $res->isSuccess = FALSE;
                $res->code = 212;
                $res->message = "위도 양식이 틀렸습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            if(isset($longitude) and !preg_match($patternLongitude, $longitude)) {
                $res->isSuccess = FALSE;
                $res->code = 213;
                $res->message = "경도 양식이 틀렸습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            if(isset($maintenanceCostMin) and !preg_match($patternMaintenanceCost, $maintenanceCostMin)) {
                $res->isSuccess = FALSE;
                $res->code = 214;
                $res->message = "관리비 양식이 틀렸습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isset($maintenanceCostMax) and !preg_match($patternMaintenanceCost, $maintenanceCostMax)) {
                $res->isSuccess = FALSE;
                $res->code = 214;
                $res->message = "관리비 양식이 틀렸습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            if(isset($exclusiveAreaMin) and !preg_match($patternArea, $exclusiveAreaMin)) {
                $res->isSuccess = FALSE;
                $res->code = 215;
                $res->message = "면적 양식이 틀렸습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isset($exclusiveAreaMax) and !preg_match($patternArea, $exclusiveAreaMax)) {
                $res->isSuccess = FALSE;
                $res->code = 215;
                $res->message = "면적 양식이 틀렸습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isset($scale) and !preg_match($patternScale, $scale)) {
                $res->isSuccess = FALSE;
                $res->code = 216;
                $res->message = "scale 양식이 틀렸습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isset($address) and !preg_match($patternRegion, $address)) {
                $res->isSuccess = FALSE;
                $res->code = 217;
                $res->message = "지역 양식이 틀렸습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            if(isset($address) and !isExistAddress($address) and !isExistStation($address) and !isExistUniversity($address)){
                $res->isSuccess = FALSE;
                $res->code = 218;
                $res->message = "존재하지 않는 지역 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }



            //검색을 위해 투쓰리룸으로 들어온 파라미터를 투룸과 쓰리룸으로 분리
            $roomType=str_replace('투쓰리룸','투룸|쓰리룸',$roomType);

            //xx동으로 분류
            if($address and preg_match($patternAddress, $address)){
                $result=[];

                $mapLatitude=getRegionLatitudeFromAddress($address);
                $mapLongitude=getRegionLongitudeFromAddress($address);

                $result['roomNum'] = addressRoomNum($address,$roomType);
                $result['mapLatitude'] = $mapLatitude;
                $result['mapLongitude'] = $mapLongitude;
                //범위에 포함된 방이 없을 경우
                if($result['roomNum']==0){
                    $result['roomList'] = "null";
                } else {
                    $result['roomList'] = addressRoomList($roomType,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax,$address,$userIdx);
                }

                //UserSearchLog 기록을 위해 다시 원상태로 복구
                $roomType=str_replace('투룸|쓰리룸','투쓰리룸',$roomType);
                //검색내용을 UserSearchLog 테이블에 기록
                insertUserSearchLog($jwtUserIdx,$roomType,$address,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax);

                http_response_code(200);
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "방 리스트 출력";
                echo json_encode($res);
                return;
            }

            if($latitude and $longitude and $scale){
                //범위내 포함된 방의 수
                $result=[];
                $result['roomNum'] = rangeRoomNum($roomType,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax,$latitude,$longitude,$scale);
                $result['mapLatitude'] = $latitude;
                $result['mapLongitude'] = $longitude;

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
                return;
            }

            if($address and preg_match($patternStation, $address)){
                //범위내 포함된 방의 수

                $mapLatitude=getStationLatitudeFromStationName($address);
                $mapLongitude=getStationLongitudeFromStationName($address);

                $result=[];
                $result['roomNum'] = stationRoomNum($roomType,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax,$address);
                $result['mapLatitude'] = $mapLatitude;
                $result['mapLongitude'] = $mapLongitude;

                //범위에 포함된 방이 없을 경우
                if(stationRoomList($roomType,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax,$address,$userIdx)){
                    $result['roomList'] = stationRoomList($roomType,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax,$address,$userIdx);
                } else {
                    $result['roomList'] = "null";
                }
                //UserSearchLog 기록을 위해 다시 원상태로 복구
                $roomType=str_replace('투룸|쓰리룸','투쓰리룸',$roomType);
                //검색내용을 UserSearchLog 테이블에 기록
                insertUserSearchLog($jwtUserIdx,$roomType,$address,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax);

                http_response_code(200);
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "방 리스트 출력";
                echo json_encode($res);
                return;
            }

            if($address and preg_match($patternUniversity, $address)){
                //범위내 포함된 방의 수

                $mapLatitude=getUniversityLatitudeFromUniversityName($address);
                $mapLongitude=getUniversityLongitudeFromUniversityName($address);

                $result=[];
                $result['roomNum'] = UniversityRoomNum($roomType,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax,$address);
                $result['mapLatitude'] = $mapLatitude;
                $result['mapLongitude'] = $mapLongitude;

                //범위에 포함된 방이 없을 경우
                if(UniversityRoomList($roomType,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax,$address,$userIdx)){
                    $result['roomList'] = UniversityRoomList($roomType,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax,$address,$userIdx);
                } else {
                    $result['roomList'] = "null";
                }
                //UserSearchLog 기록을 위해 다시 원상태로 복구
                $roomType=str_replace('투룸|쓰리룸','투쓰리룸',$roomType);
                //검색내용을 UserSearchLog 테이블에 기록
                insertUserSearchLog($jwtUserIdx,$roomType,$address,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax);

                http_response_code(200);
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "방 리스트 출력";
                echo json_encode($res);
                return;
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

            //주소,역 쿼리스트링
            $address=$_GET['address'];

            //범위 쿼리스트링
            $latitude=$_GET['latitude'];
            $longitude=$_GET['longitude'];
            $scale=$_GET['scale'];


            //필수 쿼리스트링이 들어왔는지 검사
            if(empty($roomType)){
                http_response_code(200);
                $res->isSuccess = TRUE;
                $res->code = 210;
                $res->message = "조건을 입력해 주십시오.";
                echo json_encode($res);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(!isset($latitude) and !isset($address)){
                $res->isSuccess = FALSE;
                $res->code = 219;
                $res->message = "좌표 or address를 입력해 주십시오.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isset($latitude) and isset($address)){
                $res->isSuccess = FALSE;
                $res->code = 219;
                $res->message = "좌표 or address를 입력해 주십시오.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }



            if (!preg_match($patternRoomType, $roomType)) {
                $res->isSuccess = FALSE;
                $res->code = 211;
                $res->message = "방유형 양식이 틀렸습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            if(isset($latitude) and !preg_match($patternLatitude, $latitude)) {
                $res->isSuccess = FALSE;
                $res->code = 212;
                $res->message = "위도 양식이 틀렸습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }



            if(isset($longitude) and !preg_match($patternLongitude, $longitude)) {
                $res->isSuccess = FALSE;
                $res->code = 213;
                $res->message = "경도 양식이 틀렸습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }



            if(isset($exclusiveAreaMax) and !preg_match($patternScale, $scale)) {
                $res->isSuccess = FALSE;
                $res->code = 216;
                $res->message = "scale 양식이 틀렸습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            if(isset($address) and !preg_match($patternRegion, $address)) {
                $res->isSuccess = FALSE;
                $res->code = 217;
                $res->message = "지역 양식이 틀렸습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isset($address) and !isExistAddress($address) and !isExistStation($address) and !isExistUniversity($address)){
                $res->isSuccess = FALSE;
                $res->code = 218;
                $res->message = "존재하지 않는 지역 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            $roomType=str_replace('투쓰리룸','투룸|쓰리룸',$roomType);

            //xx동으로 분류
            if($address and preg_match($patternAddress, $address)){
                $result=[];
                $result['complexNum'] = addressComplexNum($address);

                //범위에 포함된 방이 없을 경우
                if($result['complexNum']==0){
                    $result['complexList'] = "null";
                } else {
                    $result['complexList'] = addressComplexList($roomType,$address);
                }

                http_response_code(200);
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "단지 리스트 출력";
                echo json_encode($res);
                return;
            }

            //위경도로 분류
            if($latitude and $longitude and $scale){
                //범위내 포함된 방의 수
                $result=[];
                $result['complexNum'] = rangeComplexNum($roomType,$latitude,$longitude,$scale);

                //범위에 포함된 방이 없을 경우
                if(rangeComplexList($roomType,$latitude,$longitude,$scale,$userIdx)){
                    $result['complexList'] = rangeComplexList($roomType,$latitude,$longitude,$scale);
                } else {
                    $result['complexList'] = "null";
                }

                http_response_code(200);
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "단지 리스트 출력";
                echo json_encode($res);
                return;
            }

            if($address and preg_match($patternStation, $address)){
                //범위내 포함된 방의 수

                $result=[];
                $result['complexNum'] = stationComplexNum($roomType,$address);

                //범위에 포함된 방이 없을 경우
                if(stationComplexList($roomType,$address)){
                    $result['complexList'] = stationComplexList($roomType,$address);
                } else {
                    $result['complexList'] = "null";
                }

                http_response_code(200);
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "단지 리스트 출력";
                echo json_encode($res);
                return;
            }

            if($address and preg_match($patternUniversity, $address)){
                //범위내 포함된 방의 수

                $result=[];
                $result['complexNum'] = universityComplexNum($roomType,$address);

                //범위에 포함된 방이 없을 경우
                if(universityComplexList($roomType,$address)){
                    $result['complexList'] = universityComplexList($roomType,$address);
                } else {
                    $result['complexList'] = "null";
                }

                http_response_code(200);
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "방 리스트 출력";
                echo json_encode($res);
                return;
            }

            break;

        /*
        * API No. 3
        * API Name : 중개사무소 조회 API
        * 마지막 수정 날짜 : 20.07.24
        */


        case "agencyList":

            //주소, 역 쿼리스트링
            $address=$_GET['address'];

            //범위 쿼리스트링
            $latitude=$_GET['latitude'];
            $longitude=$_GET['longitude'];
            $scale=$_GET['scale'];


            if(!isset($latitude) and !isset($address)){
                $res->isSuccess = FALSE;
                $res->code = 219;
                $res->message = "좌표 or address를 입력해 주십시오.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isset($latitude) and isset($address)){
                $res->isSuccess = FALSE;
                $res->code = 219;
                $res->message = "좌표 or address를 입력해 주십시오.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isset($latitude) and !preg_match($patternLatitude, $latitude)) {
                $res->isSuccess = FALSE;
                $res->code = 212;
                $res->message = "위도 양식이 틀렸습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isset($longitude) and !preg_match($patternLongitude, $longitude)) {
                $res->isSuccess = FALSE;
                $res->code = 213;
                $res->message = "경도 양식이 틀렸습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isset($scale) and !preg_match($patternScale, $scale)) {
                $res->isSuccess = FALSE;
                $res->code = 216;
                $res->message = "scale 양식이 틀렸습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isset($address) and !preg_match($patternRegion, $address)) {
                $res->isSuccess = FALSE;
                $res->code = 217;
                $res->message = "지역 양식이 틀렸습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isset($address) and !isExistAddress($address) and !isExistStation($address) and !isExistUniversity($address)){
                $res->isSuccess = FALSE;
                $res->code = 218;
                $res->message = "존재하지 않는 지역 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            //xx동으로 분류
            if($address and preg_match($patternAddress, $address)){
                $result=[];
                $result['agencyNum'] = addressAgencyNum($address);

                //범위에 포함된 방이 없을 경우
                if(addressAgencyList($address)){
                    $result['agencyList'] = addressAgencyList($address);
                } else {
                    $result['agencyList'] = "null";
                }

                http_response_code(200);
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "중개사 리스트 출력";
                echo json_encode($res);
                return;
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
                return;
            }


            //xx역으로 분류
            if($address and preg_match($patternStation, $address)){
                $result=[];
                $result['agencyNum'] = stationAgencyNum($address);

                //범위에 포함된 방이 없을 경우
                if(stationAgencyList($address)){
                    $result['agencyList'] = stationAgencyList($address);
                } else {
                    $result['agencyList'] = "null";
                }

                http_response_code(200);
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "중개사 리스트 출력";
                echo json_encode($res);
                return;
            }

            if($address and preg_match($patternUniversity, $address)){
                $result=[];
                $result['agencyNum'] = universityAgencyNum($address);

                //범위에 포함된 방이 없을 경우
                if(universityAgencyList($address)){
                    $result['agencyList'] = universityAgencyList($address);
                } else {
                    $result['agencyList'] = "null";
                }

                http_response_code(200);
                $res->result = $result;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "중개사 리스트 출력";
                echo json_encode($res);
                return;
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
                addErrorLogs($errorLogs, $res, $req);
                return;
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
                addErrorLogs($errorLogs, $res, $req);
                return;
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
        * 마지막 수정 날짜 : 20.07.25
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
                addErrorLogs($errorLogs, $res, $req);
                return;
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

            //UserRoomLog 테이블에 기록
            insertUserRoomlog($userIdx,$roomIdx);

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
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            //UserComplexLog 테이블에 기록
            insertUserComplexLog($userIdx,$complexIdx);
            insertComplexNameInUserSearchLog($userIdx,getComplexNameFromComplexIdx($complexIdx));

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
                addErrorLogs($errorLogs, $res, $req);
                return;
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
