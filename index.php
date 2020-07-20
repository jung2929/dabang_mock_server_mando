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
    /* ******************   Test   ****************** */
    // 지도 탭 관련
    $r->addRoute('GET', '/test/room/list', ['MapController', 'roomList']);
    $r->addRoute('GET', '/test/complex/list', ['MapController', 'complexList']);
    $r->addRoute('GET', '/test/agency/list', ['MapController', 'agencyList']);
    $r->addRoute('GET', '/test/room/detail/{roomIdx}', ['MapController', 'roomDetail']);
    $r->addRoute('GET', '/test/complex/detail/{complexIdx}', ['MapController', 'complexDetail']);
    $r->addRoute('GET', '/test/agency/detail/{agencyIdx}', ['MapController', 'agencyDetail']);
    $r->addRoute('GET', '/test/geoFence', ['MapController', 'geoFence']);
    // 홈 탭 관련
    $r->addRoute('GET', '/test/room/interest/{userIdx}', ['HomeController', 'homeRoomInterest']);
    $r->addRoute('GET', '/test/complex/interest/{userIdx}', ['HomeController', 'homeComplexInterest']);
    $r->addRoute('GET', '/test/content', ['HomeController', 'homeContent']);
    $r->addRoute('GET', '/test/subscription-center', ['HomeController', 'homeSubscriptionCenter']);
    // 홈-검색 탭 관련
    $r->addRoute('GET', '/test/search/list', ['SearchController', 'searchList']);
    $r->addRoute('GET', '/test/search/recently/{userIdx}', ['SearchController', 'searchRecently']);
    $r->addRoute('DELETE', '/test/search/record/{userIdx}', ['SearchController', 'deleteSearchRecord']);
    // 관심목록 탭 관련
    $r->addRoute('GET', '/test/room/view/{userIdx}', ['InterestController', 'userRoomView']);
    $r->addRoute('GET', '/test/complex/view/{userIdx}', ['InterestController', 'userComplexView']);
    $r->addRoute('GET', '/test/room/like/{userIdx}', ['InterestController', 'userRoomLike']);
    $r->addRoute('GET', '/test/complex/like/{userIdx}', ['InterestController', 'userComplexLike']);
    $r->addRoute('GET', '/test/room/compare', ['InterestController', 'roomCompare']);
    $r->addRoute('GET', '/test/room/question/{userIdx}', ['InterestController', 'userRoomQuestion']);
    $r->addRoute('GET', '/test/agency/call/{userIdx}', ['InterestController', 'userAgencyCall']);
    // 유저 관련
    $r->addRoute('GET', '/test/user', ['UserController', 'userInfo']);
    $r->addRoute('POST', '/test/user', ['UserController', 'createUser']);
    $r->addRoute('GET', '/test/kakao-login', ['UserController', 'kakaoLogin']);
    $r->addRoute('GET', '/test/facebook-login', ['UserController', 'facebookLogin']);
    // 더보기 기타 관련
    $r->addRoute('GET', '/test/notice', ['EtcController', 'noticeList']);
    $r->addRoute('GET', '/test/side-app', ['EtcController', 'sideApp']);


    // *************완성된 API, 더미데이터 넣어 놓음**************
    $r->addRoute('GET', '/room/list', ['DummyController', 'roomList']);
    $r->addRoute('GET', '/complex/list', ['DummyController', 'complexList']);
    $r->addRoute('GET', '/agency/list', ['DummyController', 'agencyList']);
    $r->addRoute('GET', '/room/detail/{roomIdx}', ['DummyController', 'roomDetail']);
    $r->addRoute('GET', '/complex/detail/{complexIdx}', ['DummyController', 'complexDetail']);
    $r->addRoute('GET', '/agency/detail/{agencyIdx}', ['DummyController', 'agencyDetail']);
    $r->addRoute('GET', '/geoFence', ['DummyController', 'geoFence']);
    // 홈 탭 관련
    $r->addRoute('GET', '/room/interest/{userIdx}', ['DummyController', 'homeRoomInterest']);
    $r->addRoute('GET', '/complex/interest/{userIdx}', ['DummyController', 'homeComplexInterest']);
    $r->addRoute('GET', '/content', ['DummyController', 'homeContent']);
    $r->addRoute('GET', '/subscription-center', ['DummyController', 'homeSubscriptionCenter']);
    // 홈-검색 탭 관련
    $r->addRoute('GET', '/search/list', ['DummyController', 'searchList']);
    $r->addRoute('GET', '/search/recently/{userIdx}', ['DummyController', 'searchRecently']);
    $r->addRoute('DELETE', '/search/record/{userIdx}', ['DummyController', 'deleteSearchRecord']);
    // 관심목록 탭 관련
    $r->addRoute('GET', '/room/view/{userIdx}', ['DummyController', 'userRoomView']);
    $r->addRoute('GET', '/complex/view/{userIdx}', ['DummyController', 'userComplexView']);
    $r->addRoute('GET', '/room/like/{userIdx}', ['DummyController', 'userRoomLike']);
    $r->addRoute('GET', '/complex/like/{userIdx}', ['DummyController', 'userComplexLike']);
    $r->addRoute('GET', '/room/compare', ['DummyController', 'roomCompare']);
    $r->addRoute('GET', '/room/question/{userIdx}', ['DummyController', 'userRoomQuestion']);
    $r->addRoute('GET', '/agency/call/{userIdx}', ['DummyController', 'userAgencyCall']);
    // 유저 관련
    $r->addRoute('GET', '/user', ['DummyController', 'userInfo']);
    $r->addRoute('POST', '/user', ['DummyController', 'createUser']);
    $r->addRoute('GET', '/kakao-login', ['DummyController', 'kakaoLogin']);
    $r->addRoute('GET', '/facebook-login', ['DummyController', 'facebookLogin']);
    // 더보기 기타 관련
    $r->addRoute('GET', '/notice', ['DummyController', 'noticeList']);
    $r->addRoute('GET', '/side-app', ['DummyController', 'sideApp']);


    $r->addRoute('GET', '/jwt', ['DummyController', 'validateJwt']);
    $r->addRoute('POST', '/jwt', ['DummyController', 'createJwt']);
    $r->addRoute('GET', '/', ['DummyController', 'index']);



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
