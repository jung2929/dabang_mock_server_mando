<?php
require 'function.php';

const JWT_SECRET_KEY = "AllRoomProject";

//정규식 패턴
$patternName="/^[a-zA-Z가-힣 ]*$/";
$patternPwd = "/^.*(?=^.{8,15}$)(?=.*\d)(?=.*[a-zA-Z])(?=.*[!@#$%^&+=]).*$/";//특수문자,문자,숫자 포함 형태의 8~15자리 이내의 암호 정규식
$patternPhone = "/^01[0179][0-9]{7,8}$/";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));

ini_set('default_charset', 'utf8mb4');

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
         * API No. 32
         * API Name : 좋아요 클릭/해제 API
         * 마지막 수정 날짜 : 20.07.25
         */
        case "changeLikes":

            //jwt토큰에서 userIdx 얻기
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];


            //body에서 roomIdx or complexIdx 얻기
            $roomIdx=$req->roomIdx;
            $complexIdx=$req->complexIdx;

            //jwt 토큰 검사
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "유효하지 않은 토큰입니다, 로그인을 해주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            //jwt토큰에서 userIdx 얻기
            $userInfo=getDataByJWToken($jwt,JWT_SECRET_KEY);
            $userIdx=$userInfo->userIdx;

            if (!isValidUserIdx($userIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "존재하지 않는 회원입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            //roomIdx 와 complexIdx 둘 다 들어오면 오류표시
            if(isset($roomIdx) and isset($complexIdx)){
                $res->isSuccess = False;
                $res->code = 210;
                $res->message = "방 혹은 단지를 선택해 주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            //방이 들어오고 그게 있는 방인지 검사
            if(!isValidRoomIdx($roomIdx) and isset($roomIdx))
            {
                $res->isSuccess = False;
                $res->code = 211;
                $res->message = "존재하지 않는 방입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            //단지가 들어오고 그게 있는 단지인지 검사
            if(!isValidComplexIdx($complexIdx) and isset($complexIdx))
            {
                $res->isSuccess = False;
                $res->code = 212;
                $res->message = "존재하지 않는 단지입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            //방일 경우 이미 테이블에 like가 있으면 update 로 "Y"면 "N"으로, "N"이면 "Y"로 바꿈
            if(isRoomLike($userIdx,$roomIdx) and isset($roomIdx)){
                http_response_code(200);
                changeRoomLikes($userIdx,$roomIdx);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "방 좋아요 클릭/해제";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            //단지일 경우 이미 테이블에 like가 있으면 update 로 "Y"면 "N"으로, "N"이면 "Y"로 바꿈
            if(isComplexLike($userIdx,$complexIdx) and isset($complexIdx)){
                http_response_code(200);
                changeComplexLikes($userIdx,$complexIdx);
                $res->isSuccess = TRUE;
                $res->code = 101;
                $res->message = "단지 좋아요 클릭/해제";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            //방일 경우 테이블에 없으면 Y로 생성
            if(!isRoomLike($userIdx,$roomIdx) and isset($roomIdx)){
                http_response_code(200);
                createRoomLikes($userIdx,$roomIdx);
                $res->isSuccess = TRUE;
                $res->code = 102;
                $res->message = "방 좋아요 생성";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            //단지일 경우 테이블에 없으면 Y로 생성
            if(!isComplexLike($userIdx,$complexIdx) and isset($complexIdx)){
                http_response_code(200);
                createComplexLikes($userIdx,$complexIdx);
                $res->isSuccess = TRUE;
                $res->code = 103;
                $res->message = "단지 좋아요 생성";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            break;
        /*
         * API No. 23
         * API Name : 내 정보 조회 API(회원용)
         * 마지막 수정 날짜 : 20.07.25
         */
        case "userInfo":

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

            http_response_code(200);
            $res->result = userInfo($userIdx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "내 정보 보기";
            echo json_encode($res);
            break;
        /*
         * API No. 24
         * API Name : 회원가입 API(수정필요)
         * 마지막 수정 날짜 : 20.07.25
         */

        case "createUser":

            $userName=$req->userName;
            $userEmail=$req->userEmail;
            $userPwd=$req->userPwd;
            $userPwdCheck=$req->userPwdCheck;
            $userPhone=$req->userPhone;


            if(!isset($userEmail)) {
                $res->isSuccess = FALSE;
                $res->code = 210;
                $res->message = "이메일을 입력해 주십시오";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(!isset($userName)) {
                $res->isSuccess = FALSE;
                $res->code = 211;
                $res->message = "이름을 입력해 주십시오";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(!isset($userPwd)) {
                $res->isSuccess = FALSE;
                $res->code = 212;
                $res->message = "비밀번호을 입력해 주십시오";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(!isset($userPwdCheck)) {
                $res->isSuccess = FALSE;
                $res->code = 213;
                $res->message = "비밀번호 확인을 입력해 주십시오";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            // 이메일의 입력 형식 검증, 이메일은 php에서 제공하는 필터가 있다.
            if (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
                $res->isSuccess = FALSE;
                $res->code = 220;
                $res->message = "이메일을 정확히 입력해 주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!preg_match($patternName, $userName)) {
                $res->isSuccess = FALSE;
                $res->code = 221;
                $res->message = "이름을 정확히 입력해 주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!preg_match($patternPwd, $userPwd)) {
                $res->isSuccess = FALSE;
                $res->code = 222;
                $res->message = "비밀번호는 특수문자,문자,숫자 포함 형태의 8~15자리만 가능합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if ($userPwd!=$userPwdCheck) {
                $res->isSuccess = FALSE;
                $res->code = 223;
                $res->message = "비밀번호 확인이 일치하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!preg_match($patternPhone, $userPhone)) {
                $res->isSuccess = FALSE;
                $res->code = 224;
                $res->message = "전화번호를 제대로 입력해 주세요.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            //가입된 email이 있는지 확인
            if(isValidUser($userEmail)){
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "이미 가입 된 이메일 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            //가입된 전화번호가 있는지 확인
            if(isValidPhone($userPhone)){
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "이미 가입 된 전화번호 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            http_response_code(200);
            createUser($userName, $userEmail, $userPwd, $userPhone);
            $res->result = userInfoCreate($userEmail);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "정상적으로 회원가입 되었습니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 33
         * API Name : 일반 로그인 API
         * 마지막 수정 날짜 : 20.07.25
         */

        case "userBasicLogin":

            $userEmail=$req->userEmail;
            $userPwd=$req->userPwd;


            //이메일 입력했는지 여부
            if(!isset($userEmail)) {
                $res->isSuccess = FALSE;
                $res->code = 210;
                $res->message = "이메일을 입력해 주십시오";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            //비밀번호 입력 했는지 여부
            if(!isset($userPwd)) {
                $res->isSuccess = FALSE;
                $res->code = 212;
                $res->message = "비밀번호을 입력해 주십시오";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            //등록된 이메일이 있는지 여부
            if(!isValidUser($userEmail)){
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "가입하지 않은 이메일 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            //이메일과 비밀번호 일치하는지 검사
            if($userPwd!=getPwdFromEmail($userEmail)){
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "이메일과 비밀번호가 일치하지 않습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            //이메일로 userIdx알아내기
            $userIdx=getUserIdxFromEmail($userEmail);

            //jwt토큰 만들기
            $jwt = getJWToken($userIdx, $userEmail, JWT_SECRET_KEY);

            http_response_code(200);
            $res->result->userIdx=$userIdx;
            $res->result->jwt=$jwt;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "정상적으로 로그인 되었습니다.";
            echo json_encode($res);
            break;



        case "oauthLogin":

            $returnCode = $_GET["code"]; // 서버로 부터 토큰을 발급받을 수 있는 코드를 받아옴
            $restAPIKey = "553885184cb356d5b2c85be4298adf52"; // 본인의 REST API KEY를 입력
            $callbacURI = urlencode("http://mandumerry.ga/users/oauth/login"); // 본인의 Call Back URL을 입력

            $returnUrl = "https://kauth.kakao.com/oauth/token?grant_type=authorization_code&client_id=".$restAPIKey."&redirect_uri=".$callbacURI."&code=".$returnCode;

            $isPost = false;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $returnUrl);
            curl_setopt($ch, CURLOPT_POST, $isPost);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $headers = array();
            $loginResponse = curl_exec ($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close ($ch);

//            var_dump($loginResponse); // Kakao API 서버로 부터 받아온 값

            $accessToken= json_decode($loginResponse)->access_token; //Access Token만 따로 뺌

//            echo "<br><br> accessToken : ".$accessToken;

            $header = "Bearer ".$accessToken; // Bearer 다음에 공백 추가
            $getProfileUrl = "https://kapi.kakao.com/v2/user/me";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $getProfileUrl);
            curl_setopt($ch, CURLOPT_POST, $isPost);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $headers = array();
            $headers[] = "Authorization: ".$header;
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $profileResponse = curl_exec ($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close ($ch);

//            var_dump($profileResponse); // Kakao API 서버로 부터 받아온 값

            $profileResponse = json_decode($profileResponse);

            $userId = $profileResponse->id;
            $userName = $profileResponse->properties->nickname;
            $userEmail = $profileResponse->kakao_account->email;

//            echo "<br><br> userId : ".$userId;
//            echo "<br> userName : ".$userName;
//            echo "<br> userEmail : ".$userEmail;

//

            if(!isset($userEmail)) {
                $res->isSuccess = FALSE;
                $res->code = 210;
                $res->message = "이메일을 입력해 주십시오";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(!isset($userName)) {
                $res->isSuccess = FALSE;
                $res->code = 211;
                $res->message = "이름을 입력해 주십시오";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            //가입된 email이 있으면 jwt 토큰 발급, 없으면 회원가입.
            if(isValidUser($userEmail)){

                //이메일로 userIdx알아내기
                $userIdx=getUserIdxFromEmail($userEmail);

                //jwt토큰 만들기
                $jwt = getJWToken($userIdx, $userEmail, JWT_SECRET_KEY);

                http_response_code(200);
                $res->result->userIdx=$userIdx;
                $res->result->jwt=$jwt;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "정상적으로 로그인 되었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            } else {
                //회원가입 시키기
                createSnsUser($userName, $userEmail);
                //이메일로 userIdx알아내기
                $userIdx=getUserIdxFromEmail($userEmail);
                //jwt토큰 만들기
                $jwt = getJWToken($userIdx, $userEmail, JWT_SECRET_KEY);

                $res->result->userIdx=$userIdx;
                $res->result->jwt=$jwt;
                $res->isSuccess = TRUE;
                $res->code = 101;
                $res->message = "회원가입 후 로그인 되었습니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            break;


        case "testPost":
            http_response_code(200);
            $res->result = testPost($req->name);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        case "kakaoCallback":

            $restAPIKey = "553885184cb356d5b2c85be4298adf52"; //본인의 REST API KEY를 입력해주세요
            $callbacURI = urlencode("http://mandumerry.ga/users/oauth/login"); //본인의 Call Back URL을 입력해주세요
            $kakaoLoginUrl = "https://kauth.kakao.com/oauth/authorize?client_id=".$restAPIKey."&redirect_uri=".$callbacURI."&response_type=code";

            echo $kakaoLoginUrl;
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
