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
        \"roomNum\": \"2\",
        \"roomList\": [
            {
                \"roomIdx\": \"1\",
                \"monthlyRent\": \"null\",
                \"lease\": \"2억1000\",
                \"kindOfRoom\": \"원룸\",
                \"thisFloor\": \"4층\",
                \"exclusiveArea\": \"33.05㎡\",
                \"maintenanceCost\": \"7만 원(인터넷,유선 TV포함)㎡\",
                \"roomSummary\": \"2호선 도보 7분 아주깔끔하고 오피스텔같은 신축 첫입주원룸\",
                \"latitude\": \"37.5055200000\",
                \"longitude\": \"127.0783540000\",
                \"agencyIdx\": \"1\",
                \"agencyName\": \"대치SK공인중개사사무소\",
                \"agencyComment\": \"간편문의를 사용하는 중개사무소입니다.\",
                \"agencyBossPhone\": \"02-566-1688\",
                \"agencyRoomNum\": \"3\",
                \"quickInquiry\": \"Y\",
                \"checkedRoom\": \"N\",
                \"plus\": \"Y\",
                \"heart\": \"N\",
                \"hashTag\": [
                    \"보증금조절가능\",
                    \"주차가능\",
                    \"빌트인\",
                    \"전세자금대출\"
                ],
                \"roomImg\": [
                    \"gs://allroom.appspot.com/Room/1/r1-1.PNG\",
                    \"gs://allroom.appspot.com/Room/1/r1-2.PNG\",
                    \"gs://allroom.appspot.com/Room/1/r1-3.PNG\"
                ]
            },
            {
                \"roomIdx\": \"2\",
                \"monthlyRent\": \"null\",
                \"lease\": \"1억8000\",
                \"kindOfRoom\": \"오피스텔\",
                \"thisFloor\": \"5층\",
                \"exclusiveArea\": \"21.95㎡\",
                \"maintenanceCost\": \"7만 원(인터넷,유선 TV포함)㎡\",
                \"roomSummary\": \"신축 첫입주 오피스텔 원룸 전세,월세\",
                \"latitude\": \"37.5172030000\",
                \"longitude\": \"127.0405050000\",
                \"agencyIdx\": \"1\",
                \"agencyName\": \"대치SK공인중개사사무소\",
                \"agencyComment\": \"간편문의를 사용하는 중개사무소입니다.\",
                \"agencyBossPhone\": \"02-566-1688\",
                \"agencyRoomNum\": \"3\",
                \"quickInquiry\": \"Y\",
                \"checkedRoom\": \"20.07.04\",
                \"plus\": \"N\",
                \"heart\": \"Y\",
                \"hashTag\": [
                    \"주차가능\",
                    \"역세권\"
                ],
                \"roomImg\": [
                    \"gs://allroom.appspot.com/Room/2/r2-1.png\",
                    \"gs://allroom.appspot.com/Room/2/r2-2.png\",
                    \"gs://allroom.appspot.com/Room/2/r2-3.png\",
                    \"gs://allroom.appspot.com/Room/2/r2-4.png\"
                ]
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

        case "roomDetail":
            echo "{
    \"result\": {
        \"roomInfo\": {
            \"heart\": \"Y\",
            \"roomIdx\": \"2\",
            \"complexIdx\": \"Y\",
            \"sold\": \"N\",
            \"checkedRoom\": \"Y\",
            \"roomSummary\": \"신축 첫입주 오피스텔 원룸 전세,월세\",
            \"securityNum\": \"3\",
            \"monthlyRent\": \"null\",
            \"lease\": \"1억8000\",
            \"maintenanceCost\": \"7만 원(인터넷,유선 TV포함)\",
            \"parking\": \"3만 원\",
            \"shortTermRental\": \"불가능\",
            \"monthlyLivingExpenses\": \"관리비+주차비 10만 원 +a\",
            \"kindOfRoom\": \"오피스텔\",
            \"thisFloor\": \"5층\",
            \"buildingFloor\": \"6층\",
            \"exclusiveArea\": \"21.95㎡\",
            \"contractArea\": \"40.49㎡\",
            \"kindOfHeating\": \"개별난방\",
            \"builtIn\": \"빌트인 주방\",
            \"completionDate\": \"2020.06 준공\",
            \"householdNum\": \"16세대\",
            \"parkingPerHousehold\": \"0.6대\",
            \"elevator\": \"null\",
            \"pet\": \"불가능\",
            \"balcony\": \"없음\",
            \"rentSubsidy\": \"가능\",
            \"moveInDate\": \"즉시 입주\",
            \"latitude\": \"37.5172030000\",
            \"longitude\": \"127.0405050000\",
            \"roomAdress\": \"서울시 송파구 잠실동294-23\",
            \"score\": \"82\",
            \"scoreComment\": \"꼭 한번 봐야 할 방!\",
            \"description\": \"*바로 앞 버스정류장 위치해있음\r\n*서울 어디로든 진입하기 편리한 위치\r\n*신축 첫입주로 깔끔한 매물이며 풀옵션\",
            \"agencyIdx\": \"1\",
            \"agencyBossPhone\": \"02-566-1688\",
            \"agencyBossName\": \"최광민\",
            \"agencyAdress\": \"서울특별시 강남구 대치동 1029 대치 SK뷰아파트 근린생활시설동 102호 대치SK공인중개사사무소\",
            \"agencyName\": \"대치SK공인중개사사무소\",
            \"agencyMemberName\": \"최요한\",
            \"agencyMemberPosition\": \"소속공인중개사\",
            \"agencyMemberProfileImg\": \"gs://allroom.appspot.com/default/프로필 기본사진.PNG\",
            \"roomImg\": [
                \"gs://allroom.appspot.com/Room/2/r2-1.png\",
                \"gs://allroom.appspot.com/Room/2/r2-2.png\",
                \"gs://allroom.appspot.com/Room/2/r2-3.png\",
                \"gs://allroom.appspot.com/Room/2/r2-4.png\"
            ],
            \"hashTag\": [
                \"보증금조절가능\",
                \"주차가능\",
                \"빌트인\",
                \"전세자금대출\"
            ]
        },
        \"option\": [
            {
                \"iconName\": \"에어컨\",
                \"iconImg\": \"gs://allroom.appspot.com/icon/에어컨.PNG\"
            },
            {
                \"iconName\": \"세탁기\",
                \"iconImg\": \"gs://allroom.appspot.com/icon/세탁기.PNG\"
            },
            {
                \"iconName\": \"책상\",
                \"iconImg\": \"gs://allroom.appspot.com/icon/책상.PNG\"
            },
            {
                \"iconName\": \"옷장\",
                \"iconImg\": \"gs://allroom.appspot.com/icon/옷장.PNG\"
            },
            {
                \"iconName\": \"인덕션\",
                \"iconImg\": \"gs://allroom.appspot.com/icon/인덕션.PNG\"
            },
            {
                \"iconName\": \"전자레인지\",
                \"iconImg\": \"gs://allroom.appspot.com/icon/전자레인지.PNG\"
            },
            {
                \"iconName\": \"신발장\",
                \"iconImg\": \"gs://allroom.appspot.com/icon/신발장.PNG\"
            }
        ],
        \"security\": [
            {
                \"iconName\": \"비디오폰\",
                \"iconImg\": \"gs://allroom.appspot.com/icon/비디오폰.PNG\"
            },
            {
                \"iconName\": \"공동현관\",
                \"iconImg\": \"gs://allroom.appspot.com/icon/공동현관.PNG\"
            },
            {
                \"iconName\": \"CCTV\",
                \"iconImg\": \"gs://allroom.appspot.com/icon/cctv.PNG\"
            }
        ],
        \"complexInfo\": {
            \"complexIdx\": \"1\",
            \"roomDesignImg\": \"gs://allroom.appspot.com/complex/1/roomDesignImg/cb1-1.PNG\",
            \"exclusiveArea\": \"18.19㎡\",
            \"contractArea\": \"34.02㎡\",
            \"roomNum\": \"1개\",
            \"bathroomNum\": \"1개\",
            \"complexType\": \"계단식\",
            \"householdNum\": \"4세대\"
        }
    },
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"방 상세정보\"";
            break;

        case "complexDetail":
            echo "{
    \"result\": {
        \"complexInfo\": {
            \"heart\": \"N\",
            \"complexName\": \"파로스타워\",
            \"complexAdress\": \"서울특별시 송파구 잠실동\",
            \"completionDate\": 2020.06,
            \"complexNum\": 1,
            \"householdNum\": \"16세대\",
            \"parkingPerHousehold\": \"가구당 0.6대\",
            \"kindOfHeating\": \"개별난방\",
            \"madebyBuilding\": \"비오케이건설주식회사\",
            \"complexSize\": \"총 1개동/6층~6층\",
            \"fuel\": \"도시가스\",
            \"floorAreaRatio\": \"246%\",
            \"buildingCoverageRatio\": \"49%\",
            \"complexDealing\": \"-\",
            \"complexLease\": \"-\",
            \"regionDealing\": 1311,
            \"regionLease\": 1100,
            \"latitude\": 37.517203,
            \"longitude\": 127.040505,
            \"complexImg\": [
                \"gs://allroom.appspot.com/complex/1/complexImg/c1-1.PNG\",
                \"gs://allroom.appspot.com/complex/1/complexImg/c1-2.PNG\"
            ]
        },
        \"sizeInfo\": [
            {
                \"kindOfArea\": \"34(18.19㎡)\",
                \"roomDesignImg\": \"gs://allroom.appspot.com/complex/1/roomDesignImg/cb1-1.PNG\",
                \"exclusiveArea\": \"18.19㎡\",
                \"contractArea\": \"34.02㎡\",
                \"roomNum\": \"1개\",
                \"bathroomNum\": \"1개\",
                \"householdNum\": \"4세대\",
                \"maintenanceCost\": \"-\"
            },
            {
                \"kindOfArea\": \"40(21.95㎡)\",
                \"roomDesignImg\": \"gs://allroom.appspot.com/complex/1/roomDesignImg/cb1-2.PNG\",
                \"exclusiveArea\": \"21.95㎡\",
                \"contractArea\": \"40.76㎡\",
                \"roomNum\": \"1개\",
                \"bathroomNum\": \"1개\",
                \"householdNum\": \"2세대\",
                \"maintenanceCost\": \"-\"
            },
            {
                \"kindOfArea\": \"124(68.34㎡)\",
                \"roomDesignImg\": \"gs://allroom.appspot.com/complex/1/roomDesignImg/cb1-3.PNG\",
                \"exclusiveArea\": \"68.34㎡\",
                \"contractArea\": \"124.86㎡\",
                \"roomNum\": \"1개\",
                \"bathroomNum\": \"1개\",
                \"householdNum\": \"1세대\",
                \"maintenanceCost\": \"-\"
            }
        ],
        \"surroundingRecommendationComplex\": [
            {
                \"complexName\": \"위너스\",
                \"complexImg\": \"gs://allroom.appspot.com/complex/2/complexImg/c2-1.PNG\"
            }
        ]
    },
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"단지 상세정보\"
}";
            break;

        case "agencyDetail":
            echo "{
    \"result\": {
        \"agencyInfo\": {
            \"agencyComment\": \"간편문의를 사용하는 중개사무소입니다.\",
            \"quickInquiry\": \"Y\",
            \"agencyName\": \"대치SK공인중개사사무소\",
            \"agencyBossName\": \"최광민\",
            \"mediationNumber\": \"11680-2019-00257\",
            \"companyRegistrationNumber\": \"465-41-00592\",
            \"agencyBossPhone\": \"02-566-1688\",
            \"agencyAdress\": \"서울특별시 강남구 대치동 1029 대치 SK뷰아파트 근린생활시설동 102호 대치SK공인중개사사무소\",
            \"joinDate\": \"2020년 03월 21일\",
            \"completedRoom\": \"16개의 방\"
        },
        \"agencyMember\": [
            {
                \"agencyMemberName\": \"최광민\",
                \"agencyMemberPosition\": \"대표공인중개사\",
                \"agencyMemberProfileImg\": \"gs://allroom.appspot.com/default/프로필 기본사진.PNG\"
            },
            {
                \"agencyMemberName\": \"최요한\",
                \"agencyMemberPosition\": \"소속공인중개사\",
                \"agencyMemberProfileImg\": \"gs://allroom.appspot.com/default/프로필 기본사진.PNG\"
            },
            {
                \"agencyMemberName\": \"최백현\",
                \"agencyMemberPosition\": \"소속공인중개사\",
                \"agencyMemberProfileImg\": \"gs://allroom.appspot.com/default/프로필 기본사진.PNG\"
            }
        ]
    },
    \"isSuccess\": true,
    \"code\": 100,
    \"message\": \"중개사무소 상세정보보기\"
}";
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
