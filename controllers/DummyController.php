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
        case "roomList":
            echo "{
    \"result\": {
            \"roomNum\": \"225\",
            \"roomList\" : [
        {
            \"roomIdx\": \"1\",
            \"complexName\": \"씨티라이프61\",
            \"price\": \"전세 1억9000\",
            \"kindOfRoom\": \"투룸\",
            \"thisFloor\": \"3층\",
            \"exclusiveArea\": \"26.4㎡\",
            \"maintenanceCost\": \"관리비 8만\",
            \"roomSummary\": \"대치동 인테리어 특급 원룸\",
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
            \"checkedRoom\": \"20.07.04\",
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
            \"agencyName\": \"택스앤리얼티세무사부동산중개\",
            \"agencyComment\": \"프리미엄 since 20.02.10\",
            \"agencyBossPicture\": \"image경로\",
            \"agencyRoomNum\": \"306개의 방\",
            \"roomNum\": \"225\"
            \"checkedRoom\": \"20.07.04\"
            \"plus\": \"Y\",
            \"heart\": \"N\"
     }
    ]
     },
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"방 리스트 출력\"
}";
            break;

        case "complexList":
            echo "{
    \"result\": {
             \"complexNum\": \"50\",
             \"complexList\" : 
[
        {
            \"complexIdx\": \"1\",
            \"complexName\": \"씨티라이프61\",
            \"complexAdress\": \"서울특별시 강남구 삼성동\",
            \"complexImg\": \"image경로\",
            \"roomNum\": \"2\",
            \"kindOfBuilding\": \"오피스텔\",
            \"householdNum\": \"63세대\",
            \"completionDate\": \"2014.07\"
        },
        {
            \"complexIdx\": \"2\",
            \"complexName\": \"얼씨구나61\",
            \"complexAdress\": \"서울특별시 강남구 삼성동\",
            \"complexImg\": \"image경로\",
            \"roomNum\": \"2\",
            \"kindOfBuilding\": \"오피스텔\",
            \"householdNum\": \"61세대\",
            \"completionDate\": \"2014.07\"
        }
    ]
    },
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"단지 리스트 출력\"
}";
            break;
        /*
         * API No. 0
         * API Name : 테스트 Body & Insert API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "agencyList":
            echo "{
    \"result\": {
            \"agencyNum\": \"2\",
            \"agencyList\": [
        {
            \"agencyIdx\": \"1\",
            \"agencyName\": \"강남방공인중개사사무소\",
            \"adress\": \"서울특별시 강남구 삼성동\",
            \"agencyComment\": \"터치 한 번으로 편리하게 간편문의\"
            \"agencyBossPicture\": \"image경로\",
            \"roomList\": [
                          {
                           \"roomImg\": \"image경로\",
                           \"price\": \"월세 1000/75\",
                           \"kindOfRoom\": \"원룸\",
                           \"thisFloor\": \"2층\",
                           \"exclusiveArea\": \"26.4㎡\",
                           \"maintenanceCost\": \"관리비 5만\"
                          },
                          {
                           \"roomImg\": \"image경로\",
                           \"price\": \"월세 1000/75\",
                           \"kindOfRoom\": \"원룸\",
                           \"thisFloor\": \"2층\",
                           \"exclusiveArea\": \"26.4㎡\",
                           \"maintenanceCost\": \"관리비 5만\"
                          }
                         ]
        },
        {
            \"agencyIdx\": \"1\",
            \"agencyName\": \"강남방공인중개사사무소\",
            \"adress\": \"서울특별시 강남구 삼성동\",
            \"agencyComment\": \"터치 한 번으로 편리하게 간편문의\"
            \"agencyBossPicture\": \"image경로\",
            \"roomList\": [
                          {
                           \"roomImg\": \"image경로\",
                           \"price\": \"월세 1000/75\",
                           \"kindOfRoom\": \"원룸\",
                           \"thisFloor\": \"2층\",
                           \"exclusiveArea\": \"26.4㎡\",
                           \"maintenanceCost\": \"관리비 5만\"
                          },
                          {
                           \"roomImg\": \"image경로\",
                           \"price\": \"월세 1000/75\",
                           \"kindOfRoom\": \"원룸\",
                           \"thisFloor\": \"2층\",
                           \"exclusiveArea\": \"26.4㎡\",
                           \"maintenanceCost\": \"관리비 5만\"
                          }
                         ]
        }
    ]
    },
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"중개사무소 리스트 출력\"
}";
            break;
        case "homeRoomInterest":
            echo "{
    \"result\": [
    {
            \"regionName\": \"강남역\",
            \"roomType\" : \"원룸,투ㆍ쓰리룸,오피스텔\",
            \"roomNum\" : \"465개의 방\",
            \"regionImg\" : \"image경로\"
     },
    {
            \"regionName\": \"대치동\",
            \"roomType\" : \"원룸,투ㆍ쓰리룸,오피스텔\",
            \"roomNum\" : \"300개의 방\",
            \"regionImg\" : \"image경로\"
     }
     ],
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"관심지역 모든 방 리스트\"
}";
            break;

        case "homeComplexInterest":
            echo "{
    \"result\": [
    {
            \"complexName\": \"루트원레지던스\",
            \"complexImg\" : \"image경로\",
            \"roomNum\" : \"2개의 방\",
            \"kindOfBuilding\" : \"오피스텔\"
            \"householdNum\" : \"63세대\"
            \"completionDate\" : \"2014.07\"
     },
    {
            \"complexName\": \"루트원레지던스\",
            \"complexImg\" : \"image경로\",
            \"roomNum\" : \"2개의 방\",
            \"kindOfBuilding\" : \"오피스텔\"
            \"householdNum\" : \"63세대\"
            \"completionDate\" : \"2014.07\"
     }
     ],
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"관심지역 모든 단지 리스트\"
}";
            break;

        case "homeContent":
            echo "{
    \"result\": [
    {
            \"postImg\": \"image경로\",
            \"postUrl\" : \"http://naver.me/5VHO327i\",
            \"postTitle\" : \"한국주택금융공사 청년을 위한 전세·월세 대출보증\",
            \"postViewCount\" : \"76128\"
     },
    {
            \"postImg\": \"image경로\",
            \"postUrl\" : \"http://naver.me/5VHO327i\",
            \"postTitle\" : \"한국주택금융공사 청년을 위한 전세·월세 대출보증\",
            \"postViewCount\" : \"76128\"
     }
     ],
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"나를 위한 추천 콘텐츠 리스트\"
}";
            break;

        case "homeSubscriptionCenter":
            echo "{
    \"result\": 
    {
            \"subscriptionCenterImg\": \"image경로\",
            \"subscriptionCenterUrl\" : \"http://naver.me/5VHO327i\"
     },
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"lh청약센터 보기\"
}";
            break;

        case "searchList":
            echo "{
    \"result\": [
        {
            \"region\": \"강남대학교\",
            \"icon\": \"image경로\",
            \"hashtag\": \"null\"
        },
        {
            \"region\": \"강남역\",
            \"icon\": \"image경로\",
            \"hashtag\":  [
                \"2호선\",
                \"신분당선\"
            ]
        }
    ],
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"검색어 리스트 출력\"
}";
            break;


        case "searchRecently":
            echo "{
    \"result\": [
        {
            \"region\": \"강남대학교\",
            \"icon\": \"image경로\",
            \"hashtag\": \"null\"
        },
        {
            \"region\": \"강남역\",
            \"icon\": \"image경로\",
            \"hashtag\":  [
                \"2호선\",
                \"신분당선\"
            ]
        }
    ],
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"최근검색 리스트\"
}";
            break;



        case "userRoomView":
            echo "{
    \"result\": [
        {
            \"roomIdx\": \"1\",
            \"complexName\": \"씨티라이프61\",
            \"price\": \"전세 1억9000\",
            \"kindOfRoom\": \"투룸\",
            \"thisFloor\": \"3층\",
            \"exclusiveArea\": \"26.4㎡\",
            \"maintenanceCost\": \"관리비 8만\",
            \"roomSummary\": \"대치동 인테리어 특급 원룸\",
            \"hashTag\": [
                \"전세가능\",
                \"분리형\"
            ],
            \"roomImg\": \"image경로\",
            \"checkedRoom\": \"20.07.04\",
            \"heart\": \"N\"
        },
        {
            \"roomIdx\": \"2\",
            \"complexName\": \"씨티44\",
            \"price\": \"전세 1억9000\",
            \"kindOfRoom\": \"투룸\",
            \"thisFloor\": \"3층\"
            \"exclusiveArea\": \"26.4㎡\",
            \"maintenanceCost\": \"관리비 8만\",
            \"roomSummary\": \"대치동 인테리어 특급 원룸\",
            \"hashTag\": [
                \"전세가능\",
                \"분리형\"
            ],
            \"roomImg\": \"image경로\",
            \"checkedRoom\": \"20.07.04\",
            \"heart\": \"N\"
     }
    ],
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"최근 본 방 리스트 출력\"
}";
            break;



        case "userComplexView":
            echo "{
    \"result\": [
        {
            \"complexIdx\": \"1\",
            \"complexName\": \"씨티라이프61\",
            \"complexAdress\": \"서울특별시 강남구 삼성동\",
            \"complexImg\": \"image경로\",
            \"roomNum\": \"2\",
            \"kindOfBuilding\": \"오피스텔\",
            \"householdNum\": \"63세대\",
            \"completionDate\": \"2014.07\",
        },
        {
            \"complexIdx\": \"2\",
            \"complexName\": \"씨티44\",
            \"complexAdress\": \"서울특별시 강남구 삼성동\",
            \"complexImg\": \"image경로\",
            \"roomNum\": \"4\",
            \"kindOfBuilding\": \"오피스텔\",
            \"householdNum\": \"63세대\",
            \"completionDate\": \"2014.07\",
        }
    ],
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"최근 본 단지 리스트 출력\"
}";
            break;


        case "userRoomLike":
            echo "{
    \"result\": [
        {
            \"roomIdx\": \"1\",
            \"complexName\": \"씨티라이프61\",
            \"price\": \"전세 1억9000\",
            \"kindOfRoom\": \"투룸\",
            \"thisFloor\": \"3층\",
            \"exclusiveArea\": \"26.4㎡\",
            \"maintenanceCost\": \"관리비 8만\",
            \"roomSummary\": \"대치동 인테리어 특급 원룸\",
            \"hashTag\": [
                \"전세가능\",
                \"분리형\"
            ],
            \"roomImg\": \"image경로\",
            \"checkedRoom\": \"20.07.04\",
            \"heart\": \"Y\"
        },
        {
            \"roomIdx\": \"2\",
            \"price\": \"전세 1억9000\",
            \"kindOfRoom\": \"투룸\",
            \"thisFloor\": \"3층\",
            \"exclusiveArea\": \"26.4㎡\",
            \"maintenanceCost\": \"관리비 8만\",
            \"roomSummary\": \"대치동 인테리어 특급 원룸\"
            \"hashTag\": [
                \"전세가능\",
                \"분리형\"
            ],
            \"roomImg\": \"image경로\",
            \"checkedRoom\": \"20.07.04\",
            \"heart\": \"Y\"
     }
    ],
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"찜한 방 리스트 출력\"
}";
            break;


        case "userComplexLike":
            echo "{
    \"result\": [
        {
            \"complexIdx\": \"1\",
            \"complexName\": \"씨티라이프61\",
            \"complexAdress\": \"서울특별시 강남구 삼성동\",
            \"complexImg\": \"image경로\",
            \"roomNum\": \"2\",
            \"kindOfBuilding\": \"오피스텔\",
            \"householdNum\": \"63세대\",
            \"completionDate\": \"2014.07\",
        },
        {
            \"complexIdx\": \"2\",
            \"complexName\": \"씨티44\",
            \"complexAdress\": \"서울특별시 강남구 삼성동\",
            \"complexImg\": \"image경로\",
            \"roomNum\": \"4\",
            \"kindOfBuilding\": \"오피스텔\",
            \"householdNum\": \"66세대\",
            \"completionDate\": \"2014.07\",
        }
    ],
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"찜한 단지 리스트 출력\"
}";
            break;


        case "roomCompare":
            echo "{
    \"result\": [
    {
            \"roomIdx\": \"13747606\",
            \"kindOfRoom\" : \"쓰리룸\",
            \"monthlyRent\" : \"1000/280\",
            \"lease\" : \"5억\",
            \"exclusiveArea\": \"26.4㎡\",
            \"contractArea\" : \"60.81㎡\",
            \"thisFloor\" : \"3층\",
            \"buildingFloor\" : \"5층\",
            \"maintenanceCost\": \"8만 원(기타포함)\",
            \"parking\" : \"2만 원\",
            \"shortTermRental\" : \"가능\",
            \"option\" : [
 {
    \"iconName\": \"에어컨\",
    \"iconImg\": \"image경로\",
 },
{
    \"iconName\": \"세탁기\",
    \"iconImg\": \"image경로\",
 }
],
            \"security\" : [
 {
    \"iconName\": \"CCTV\",
    \"iconImg\": \"image경로\",
 },
{
    \"iconName\": \"인터폰\",
    \"iconImg\": \"image경로\",
 }
],
            \"ect\" : {
    \"kindOfHeating\": \"개별남방\",
    \"builtIn\": \"빌트인 주방\",
    \"elevator\": \"있음\",
    \"elevator\": \"불가능\",
    \"pet\": \"인터폰\",
    \"balcony\": \"있음\",
    \"rentSubsidy\": \"가능\",
    \"moveInDate\": \"날짜 협의\"
     }
     },
     {
            \"roomIdx\": \"120111\",
            \"kindOfRoom\" : \"쓰리룸\",
            \"monthlyRent\" : \"1500/280\",
            \"lease\" : \"5억\",
            \"exclusiveArea\": \"26.4㎡\",
            \"contractArea\" : \"60.81㎡\",
            \"thisFloor\" : \"3층\",
            \"buildingFloor\" : \"5층\",
            \"maintenanceCost\": \"8만 원(기타포함)\",
            \"parking\" : \"2만 원\",
            \"shortTermRental\" : \"가능\",
            \"option\" : [
 {
    \"iconName\": \"에어컨\",
    \"iconImg\": \"image경로\",
 },
{
    \"iconName\": \"세탁기\",
    \"iconImg\": \"image경로\",
 }
],
            \"security\" : [
 {
    \"iconName\": \"CCTV\",
    \"iconImg\": \"image경로\",
 },
{
    \"iconName\": \"인터폰\",
    \"iconImg\": \"image경로\",
 }
],
            \"ect\" : {
    \"kindOfHeating\": \"개별남방\",
    \"builtIn\": \"빌트인 주방\",
    \"elevator\": \"있음\",
    \"elevator\": \"불가능\",
    \"pet\": \"인터폰\",
    \"balcony\": \"있음\",
    \"rentSubsidy\": \"가능\",
    \"moveInDate\": \"날짜 협의\"
     }
     ],
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"비교하기 리스트\"
}";
            break;



        case "userRoomQuestion":
            echo "{
    \"result\": [
        {
            \"roomIdx\": \"1\",
            \"complexName\": \"씨티라이프61\",
            \"price\": \"전세 1억9000\",
            \"kindOfRoom\": \"투룸\",
            \"thisFloor\": \"3층\",
            \"exclusiveArea\": \"26.4㎡\",
            \"maintenanceCost\": \"관리비 8만\",
            \"roomSummary\": \"대치동 인테리어 특급 원룸\",
            \"hashTag\": [
                \"전세가능\",
                \"분리형\"
            ],
            \"roomImg\": \"image경로\",
            \"checkedRoom\": \"20.07.04\",
            \"heart\": \"Y\",
            \"inquiryTime\": \"2002. 7. 19 16:20\"
        },
        {
            \"roomIdx\": \"2\",
            \"price\": \"전세 1억9000\",
            \"kindOfRoom\": \"투룸\",
            \"thisFloor\": \"3층\",
            \"exclusiveArea\": \"26.4㎡\",
            \"maintenanceCost\": \"관리비 8만\",
            \"roomSummary\": \"대치동 인테리어 특급 원룸\",
            \"hashTag\": [
                \"전세가능\",
                \"분리형\"
            ],
            \"roomImg\": \"image경로\",
            \"checkedRoom\": \"20.07.04\",
            \"heart\": \"Y\",
            \"inquiryTime\": \"2002. 7. 19 16:20\"
     }
    ],
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"문의한 방 리스트 출력\"
}";
            break;


        case "userAgencyCall":
            echo "{
    \"result\": [
        {
            \"roomIdx\": \"1\",
            \"complexName\": \"씨티라이프61\",
            \"price\": \"전세 1억9000\",
            \"kindOfRoom\": \"투룸\",
            \"thisFloor\": \"3층\",
            \"exclusiveArea\": \"26.4㎡\",
            \"maintenanceCost\": \"관리비 8만\",
            \"roomSummary\": \"대치동 인테리어 특급 원룸\",
            \"hashTag\": [
                \"전세가능\",
                \"분리형\"
            ],
            \"roomImg\": \"image경로\",
            \"agencyIdx\": \"1\",
            \"agencyName\": \"택스앤리얼티세무사부동산중개\",
            \"agencyBossPicture\": \"image경로\",
            \"checkedRoom\": \"20.07.04\",
            \"heart\": \"N\"
        },
        {
            \"roomIdx\": \"1\",
            \"complexName\": \"씨티44\",
            \"price\": \"전세 1억9000\",
            \"kindOfRoom\": \"투룸\",
            \"thisFloor\": \"3층\",
            \"exclusiveArea\": \"26.4㎡\",
            \"maintenanceCost\": \"관리비 8만\",
            \"roomSummary\": \"대치동 인테리어 특급 원룸\",
            \"hashTag\": [
                \"전세가능\",
                \"분리형\"
            ],
            \"roomImg\": \"image경로\",
            \"agencyIdx\": \"2\",
            \"agencyName\": \"택부동산중개\",
            \"agencyBossPicture\": \"image경로\",
            \"checkedRoom\": \"20.07.04\",
            \"heart\": \"N\"
        }
    ],
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"연락한 부동산 리스트 출력\"
}";
            break;


        case "userInfo":
            echo "{
    \"result\": 
        {
            \"userName\": \"김태민\",
            \"userEmail\": \"jsungmin6@naver.com\",
            \"userProfileImg\": \"image경로\",
            \"userPhone\": \"01051817588\"
        }
    ,
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"내 정보 보기\"
}";
            break;

        case "createUser":
            echo "{
    \"result\": 
        {
            \"userIdx\": \"1\"
        }
    ,
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"회원가입 성공\"
}";
            break;

        case "kakaoLogin":
            echo "{
    \"result\": 
        {
            \"userIdx\": \"1\"
        }
    ,
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"카카오 로그인 성공\"
}";
            break;


        case "facebookLogin":
            echo "{
    \"result\": 
        {
            \"userIdx\": \"1\"
        }
    ,
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"페이스북 로그인 성공\"
}";
            break;


        case "noticeList":
            echo "{
    \"result\": [
        {
            \"noticeTitle\": \"[공지사항]다방 매물확인 메신저 서비스 안내\",
            \"noticeUrl\": \"http://naver.me/5VHO327i\",
            \"createdAt\": \"2020.04.16\"
        },
        {
            \"noticeTitle\": \"[공지사항]다방 매물확인 메신저 서비스 안내\",
            \"noticeUrl\": \"http://naver.me/5VHO327i\",
            \"createdAt\": \"2020.04.16\"
        }
        ]
    ,
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"공지사항 리스트\"
}";
            break;


        case "sideApp":
            echo "{
    \"result\": [
        {
            \"sideType\": \"패밀리 APP\",
            \"sideName\": \"다팡프로\",
            \"sideImg\": \"이미지링크\"
            \"sideUrl\": \"http://naver.me/5VHO327i\"
        },
        {
            \"sideType\": \"다방SNS\",
            \"sideName\": \"페이스북\",
            \"sideImg\": \"이미지링크\"
            \"sideUrl\": \"http://naver.me/5VHO327i\"
        }
        ]
    ,
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"다방 관련 어플, 사이트\"
}";
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
