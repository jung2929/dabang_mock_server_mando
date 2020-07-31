<?php
require 'function.php';

const JWT_SECRET_KEY = "AllRoomProject";
//보여줄 방 수
$recommendRoomLimitNum=5;
//두번째 추첨 알고리즘 발생시 필터링 할 때 주변 지역 범위 크기
$recommendRoomSecondScale=10000;
//관리비 최소 Default 값
$maintenanceCostMinDefault=0;
//관리비 최대 Default 값
$maintenanceCostMaxDefault=1000;
//범위면적 최소 Default 값
$exclusiveAreaMinDefault=0;
//범위면적 최대 Default 값
$exclusiveAreaMaxDefault=1000;
//방 종류 Default
$roomTypeDefault='원룸|투룸|쓰리룸|오피스텔';

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
        * API Name : 연락테이블에 데이터 삽입 API
        * 마지막 수정 날짜 : 20.07.28
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
        * API Name : 문의테이블에 데이터 삽입 API
        * 마지막 수정 날짜 : 20.07.28
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
        * API No. 34
        * API Name : 가까운 주요 주변시설 가져오기
        * 마지막 수정 날짜 : 20.07.28
        */
        case "amenitiesList":

            $latitude=$_GET['latitude'];
            $longitude=$_GET['longitude'];

            http_response_code(200);
            $res->result = getHospital($latitude,$longitude);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "가까운 주요 주변시설";
            echo json_encode($res);
            break;

        /*
        * API No. 36
        * API Name : 지도 CCTV 좌표
        * 마지막 수정 날짜 : 20.07.29
        */
        case "cctvList":

            $minLatitude=$_GET['minLatitude'];
            $minLongitude=$_GET['minLongitude'];
            $maxLatitude=$_GET['maxLatitude'];
            $maxLongitude=$_GET['maxLongitude'];

            http_response_code(200);
            $res->result = getCctvList($minLatitude,$minLongitude,$maxLatitude,$maxLongitude);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "주변 cctv 정보";
            echo json_encode($res);
            break;

        /*
        * API No. 37
        * API Name : 실시간 검색어
        * 마지막 수정 날짜 : 20.07.30
        */
        case "searchWord":

            $rows=$_GET['rows'];
            $pages=$_GET['pages'];

            http_response_code(200);

            //transaction 적용 (실시간 테이블 데이터 삭제, 생성)
            createSearchWord();

            $res->result = selectSearchWord($rows,$pages);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "실시간 검색어";
            echo json_encode($res);
            break;


        /*
        * API No. 38
        * API Name : 유저 추천하는 방 조회 API
        * 마지막 수정 날짜 : 20.07.30
        */
        case "recommendRooms":

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


            //지역 선정 알고리즘 : (조회,좋아요,문의,전화에 가중치)
            $address=getRecommendAddress($userIdx);

            //방 종류 선택 알고리즘 : Default 값 제외한 가장 많이 필터링 한 방 종류 선택
            $roomType=getRecommendRoomType($userIdx);

            //관리비와 면적 필터링 알고리즘 : 이상치(튀는 값) 제거위해 (m - 1.5σ) ~ (m + 1.5σ) 구간을 벗어나는 값을 이상치로 판단 후 제거, 제거 후 평균
            $maintenanceCostMin=getMaintenaceCostMin($userIdx);
            $maintenanceCostMax=getMaintenaceCostMax($userIdx);
            $exclusiveAreaMin=getExclusiveAreaMin($userIdx);
            $exclusiveAreaMax=getExclusiveAreaMax($userIdx);

//            echo "address : ".$address;
//            echo "roomType : ".$roomType;
//            echo "maintenanceCostMin : ".$maintenanceCostMin;
//            echo "maintenanceCostMax : ".$maintenanceCostMax;
//            echo "exclusiveAreaMin : ".$exclusiveAreaMin;
//            echo "exclusiveAreaMax : ".$exclusiveAreaMax;
//            echo "userIdx : ".$userIdx;


            //추천하는 방 수
            $recommendRoomNum=recommendRoomNum($roomType,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax,$address);

            $result=[];

            //추천하는 방 리스트 범위 내에서 랜덤으로 뽑아 줌.
            $result['roomList'] = recommendRoomList($roomType,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax,$address,$userIdx);


            //추천하는 방이 기준 이하일 경우
            if($recommendRoomNum<$recommendRoomLimitNum){
                //지역에서 좌표범위로 필터링을 바꾼후 필터링 조건 넓게 바꿔 줌.
                $latitude=getRegionLatitudeFromAddress($address);
                $longitude=getRegionLongitudeFromAddress($address);
                $scale=$recommendRoomSecondScale;
                $limit=$recommendRoomLimitNum-$recommendRoomNum;
                $maintenanceCostMin=$maintenanceCostMinDefault;
                $maintenanceCostMax=$maintenanceCostMaxDefault;
                $exclusiveAreaMin=$exclusiveAreaMinDefault;
                $exclusiveAreaMax=$exclusiveAreaMaxDefault;
                $roomType=$roomTypeDefault;

                //두번째 추천 결과 결과 내에서 랜덤으로 뽑음
                $result2=recommendSecondRoomList($roomType,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax,$latitude,$longitude,$scale,$userIdx);

                //뽑은 결과를 result에 합쳐줄 때 처음 추천된 방과 같은 방은 필터링 해준다.
                $i=0;
                while(count($result['roomList'])<$recommendRoomLimitNum)
                {
                    if(!in_array($result2[$i], $result['roomList']))
                    {
                        array_push($result['roomList'],$result2[$i]);
                    }
                    $i=$i+1;
                }

            }

            http_response_code(200);
            $res->result = $result;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "추천 방 목록";
            echo json_encode($res);
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
