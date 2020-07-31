<?php
require './pdos/DatabasePdo.php';
require './pdos/IndexPdo.php';
require './vendor/autoload.php';

use \Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;

date_default_timezone_set('Asia/Seoul');
ini_set('default_charset', 'utf8mb4');

//에러출력하게 하는 코드
//error_reporting(E_ALL); ini_set("display_errors", 1);

//Main Server API
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    // *************완성된 API, 더미데이터 넣어 놓음**************
    // 지도 탭 정보 관련
    $r->addRoute('GET', '/rooms', ['MapController', 'roomList']);
    $r->addRoute('GET', '/complexes', ['MapController', 'complexList']);
    $r->addRoute('GET', '/agencies', ['MapController', 'agencyList']);
    $r->addRoute('GET', '/rooms/{roomIdx}', ['MapController', 'roomDetail']);
    $r->addRoute('GET', '/complexes/{complexIdx}', ['MapController', 'complexDetail']);
    $r->addRoute('GET', '/agencies/{agencyIdx}', ['MapController', 'agencyDetail']);
    $r->addRoute('GET', '/complexes/{complexIdx}/rooms', ['MapController', 'complexRoomList']);
    $r->addRoute('GET', '/agencies/{agencyIdx}/rooms', ['MapController', 'agencyRoomList']);
    $r->addRoute('GET', '/geoFence', ['DummyController', 'geoFence']);
    // 홈 탭 리스트 정보 관련
    $r->addRoute('GET', '/users/{userIdx}/interest-regions', ['HomeController', 'homeRoomInterest']);
    $r->addRoute('GET', '/users/{userIdx}/interest-complexes', ['HomeController', 'homeComplexInterest']);
    $r->addRoute('GET', '/contents', ['HomeController', 'homeContent']);
    $r->addRoute('GET', '/events', ['HomeController', 'homeEvent']);
    // 검색 탭 관련
    $r->addRoute('GET', '/searches', ['SearchController', 'searchList']);
    $r->addRoute('GET', '/users/{userIdx}/recently-searches', ['SearchController', 'searchRecently']);
    $r->addRoute('DELETE', '/searches/{userIdx}', ['SearchController', 'deleteSearchRecord']);
    // 관심목록 탭 리스트 관련
    $r->addRoute('GET', '/users/{userIdx}/looks/rooms', ['InterestController', 'userRoomView']);
    $r->addRoute('GET', '/users/{userIdx}/looks/complexes', ['InterestController', 'userComplexView']);
    $r->addRoute('GET', '/users/{userIdx}/likes/rooms', ['InterestController', 'userRoomLike']);
    $r->addRoute('GET', '/comparison/rooms', ['InterestController', 'roomCompare']);
    $r->addRoute('GET', '/users/{userIdx}/likes/complexes', ['InterestController', 'userComplexLike']);
    $r->addRoute('GET', '/users/{userIdx}/inquiries/complexes', ['InterestController', 'userRoomInquiry']);
    $r->addRoute('GET', '/users/{userIdx}/calls/agencies', ['InterestController', 'userAgencyCall']);
    // 유저 관련
    $r->addRoute('GET', '/users/{userIdx}', ['UserController', 'userInfo']);
    $r->addRoute('POST', '/users/login', ['UserController', 'userBasicLogin']);
    $r->addRoute('POST', '/users', ['UserController', 'createUser']);
    $r->addRoute('GET', '/kakaoCallback', ['UserController', 'kakaoCallback']);
    $r->addRoute('GET', '/users/oauth/login', ['UserController', 'oauthLogin']);
    $r->addRoute('PATCH', '/likes', ['UserController', 'changeLikes']);
    // 더보기 기타 관련
    $r->addRoute('GET', '/notices', ['DummyController', 'noticeList']);
    $r->addRoute('GET', '/familyApps', ['DummyController', 'familyApps']);
    //추가
    $r->addRoute('POST', '/calls', ['EtcController', 'createCallLog']);
    $r->addRoute('POST', '/inquiries', ['EtcController', 'createInquireLog']);
    //실시간 검색어
    $r->addRoute('GET', '/realTimeSearches', ['EtcController', 'searchWord']);
    //추천 알고리즘
    $r->addRoute('GET', '/users/{userIdx}/recommend-rooms', ['EtcController', 'recommendRooms']);






//    $r->addRoute('GET', '/users', 'get_all_users_handler');
//    // {id} must be a number (\d+)
//    $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
//    // The /{title} suffix is optional
//    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// 로거 채널 생성
$accessLogs = new Logger('ACCESS_LOGS');
$errorLogs = new Logger('ERROR_LOGS');
// log/your.log 파일에 로그 생성. 로그 레벨은 Info
$accessLogs->pushHandler(new StreamHandler('logs/access.log', Logger::INFO));
$errorLogs->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));
// add records to the log
//$log->addInfo('Info log');
// Debug 는 Info 레벨보다 낮으므로 아래 로그는 출력되지 않음
//$log->addDebug('Debug log');
//$log->addError('Error log');

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo "404 Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        echo "405 Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        switch ($routeInfo[1][0]) {
            case 'IndexController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/IndexController.php';
                break;
            case 'MainController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/MainController.php';
                break;
            case 'MapController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/MapController.php';
                break;
            case 'HomeController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/HomeController.php';
                break;
            case 'SearchController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/SearchController.php';
                break;
            case 'InterestController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/InterestController.php';
                break;
            case 'UserController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/UserController.php';
                break;
            case 'EtcController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/EtcController.php';
                break;
            case 'DummyController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/DummyController.php';
                break;
        }

        break;
}
