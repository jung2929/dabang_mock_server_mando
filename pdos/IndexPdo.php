<?php

//READ
function test()
{
    $pdo = pdoSqlConnect();
    $query = "SELECT * FROM Test;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function userInfoCreate($userEmail)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT userIdx FROM User WHERE userEmail = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userEmail]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

function createUser($userName, $userEmail, $userPwd, $userPhone)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO User (userName, userEmail, userPwd, userPhone) VALUES (?, ? ,?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$userName, $userEmail, $userPwd, $userPhone]);

    $st = null;
    $pdo = null;

}


function userInfo($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select COALESCE(userName,'null') as userName,
       COALESCE(userEmail,'null') as userEmail,
       COALESCE(userProfileImg,'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%ED%94%84%EB%A1%9C%ED%95%84%20%EA%B8%B0%EB%B3%B8%EC%82%AC%EC%A7%84.PNG?alt=media&token=7e94ef45-54cc-4cfa-9b2d-8c091d953914') as userProfileImg,
       COALESCE(userPhone,'null') as userPhone
from User
where userIdx = :userIdx
  and isDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}



function userAgencyCall($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select URL.roomIdx,
       COALESCE(C.complexName,'null') as complexName,
       COALESCE(concat('월세 ',R.monthlyRent),'null') as monthlyRent,
       COALESCE(R.lease,'null') as lease,
       COALESCE(R.kindOfRoom,'null') as roomType,
       COALESCE(R.thisFloor,'null') as thisFloor,
       COALESCE(concat(R.exclusiveArea,\"㎡\"), 'null') as exclusiveArea,
       case when R.maintenanceCost=0 then 'null' else concat('관리비 ',R.maintenanceCost,'만원') end as maintenanceCost,
       COALESCE(R.roomSummary,'null') as roomSummary,
       COALESCE(RI.roomImg,'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%EB%B0%A9%20%EA%B8%B0%EB%B3%B8%EC%9D%B4%EB%AF%B8%EC%A7%80.PNG?alt=media&token=ac7a7438-5dde-4666-bccd-6ab0c07d0f36') as roomImg,
       COALESCE(A.agencyIdx,'null') as agencyIdx,
       COALESCE(A.agencyName,'null') as agencyName,
       COALESCE(AM.agencyMemberProfileImg,'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%ED%94%84%EB%A1%9C%ED%95%84%20%EA%B8%B0%EB%B3%B8%EC%82%AC%EC%A7%84.PNG?alt=media&token=7e94ef45-54cc-4cfa-9b2d-8c091d953914') as agencyBossImg,
       COALESCE(concat(ARN.agencyRoomNum,'개의 방'),'0개의 방') as agencyRoomNum,
       COALESCE(R.checkedRoom,'N') as checkedRoom,
       COALESCE(UH.heart,'N') as heart
from (select Max(userCallIdx) as userCallIdx, roomIdx from UserCallLog
where userIdx=:userIdx and isDeleted=\"N\"
group by roomIdx) as URL
left join UserCallLog as URL2
on URL2.userCallIdx = URL.userCallIdx and URL2.roomIdx = URL.roomIdx
left join RoomInComplex as RIC
on URL.roomIdx = RIC.roomIdx
left join Complex as C
on RIC.complexIdx = C.complexIdx
left join Room as R
on URL.roomIdx = R.roomIdx
left join (select RI.roomIdx, R.roomImg
from (select min(roomImgIdx) as roomImgIdx, roomIdx
from RoomImg
group by roomIdx) as RI
left join RoomImg as R
on R.roomImgIdx = RI.roomImgIdx and R.roomIdx = RI.roomIdx) as RI
on RI.roomIdx = URL.roomIdx
left join AgencyRoom as AR
on AR.roomIdx = URL.roomIdx
left join Agency as A
on AR.agencyIdx = A.agencyIdx
left join AgencyMember as AM
on A.agencyBossName = AM.agencyMemberName and AM.agencyMemberPosition=\"대표공인중개사\"
left join (select agencyIdx, count(agencyIdx) as agencyRoomNum from AgencyRoom
group by agencyIdx) as ARN
on ARN.agencyIdx = AR.agencyIdx
left join (select roomIdx, heart from UserHeart where isDeleted =\"N\" and roomIdx is not null and userIdx = :userIdx) as UH
on UH.roomIdx = URL.roomIdx
order by URL2.createdAt desc
";

    $st = $pdo->prepare($query);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $result = array();
    //행을 한줄씩 읽음
    while($row = $st -> fetch()) {
        //한줄 읽은 행에 거기에 맞는 해시태그 추가
        $pdo2 = pdoSqlConnect();
        $query2="select hashtag from RoomHashTag
        where roomIdx=:roomIdx";
        $st2 = $pdo2->prepare($query2);
        $st2->bindParam(':roomIdx',$row['roomIdx'],PDO::PARAM_STR);
        $st2->execute();
        $st2->setFetchMode(PDO::FETCH_NUM);
        $hash = $st2->fetchAll();

        //배열형식으로 되어있어 배열을 품
        $hashlist=array();
        for($i=0;$i<count($hash);$i++){
            array_push($hashlist,$hash[$i][0]);
        }

        if($hashlist){
            $row["hashTag"] = $hashlist;
        }else{
            $row["hashTag"] = 'null';
        }

        $result[] = $row;
    }

    $st = null;
    $pdo = null;

    return $result;

}


function userRoomInquiry($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select URL.roomIdx,
       COALESCE(C.complexName,'null') as complexName,
       COALESCE(concat('월세 ',R.monthlyRent),'null') as monthlyRent,
       COALESCE(R.lease,'null') as lease,
       COALESCE(R.kindOfRoom,'null') as roomType,
       COALESCE(R.thisFloor,'null') as thisFloor,
       COALESCE(concat(R.exclusiveArea,\"㎡\"), 'null') as exclusiveArea,
       case when R.maintenanceCost=0 then 'null' else concat('관리비 ',R.maintenanceCost,'만원') end as maintenanceCost,
       COALESCE(R.roomSummary,'null') as roomSummary,
       COALESCE(RI.roomImg,'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%EB%B0%A9%20%EA%B8%B0%EB%B3%B8%EC%9D%B4%EB%AF%B8%EC%A7%80.PNG?alt=media&token=ac7a7438-5dde-4666-bccd-6ab0c07d0f36') as roomImg,
       COALESCE(R.checkedRoom,'N') as checkedRoom,
       COALESCE(UH.heart, 'N') as heart,
       URL2.createdAt as inquiryTime
from (select Max(userInquiryLogIdx) as userInquiryLogIdx, roomIdx from UserInquiryLog
where userIdx=:userIdx and isDeleted=\"N\"
group by roomIdx) as URL
left join UserInquiryLog as URL2
on URL2.userInquiryLogIdx = URL.userInquiryLogIdx and URL2.roomIdx = URL.roomIdx
left join RoomInComplex as RIC
on URL.roomIdx = RIC.roomIdx
left join Complex as C
on RIC.complexIdx = C.complexIdx
left join Room as R
on URL.roomIdx = R.roomIdx
left join (SELECT userIdx, roomIdx, heart
                    FROM UserHeart
                    where userIdx = :userIdx) as UH
on UH.roomIdx = URL.roomIdx
left join (select RI.roomIdx, R.roomImg
from (select min(roomImgIdx) as roomImgIdx, roomIdx
from RoomImg
group by roomIdx) as RI
left join RoomImg as R
on R.roomImgIdx = RI.roomImgIdx and R.roomIdx = RI.roomIdx) as RI
on RI.roomIdx = URL.roomIdx
order by URL2.createdAt desc
";

    $st = $pdo->prepare($query);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $result = array();
    //행을 한줄씩 읽음
    while($row = $st -> fetch()) {
        //한줄 읽은 행에 거기에 맞는 해시태그 추가
        $pdo2 = pdoSqlConnect();
        $query2="select hashtag from RoomHashTag
        where roomIdx=:roomIdx";
        $st2 = $pdo2->prepare($query2);
        $st2->bindParam(':roomIdx',$row['roomIdx'],PDO::PARAM_STR);
        $st2->execute();
        $st2->setFetchMode(PDO::FETCH_NUM);
        $hash = $st2->fetchAll();

        //배열형식으로 되어있어 배열을 품
        $hashlist=array();
        for($i=0;$i<count($hash);$i++){
            array_push($hashlist,$hash[$i][0]);
        }

        if($hashlist){
            $row["hashTag"] = $hashlist;
        }else{
            $row["hashTag"] = 'null';
        }

        $result[] = $row;
    }

    $st = null;
    $pdo = null;

    return $result;

}



function userComplexLike($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select COALESCE(UC.complexIdx,\"null\") as complexIdx,
       COALESCE(C.complexName,\"null\") as complexName,
       COALESCE(C.complexAddress,\"null\") as complexAddress,
       COALESCE(CI.complexImg,\"https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%EB%B0%A9%20%EA%B8%B0%EB%B3%B8%EC%9D%B4%EB%AF%B8%EC%A7%80.PNG?alt=media&token=ac7a7438-5dde-4666-bccd-6ab0c07d0f36\") as complexImg,
       COALESCE(RN.roomNum,\"0\") as roomNum,
       COALESCE(C.kindOfBuilding,\"null\") as roomType,
       COALESCE(concat(C.householdNum,'세대'),\"null\") as householdNum,
       COALESCE(C.completionDate,\"null\") as completionDate
from (select complexIdx, updatedAt
      from UserHeart
      where userIdx = :userIdx
        and complexIdx is not null
        and heart = \"Y\"
        and isDeleted = \"N\") as UC
         left join Complex as C
                   on C.complexIdx = UC.complexIdx
         left join (select C.complexIdx, C.complexImg
                    from (select min(complexImgIdx) as complexImgIdx, complexIdx
                          from ComplexImg
                          group by complexIdx) as CI
                             left join ComplexImg as C
                                       on C.complexImgIdx = CI.complexImgIdx and C.complexIdx = CI.complexIdx) as CI
                   on CI.complexIdx = UC.complexIdx
         left join (select complexIdx, count(complexIdx) as roomNum
                    from RoomInComplex
                    group by complexIdx) as RN
                   on RN.complexIdx = UC.complexIdx
order by UC.updatedAt desc
";

    $st = $pdo->prepare($query);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;

}



function roomCompare($roomIdx1,$roomIdx2,$roomIdx3)
{
    $pdo = pdoSqlConnect();
    $query = "select URL.roomIdx,
       COALESCE(RI.roomImg,'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%EB%B0%A9%20%EA%B8%B0%EB%B3%B8%EC%9D%B4%EB%AF%B8%EC%A7%80.PNG?alt=media&token=ac7a7438-5dde-4666-bccd-6ab0c07d0f36') as roomImg,
       COALESCE(R.kindOfRoom,'null') as roomType,
       COALESCE(concat('월세 ',R.monthlyRent),'null') as monthlyRent,
       COALESCE(R.lease,'null') as lease,
       COALESCE(concat(R.exclusiveArea,\"㎡\"), 'null') as exclusiveArea,
       COALESCE(concat(R.contractArea,\"㎡\"), 'null') as contractArea,
       COALESCE(R.thisFloor,'null') as thisFloor,
       COALESCE(R.buildingFloor,'null') as buildingFloor,
       case when R.maintenanceCost=0 then 'null' else concat('관리비 ',R.maintenanceCost,'만원') end as maintenanceCost,
       COALESCE(R.parking,'null') as parking,
       COALESCE(R.shortTermRental,'null') as shortTermRental,
       COALESCE(O.options,'null') as options,
       COALESCE(S.security,'null') as security,
       COALESCE(R.kindOfHeating,'null') as kindOfHeating,
       COALESCE(R.builtIn,'null') as builtIn,
       COALESCE(R.elevator,'null') as elevator,
       COALESCE(R.pet,'null') as pet,
       COALESCE(R.balcony,'null') as balcony,
       COALESCE(R.rentSubsidy,'null') as rentSubsidy,
       COALESCE(R.moveInDate,'null') as moveInDate,
       COALESCE(A.agencyIdx,'null') as agencyIdx,
       COALESCE(A.agencyName,'null') as agencyName,
       COALESCE(A.agencyBossName,'null') as agencyBossName,
       COALESCE(AM.agencyMemberProfileImg,'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%ED%94%84%EB%A1%9C%ED%95%84%20%EA%B8%B0%EB%B3%B8%EC%82%AC%EC%A7%84.PNG?alt=media&token=7e94ef45-54cc-4cfa-9b2d-8c091d953914') as agencyBossImg,
       COALESCE(A.agencyBossPhone,'null') as agencyBossPhone
from (select roomIdx from Room where roomIdx = :roomIdx1 or roomIdx = :roomIdx2 or roomIdx = :roomIdx3) as URL
left join RoomInComplex as RIC
on URL.roomIdx = RIC.roomIdx
left join Complex as C
on RIC.complexIdx = C.complexIdx
left join Room as R
on URL.roomIdx = R.roomIdx
left join (select RI.roomIdx, R.roomImg
from (select min(roomImgIdx) as roomImgIdx, roomIdx
from RoomImg
group by roomIdx) as RI
left join RoomImg as R
on R.roomImgIdx = RI.roomImgIdx and R.roomIdx = RI.roomIdx) as RI
on RI.roomIdx = URL.roomIdx
left join AgencyRoom as AR
on URL.roomIdx = AR.roomIdx
left join Agency as A
on AR.agencyIdx = A.agencyIdx
left join AgencyMember as AM
on A.agencyBossName = AM.agencyMemberName and AM.agencyMemberPosition=\"대표공인중개사\"
left join (select RI.roomIdx, GROUP_CONCAT(I.iconName) as options from RoomIcon as RI
left join Icon as I
on I.iconIdx = RI.iconIdx
where I.iconType = \"옵션\"
group by RI.roomIdx) as O
on O.roomIdx = URL.roomIdx
left join (select RI.roomIdx, GROUP_CONCAT(I.iconName) as security from RoomIcon as RI
left join Icon as I
on I.iconIdx = RI.iconIdx
where I.iconType = \"보안/안전시설\"
group by RI.roomIdx) as S
on S.roomIdx = URL.roomIdx
";

    $st = $pdo->prepare($query);
    $st->bindParam(':roomIdx1',$roomIdx1,PDO::PARAM_STR);
    $st->bindParam(':roomIdx2',$roomIdx2,PDO::PARAM_STR);
    $st->bindParam(':roomIdx3',$roomIdx3,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


function userRoomLike($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select URL.roomIdx,
       COALESCE(C.complexName,'null') as complexName,
       COALESCE(concat('월세 ',R.monthlyRent),'null') as monthlyRent,
       COALESCE(R.lease,'null') as lease,
       COALESCE(R.kindOfRoom,'null') as roomType,
       COALESCE(R.thisFloor,'null') as thisFloor,
       COALESCE(concat(R.exclusiveArea,\"㎡\"), 'null') as exclusiveArea,
       case when R.maintenanceCost=0 then 'null' else concat('관리비 ',R.maintenanceCost,'만원') end as maintenanceCost,
       COALESCE(R.roomSummary,'null') as roomSummary,
       COALESCE(RI.roomImg,'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%EB%B0%A9%20%EA%B8%B0%EB%B3%B8%EC%9D%B4%EB%AF%B8%EC%A7%80.PNG?alt=media&token=ac7a7438-5dde-4666-bccd-6ab0c07d0f36') as roomImg,
       COALESCE(R.checkedRoom,'N') as checkedRoom,
       COALESCE(UH.heart, 'N') as heart,
       COALESCE(R.sold, 'N') as sold,
       COALESCE(R.isDeleted, 'N') as isDeleted,
       COALESCE(R.open, 'Y') as open
from (select updatedAt, roomIdx from UserHeart
where userIdx=:userIdx and isDeleted=\"N\" and roomIdx is not null and heart =\"Y\") as URL
left join RoomInComplex as RIC
on URL.roomIdx = RIC.roomIdx
left join Complex as C
on RIC.complexIdx = C.complexIdx
left join Room as R
on URL.roomIdx = R.roomIdx
left join (SELECT userIdx, roomIdx, heart
                    FROM UserHeart
                    where userIdx = :userIdx) as UH
on UH.roomIdx = URL.roomIdx
left join (select RI.roomIdx, R.roomImg
from (select min(roomImgIdx) as roomImgIdx, roomIdx
from RoomImg
group by roomIdx) as RI
left join RoomImg as R
on R.roomImgIdx = RI.roomImgIdx and R.roomIdx = RI.roomIdx) as RI
on RI.roomIdx = URL.roomIdx
order by URL.updatedAt desc
";

    $st = $pdo->prepare($query);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $result = array();
    //행을 한줄씩 읽음
    while($row = $st -> fetch()) {
        //한줄 읽은 행에 거기에 맞는 해시태그 추가
        $pdo2 = pdoSqlConnect();
        $query2="select hashtag from RoomHashTag
        where roomIdx=:roomIdx";
        $st2 = $pdo2->prepare($query2);
        $st2->bindParam(':roomIdx',$row['roomIdx'],PDO::PARAM_STR);
        $st2->execute();
        $st2->setFetchMode(PDO::FETCH_NUM);
        $hash = $st2->fetchAll();

        //배열형식으로 되어있어 배열을 품
        $hashlist=array();
        for($i=0;$i<count($hash);$i++){
            array_push($hashlist,$hash[$i][0]);
        }

        if($hashlist){
            $row["hashTag"] = $hashlist;
        }else{
            $row["hashTag"] = 'null';
        }

        $result[] = $row;
    }

    $st = null;
    $pdo = null;

    return $result;
}



function userComplexView($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select COALESCE(UC.complexIdx,\"null\") as complexIdx,
       COALESCE(C.complexName,\"null\") as complexName,
       COALESCE(C.complexAddress,\"null\") as complexAddress,
       COALESCE(CI.complexImg,\"https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%EB%B0%A9%20%EA%B8%B0%EB%B3%B8%EC%9D%B4%EB%AF%B8%EC%A7%80.PNG?alt=media&token=ac7a7438-5dde-4666-bccd-6ab0c07d0f36\") as complexImg,
       COALESCE(RN.roomNum,\"0\") as roomNum,
       COALESCE(C.kindOfBuilding,\"null\") as roomType,
       COALESCE(concat(C.householdNum,'세대'),\"null\") as householdNum,
       COALESCE(C.completionDate,\"null\") as completionDate
from (select Max(createdAt) as createdAt, complexIdx from UserComplexLog
where userIdx=:userIdx and isDeleted=\"N\"
group by complexIdx) as UC
         left join Complex as C
                   on C.complexIdx = UC.complexIdx
         left join (select C.complexIdx, C.complexImg
                    from (select min(complexImgIdx) as complexImgIdx, complexIdx
                          from ComplexImg
                          group by complexIdx) as CI
                             left join ComplexImg as C
                                       on C.complexImgIdx = CI.complexImgIdx and C.complexIdx = CI.complexIdx) as CI
                   on CI.complexIdx = UC.complexIdx
         left join (select complexIdx, count(complexIdx) as roomNum
                    from RoomInComplex
                    group by complexIdx) as RN
                   on RN.complexIdx = UC.complexIdx
order by UC.createdAt desc";

    $st = $pdo->prepare($query);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $result = array();
    //행을 한줄씩 읽음
    while($row = $st -> fetch()) {
        //한줄 읽은 행에 거기에 맞는 해시태그 추가
        $pdo2 = pdoSqlConnect();
        $query2="select hashtag from RoomHashTag
        where roomIdx=:roomIdx";
        $st2 = $pdo2->prepare($query2);
        $st2->bindParam(':roomIdx',$row['roomIdx'],PDO::PARAM_STR);
        $st2->execute();
        $st2->setFetchMode(PDO::FETCH_NUM);
        $hash = $st2->fetchAll();

        //배열형식으로 되어있어 배열을 품
        $hashlist=array();
        for($i=0;$i<count($hash);$i++){
            array_push($hashlist,$hash[$i][0]);
        }

        if($hashlist){
            $row["hashTag"] = $hashlist;
        }else{
            $row["hashTag"] = 'null';
        }

        $result[] = $row;
    }

    $st = null;
    $pdo = null;

    return $result;
}



function userRoomView($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "
select URL.roomIdx,
       COALESCE(C.complexName,'null') as complexName,
       COALESCE(R.monthlyRent,'null') as monthlyRent,
       COALESCE(R.lease,'null') as lease,
       COALESCE(R.kindOfRoom,'null') as roomType,
       COALESCE(R.thisFloor,'null') as thisFloor,
       COALESCE(concat(R.exclusiveArea,\"㎡\"), 'null') as exclusiveArea,
       case when R.maintenanceCost=0 then 'null' else concat('관리비 ',R.maintenanceCost,'만') end as maintenanceCost,
       COALESCE(R.roomSummary,'null') as roomSummary,
       COALESCE(RI.roomImg,'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%EB%B0%A9%20%EA%B8%B0%EB%B3%B8%EC%9D%B4%EB%AF%B8%EC%A7%80.PNG?alt=media&token=ac7a7438-5dde-4666-bccd-6ab0c07d0f36') as roomImg,
       COALESCE(R.checkedRoom,'N') as checkedRoom,
       COALESCE(UH.heart, 'N') as heart,
       COALESCE(R.sold, 'N') as sold,
       COALESCE(R.isDeleted, 'N') as isDeleted,
       COALESCE(R.open, 'Y') as open
from (select Max(userRoomLogIdx) as userRoomLogIdx, roomIdx from UserRoomLog
where userIdx=:userIdx and isDeleted=\"N\"
group by roomIdx) as URL
left join UserRoomLog as URL2
on URL2.userRoomLogIdx = URL.userRoomLogIdx and URL2.roomIdx = URL.roomIdx
left join RoomInComplex as RIC
on URL.roomIdx = RIC.roomIdx
left join Complex as C
on RIC.complexIdx = C.complexIdx
left join Room as R
on URL.roomIdx = R.roomIdx
left join (SELECT userIdx, roomIdx, heart
                    FROM UserHeart
                    where userIdx = :userIdx) as UH
on UH.roomIdx = URL.roomIdx
left join (select RI.roomIdx, R.roomImg
from (select min(roomImgIdx) as roomImgIdx, roomIdx
from RoomImg
group by roomIdx) as RI
left join RoomImg as R
on R.roomImgIdx = RI.roomImgIdx and R.roomIdx = RI.roomIdx) as RI
on RI.roomIdx = URL.roomIdx
order by URL2.createdAt desc";

    $st = $pdo->prepare($query);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $result = array();
    //행을 한줄씩 읽음
    while($row = $st -> fetch()) {
        //한줄 읽은 행에 거기에 맞는 해시태그 추가
        $pdo2 = pdoSqlConnect();
        $query2="select hashtag from RoomHashTag
        where roomIdx=:roomIdx";
        $st2 = $pdo2->prepare($query2);
        $st2->bindParam(':roomIdx',$row['roomIdx'],PDO::PARAM_STR);
        $st2->execute();
        $st2->setFetchMode(PDO::FETCH_NUM);
        $hash = $st2->fetchAll();

        //배열형식으로 되어있어 배열을 품
        $hashlist=array();
        for($i=0;$i<count($hash);$i++){
            array_push($hashlist,$hash[$i][0]);
        }

        if($hashlist){
            $row["hashTag"] = $hashlist;
        }else{
            $row["hashTag"] = 'null';
        }

        $result[] = $row;

    }

    $st = null;
    $pdo = null;

    return $result;
}

function deleteSearchRecord($userIdx){
    $pdo = pdoSqlConnect();
    $query = "update UserSearchLog
set isDeleted=\"Y\"
where userIdx = :userIdx";

    $st = $pdo->prepare($query);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->execute();
    $st = null;
    $pdo = null;
}


function searchRecently($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select C.regionName as regionName,
       COALESCE(C.address,'null') as address,
       COALESCE(C.tag,'null') as tag,
       case when regionName like '%동' then 'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/icon%2F%EC%A7%80%EC%97%AD%EB%AA%85%EC%95%84%EC%9D%B4%EC%BD%98.PNG?alt=media&token=9cd01fe3-122b-4faa-86b5-0af71919afd4'
       when regionName like '%역' then 'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/icon%2F%EC%97%AD%20%EC%95%84%EC%9D%B4%EC%BD%98.PNG?alt=media&token=6ea88cf0-e8f7-45cd-9459-1819aaf0b73a'
       else 'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/icon%2F%EC%95%84%ED%8C%8C%ED%8A%B8%EC%95%84%EC%9D%B4%EC%BD%98.PNG?alt=media&token=b67cb97f-0174-4828-b538-8c1954fb732b'
       end as icon
from((select C.searchLog as regionName , null as address , null as tag, C.createdAt as createdAt
from (select searchLog,Max(createdAt) as createdAt from UserSearchLog where isDeleted=\"N\" and userIdx = :userIdx group by searchLog) as C
where searchlog like '%동')
union
(select R.regionName, R.address, S.stationLine as tag, R.createdAt  from
(select searchlog as regionName , null as address , createdAt
from (select searchLog,Max(createdAt) as createdAt from UserSearchLog where isDeleted=\"N\" and userIdx = :userIdx group by searchLog) as C
where searchlog like '%역') as R
left join Station as S
on S.stationName = R.regionName)
union
(select R.regionName, C.complexAddress, C.kindOfbuilding, R.createdAt from
(select C.searchlog as regionName , null as address , null as tag, C.createdAt from (select searchLog,Max(createdAt) as createdAt from UserSearchLog where isDeleted=\"N\" and userIdx = :userIdx group by searchLog) as C
where not searchlog like  '%역' and not searchlog like  '%동') as R
left join Complex as C
on C.complexName = R.regionName)) as C
order by createdAt desc
limit 10";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


function searchList($keyWord)
{
    $pdo = pdoSqlConnect();
    $query = "select C.regionName as regionName,
       COALESCE(C.address,'null') as address,
       COALESCE(C.tag,'null') as tag,
       case when regionName like '%동' then 'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/icon%2F%EC%A7%80%EC%97%AD%EB%AA%85%EC%95%84%EC%9D%B4%EC%BD%98.PNG?alt=media&token=9cd01fe3-122b-4faa-86b5-0af71919afd4'
       when regionName like '%역' then 'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/icon%2F%EC%97%AD%20%EC%95%84%EC%9D%B4%EC%BD%98.PNG?alt=media&token=6ea88cf0-e8f7-45cd-9459-1819aaf0b73a'
       else 'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/icon%2F%EC%95%84%ED%8C%8C%ED%8A%B8%EC%95%84%EC%9D%B4%EC%BD%98.PNG?alt=media&token=b67cb97f-0174-4828-b538-8c1954fb732b'
       end as icon
from(
(select complexName as regionName , complexAddress as address , kindOfBuilding as tag from Complex where isDeleted='N')
union
(select dongAddress as regionName, null as address, null as tag from Region where isDeleted='N')
union
(select stationName as regionName, null as address, stationLine as tag from Station where isDeleted='N')) as C
where C.regionName like concat('%',:keyWord,'%')
order by icon desc
limit 20";

    $st = $pdo->prepare($query);
    $st->bindParam(':keyWord',$keyWord,PDO::PARAM_STR);
    //    $st->execute([$param,$param]);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function homeEvent()
{
    $pdo = pdoSqlConnect();
    $query = "select homeEventImg, homeEventUrl from HomeEvent
where isDeleted=\"N\"
order by createdAt desc
limit 5";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function homeContent()
{
    $pdo = pdoSqlConnect();
    $query = "select COALESCE(postImg,'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%EB%B0%A9%20%EA%B8%B0%EB%B3%B8%EC%9D%B4%EB%AF%B8%EC%A7%80.PNG?alt=media&token=ac7a7438-5dde-4666-bccd-6ab0c07d0f36') as postImg,
       COALESCE(postUrl,\"null\") as postUrl,
       COALESCE(postTitle,\"null\") as postTitle,
       COALESCE(FORMAT(postViewCount , 0),\"0\") as postViewCount
from NaverPost
where isDeleted = \"N\"
order by createdAt desc
limit 5";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


function homeComplexInterest($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select URL.complexIdx,
       COALESCE(C.complexName, \"null\")     as complexName,
       COALESCE(CI.complexImg, \"https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%EB%B0%A9%20%EA%B8%B0%EB%B3%B8%EC%9D%B4%EB%AF%B8%EC%A7%80.PNG?alt=media&token=ac7a7438-5dde-4666-bccd-6ab0c07d0f36\") as complexImg,
       COALESCE(concat(RN.roomNum,'개의 방'), \"0개의 방\")     as roomNum,
       COALESCE(C.kindOfBuilding, \"null\")     as kindOfBuilding,
       COALESCE(concat(C.householdNum,'세대'), \"null\")     as householdNum,
       COALESCE(C.completionDate, \"null\")     as completionDate
from (select Max(userComplexLogIdx) as userComplexLogIdx, complexIdx from UserComplexLog
where userIdx=:userIdx and isDeleted=\"N\"
group by complexIdx) as URL
left join UserComplexLog as URL2
on URL2.userComplexLogIdx = URL.userComplexLogIdx and URL2.complexIdx = URL.complexIdx
left join Complex as C
on C.complexIdx = URL.complexIdx
left join (select COM.complexName,
                           COM.kindOfBuilding,
                           COM.householdNum,
                           COM.completionDate,
                           CI.complexIdx,
                           CI.complexImg as complexImg
                    from (select complexIdx, Max(createdAt) as createdAt
                          from ComplexImg
                          group by complexIdx) as C
                             left join ComplexImg as CI
                                       on CI.complexIdx = CI.complexIdx and C.createdAt = CI.createdAt
                             left join Complex as COM
                                       on COM.complexIdx = CI.complexIdx
) as CI
                   on CI.complexIdx = C.complexIdx
         left join (select complexIdx, count(complexIdx) as roomNum
                    from RoomInComplex
                    group by complexIdx) as RN
                   on RN.complexIdx = C.complexIdx
order by URL2.createdAt desc
limit 5";

    $st = $pdo->prepare($query);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


function homeRoomInterest($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select substring_index(U.searchLog,' ',-1) as searchLog,
       COALESCE(concat(RN.roomNum,'개의 방'),'0개의 방') as roomNum,
       COALESCE(R.dongImg,'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%EB%B0%A9%20%EA%B8%B0%EB%B3%B8%EC%9D%B4%EB%AF%B8%EC%A7%80.PNG?alt=media&token=ac7a7438-5dde-4666-bccd-6ab0c07d0f36') as dongImg,
       replace(replace(U.roomType,'투쓰리룸','투ㆍ쓰리룸'),'|',',') as roomType
from (select U.searchLog, U.createdAt, R.roomType from
     (select searchLog, Max(createdAt) as createdAt
      from UserSearchLog
      where userIdx = :userIdx
    group by searchLog) as U
left join UserSearchLog as R
on R.searchLog = U.searchLog and R.createdAt = U.createdAt) as U
         left join Region as R
                   on R.dongAddress = U.searchLog
         left join (select substring_index(roomAddress, ' ', -1)        as dongName,
                           count(substring_index(roomAddress, ' ', -1)) as roomNum
                    from Room
                    group by substring_index(roomAddress, ' ', -1)) as RN
                   on R.dongName = RN.dongName
where U.searchLog Like '%동' or U.searchLog Like '%면' or  U.searchLog Like '%읍'
order by U.createdAt desc
limit 5";

    $st = $pdo->prepare($query);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


//READ
function roomDetail($roomIdx,$userIdx)
{
    $pdo = pdoSqlConnect();
    $query1 = "select
       COALESCE(U.heart,'N') as heart,
       R.roomIdx,
       COALESCE(RIC.complexIdx, \"null\") as complexIdx,
       COALESCE(R.sold,\"N\") as sold,
       COALESCE(R.checkedRoom,\"N\") as sold,
       COALESCE(R.roomSummary, \"null\") as roomSummary,
       COALESCE(SN.securityNum, \"0\") as securityNum,
       COALESCE(R.monthlyRent, \"null\") as monthlyRent,
       COALESCE(R.lease, \"null\") as lease,
       case when R.maintenanceCost=0 then 'null' else concat(R.maintenanceCost,'만') end as maintenanceCost,
       COALESCE(R.parking, \"null\") as parking,
       COALESCE(R.shortTermRental, \"null\") as shortTermRental,
       COALESCE(R.monthlyLivingExpenses, \"null\") as monthlyLivingExpenses,
       COALESCE(R.kindOfRoom, \"null\") as kindOfRoom,
       COALESCE(R.thisFloor, \"null\") as thisFloor,
       COALESCE(R.buildingFloor, \"null\") as buildingFloor,
       COALESCE(concat(R.exclusiveArea,\"㎡\"), \"null\") as exclusiveArea,
       COALESCE(concat(R.contractArea,\"㎡\"), \"null\") as contractArea,
       COALESCE(R.kindOfHeating, \"null\") as kindOfHeating,
       COALESCE(R.builtIn, \"null\") as builtIn,
       COALESCE(concat(DATE_FORMAT(R.completionDate,\"%Y.%m\"),\" 준공\"), \"null\") as completionDate,
       COALESCE(concat(R.householdNum,\"세대\"), \"null\") as householdNum,
       COALESCE(concat(R.parkingPerHousehold,\"대\"), \"null\") as parkingPerHousehold,
       COALESCE(R.elevator, \"null\") as elevator,
       COALESCE(R.pet, \"null\") as pet,
       COALESCE(R.balcony, \"null\") as balcony,
       COALESCE(R.rentSubsidy, \"null\") as rentSubsidy,
       COALESCE(R.moveInDate, \"null\") as moveInDate,
       R.latitude,
       R.longitude,
       COALESCE(R.roomAddress,'null') as roomAddress,
       COALESCE(R.score, \"null\") as score,
       COALESCE(R.scoreComment, \"null\") as scoreComment,
       COALESCE(R.description, \"null\") as description,
       COALESCE(A.agencyIdx, \"null\") as agencyIdx,
       COALESCE(A.agencyBossPhone, \"null\") as agencyBossPhone,
       COALESCE(A.agencyBossName, \"null\") as agencyBossName,
       COALESCE(A.agencyAddress, \"null\") as agencyAddress,
       COALESCE(A.agencyName, \"null\") as agencyName,
       COALESCE(A.agencyMemberName, \"null\") as agencyMemberName,
       COALESCE(A.agencyMemberPosition, \"null\") as agencyMemberPosition,
       COALESCE(A.agencyMemberProfileImg, \"https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%ED%94%84%EB%A1%9C%ED%95%84%20%EA%B8%B0%EB%B3%B8%EC%82%AC%EC%A7%84.PNG?alt=media&token=7e94ef45-54cc-4cfa-9b2d-8c091d953914\") as agencyMemberProfileImg,
       COALESCE(A.agencyMemberPhone, \"null\") as complexIdx,
       COALESCE(A.quickInquiry, \"null\") as complexIdx
from (select * from Room where roomIdx = :roomIdx and isDeleted = \"N\") as R
         left join (SELECT userIdx,roomIdx,heart FROM UserHeart
where userIdx = :userIdx
) as U
                   on R.roomIdx = U.roomIdx
         left join RoomInComplex as RIC
                   on RIC.roomIdx = R.roomIdx
         left join Complex as C
                   on RIC.complexIdx = C.complexIdx
         left join (select R.roomIdx, R.iconType, count(R.iconType) as securityNum
                    from (
                             select R.roomIdx, I.iconType
                             from RoomIcon as R
                                      left join Icon as I
                                                on I.iconIdx = R.iconIdx
                             where R.roomIdx = :roomIdx) as R
                    group by R.iconType
                    Having R.iconType = \"보안/안전시설\") as SN
                   on SN.roomIdx = R.roomIdx
         left join (select AR.roomIdx,
                           A.agencyIdx,
                           A.agencyBossPhone,
                           A.agencyBossName,
                           A.agencyAddress,
                           A.agencyName,
                           A.quickInquiry,
                           AM.agencyMemberName,
                           AM.agencyMemberPosition,
                           AM.agencyMemberProfileImg,
                           AM.agencyMemberPhone
                    from Agency as A
                             left join AgencyMember as AM
                                       on AM.agencyIdx = A.agencyIdx
                             left join AgencyRoom as AR
                                       on AR.agencyIdx = A.agencyIdx
                    where AR.roomIdx = :roomIdx
                      and AM.agencyMemberPosition != \"대표공인중개사\"
                      and A.isDeleted = \"N\"
                    limit 1
) as A
                   on A.roomIdx = R.roomIdx";

    //방 이미지 쿼리
    $query2="select COALESCE(roomImg,\"https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%EB%B0%A9%20%EA%B8%B0%EB%B3%B8%EC%9D%B4%EB%AF%B8%EC%A7%80.PNG?alt=media&token=ac7a7438-5dde-4666-bccd-6ab0c07d0f36\") as roomImg from RoomImg where roomIdx=:roomIdx";

    //방 해시태그 쿼리
    $query3="select COALESCE(hashTag,\"null\") as roomImg from RoomHashTag where roomIdx=:roomIdx";

    //query1 실행
    $st = $pdo->prepare($query1);
    $st->bindParam(':roomIdx',$roomIdx,PDO::PARAM_STR);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetch();

    //query2 실행
    $st = $pdo->prepare($query2);
    $st->bindParam(':roomIdx',$roomIdx,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_NUM);
    $temp=$st->fetchAll();
    $roomImg=array();
    for($i=0;$i<count($temp);$i++){
        array_push($roomImg,$temp[$i][0]);
    }
    $res["roomImg"]=$roomImg;

    //query3 실행
    $st = $pdo->prepare($query3);
    $st->bindParam(':roomIdx',$roomIdx,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_NUM);
    $temp=$st->fetchAll();
    $hashTag=array();
    for($i=0;$i<count($temp);$i++){
        array_push($hashTag,$temp[$i][0]);
    }
    $res["hashTag"]=$hashTag;

    $st = null;
    $pdo = null;

    return $res;
}


function complexDetail($complexIdx,$userIdx)
{
    $pdo = pdoSqlConnect();
    $query1 = "select COALESCE(UH.heart, \"N\") as heart,
       COALESCE(C.complexName, \"null\") as complexName,
       COALESCE(C.complexAddress, \"null\") as complexAddress,
       COALESCE(C.completionDate, \"null\") as completionDate,
       COALESCE(C.complexNum, \"null\") as complexNum,
       COALESCE(concat(C.householdNum,\"세대\"), \"null\") as householdNum,
       COALESCE(concat(\"가구당 \",C.parkingPerHousehold,\"대\"), \"null\") as parkingPerHousehold,
       COALESCE(C.kindOfHeating, \"null\") as kindOfHeating,
       COALESCE(C.madebyBuilding, \"null\") as madebyBuilding,
       COALESCE(C.complexSize, \"null\") as complexSize,
       COALESCE(C.kindOfHeating, \"null\") as kindOfHeating,
       COALESCE(C.fuel, \"null\") as fuel,
       COALESCE(concat(C.floorAreaRatio,\"%\"), \"null\") as floorAreaRatio,
       COALESCE(concat(C.buildingCoverageRatio,\"%\"), \"null\") as buildingCoverageRatio,
       COALESCE(C.complexDealing, \"-\") as complexDealing,
       COALESCE(C.complexLease, \"-\") as complexLease,
       COALESCE(R.regionDealing, \"-\") as regionDealing,
       COALESCE(R.regionLease, \"-\") as regionLease,
       COALESCE(C.latitude, \"null\") as latitude,
       COALESCE(C.longitude, \"null\") as longitude
from (select * from Complex where complexIdx = :complexIdx) as C
         left join (SELECT userIdx,complexIdx,heart FROM UserHeart
where userIdx = :userIdx
) as UH
                   on UH.complexIdx = C.complexIdx
         left join Region as R
                   on R.dongAddress = C.complexAddress";

    //방 이미지 쿼리
    $query2="select COALESCE(complexImg,\"https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%EB%B0%A9%20%EA%B8%B0%EB%B3%B8%EC%9D%B4%EB%AF%B8%EC%A7%80.PNG?alt=media&token=ac7a7438-5dde-4666-bccd-6ab0c07d0f36\") as complexImg from ComplexImg where complexIdx=:complexIdx";

    //query1 실행
    $st = $pdo->prepare($query1);
    $st->bindParam(':complexIdx',$complexIdx,PDO::PARAM_STR);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetch();

    //query2 실행
    $st = $pdo->prepare($query2);
    $st->bindParam(':complexIdx',$complexIdx,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_NUM);
    $temp=$st->fetchAll();
    $complexImg=array();
    for($i=0;$i<count($temp);$i++){
        array_push($complexImg,$temp[$i][0]);
    }
    $res["complexImg"]=$complexImg;

    $st = null;
    $pdo = null;

    return $res;
}

function complexSizeInfo($complexIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select
       COALESCE(kindOfArea,\"null\") as kindOfArea,
       COALESCE(roomDesignImg,\"https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%EC%84%A4%EA%B3%84%EB%8F%84%20%EA%B8%B0%EB%B3%B8%EC%9D%B4%EB%AF%B8%EC%A7%80.jpg?alt=media&token=acfde156-fc0b-4ba3-bf75-f81544c2c6c2\") as roomDesignImg,
       COALESCE(concat(exclusiveArea,\"㎡\"),\"null\") as exclusiveArea,
       COALESCE(concat(contractArea,\"㎡\"),\"null\")as contractArea,
       COALESCE(concat(roomNum,\"개\"),\"null\")as roomNum,
       COALESCE(concat(bathroomNum,\"개\"),\"null\")as bathroomNum,
       COALESCE(concat(householdNum,\"세대\"),\"null\") as householdNum,
       COALESCE(maintenanceCost,\"-\") as maintenanceCost
from ComplexInfo
where complexIdx = :complexIdx";

    $st = $pdo->prepare($query);
    $st->bindParam(':complexIdx',$complexIdx,PDO::PARAM_STR);
    $st->execute();
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


function surroundingRecommendationComplex($complexIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select COM.complexName,
       COALESCE(CI.complexImg,\"https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%EB%B0%A9%20%EA%B8%B0%EB%B3%B8%EC%9D%B4%EB%AF%B8%EC%A7%80.PNG?alt=media&token=ac7a7438-5dde-4666-bccd-6ab0c07d0f36\") as complexImg
from (select complexIdx, Max(createdAt) as createdAt
      from ComplexImg
      group by complexIdx) as C
         left join ComplexImg as CI
                   on CI.complexIdx = CI.complexIdx and C.createdAt = CI.createdAt
         left join Complex as COM
                   on COM.complexIdx = CI.complexIdx
where CI.complexIdx != :complexIdx and COM.isDeleted = \"N\" and CI.isDeleted = \"N\"
";

    $st = $pdo->prepare($query);
    $st->bindParam(':complexIdx',$complexIdx,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


function ComplexInRoomDetail($roomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select R.complexIdx,
       COALESCE(CI.roomDesignImg,\"https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%EC%84%A4%EA%B3%84%EB%8F%84%20%EA%B8%B0%EB%B3%B8%EC%9D%B4%EB%AF%B8%EC%A7%80.jpg?alt=media&token=acfde156-fc0b-4ba3-bf75-f81544c2c6c2\") as roomDesignImg,
       COALESCE(concat(CI.exclusiveArea,\"㎡\"),\"null\") as exclusiveArea,
       COALESCE(concat(CI.contractArea,\"㎡\"),\"null\")as contractArea,
       COALESCE(concat(CI.roomNum,\"개\"),\"null\")as roomNum,
       COALESCE(concat(CI.bathroomNum,\"개\"),\"null\")as bathroomNum,
       COALESCE(C.complexType,\"null\")as complexType,
       COALESCE(concat(CI.householdNum,\"세대\"),\"null\") as householdNum
from (select roomIdx, complexIdx
      from RoomInComplex
      where roomIdx = :roomIdx) as R
         left join ComplexInfo as CI
                   on CI.complexIdx = R.complexIdx
         left join Complex as C
                   on R.complexIdx = C.complexIdx
limit 1";

    $st = $pdo->prepare($query);
    $st->bindParam(':roomIdx',$roomIdx,PDO::PARAM_STR);
    $st->execute();
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

function agencyDetail($agencyIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select COALESCE(A.agencyComment, \"null\") as agencyComment,
       COALESCE(A.quickInquiry, \"N\") as quickInquiry,
       COALESCE(A.agencyName, \"null\") as agencyName,
       COALESCE(A.agencyBossName, \"null\") as agencyBossName,
       COALESCE(AM.agencyMemberProfileImg, 'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%ED%94%84%EB%A1%9C%ED%95%84%20%EA%B8%B0%EB%B3%B8%EC%82%AC%EC%A7%84.PNG?alt=media&token=7e94ef45-54cc-4cfa-9b2d-8c091d953914') as agencyBossImg,
       COALESCE(A.mediationNumber, \"null\") as mediationNumber,
       COALESCE(A.companyRegistrationNumber, \"null\") as companyRegistrationNumber,
       COALESCE(A.agencyBossPhone, \"null\") as agencyBossPhone,
       COALESCE(A.agencyAddress, \"null\") as agencyAddress,
       COALESCE(DATE_FORMAT(A.joinDate,\"%Y년 %m월 %d일\"), \"null\") as joinDate,
       COALESCE(concat(A.completedRoom,\"개의 방\"), \"null\") as completedRoom
from Agency as A
left join AgencyMember as AM
on A.agencyBossName = AM.agencyMemberName and AM.agencyMemberPosition = \"대표공인중개사\"
where A.agencyIdx = :agencyIdx";

    $st = $pdo->prepare($query);
    $st->bindParam(':agencyIdx',$agencyIdx,PDO::PARAM_STR);
    $st->execute();
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

function agencyMember($agencyIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select COALESCE(agencyMemberName, \"null\") as agencyMemberName,
       COALESCE(agencyMemberPosition, \"null\") as agencyMemberPosition,
       COALESCE(agencyMemberProfileImg, \"https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%ED%94%84%EB%A1%9C%ED%95%84%20%EA%B8%B0%EB%B3%B8%EC%82%AC%EC%A7%84.PNG?alt=media&token=7e94ef45-54cc-4cfa-9b2d-8c091d953914\") as agencyMemberProfileImg
from AgencyMember where agencyIdx=:agencyIdx;";

    $st = $pdo->prepare($query);
    $st->bindParam(':agencyIdx',$agencyIdx,PDO::PARAM_STR);
    $st->execute();
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function roomOption($roomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select I.iconName,I.iconImg from RoomIcon as R
left join Icon as I
on R.iconIdx = I.iconIdx
where R.roomIdx=:roomIdx and I.iconType = \"옵션\";";

    $st = $pdo->prepare($query);
    $st->bindParam(':roomIdx',$roomIdx,PDO::PARAM_STR);
    $st->execute();
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function roomSecurity($roomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select I.iconName,I.iconImg from RoomIcon as R
left join Icon as I
on R.iconIdx = I.iconIdx
where R.roomIdx=:roomIdx and I.iconType = \"보안/안전시설\";";

    $st = $pdo->prepare($query);
    $st->bindParam(':roomIdx',$roomIdx,PDO::PARAM_STR);
    $st->execute();
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function addressRoomNum($address,$roomType)
{
    $pdo = pdoSqlConnect();
    $query = "select count(*) from Room
where roomAddress Like concat('%',:address,'%')
and kindOfRoom regexp :roomType";

    $st = $pdo->prepare($query);
    $st->bindParam(':address',$address,PDO::PARAM_STR);
    $st->bindParam(':roomType',$roomType,PDO::PARAM_STR);
    $st->execute();
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_NUM);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0][0];
}

function addressComplexNum($address)
{
    $pdo = pdoSqlConnect();
    $query = "select count(*) from Complex
where complexAddress Like concat('%',:address,'%')";

    $st = $pdo->prepare($query);
    $st->bindParam(':address',$address,PDO::PARAM_STR);
    $st->execute();
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_NUM);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0][0];
}

function addressAgencyNum($address)
{
    $pdo = pdoSqlConnect();
    $query = "select count(*) from Agency
where agencyAddress like concat('%',:address,'%') and isDeleted='N'";

    $st = $pdo->prepare($query);
    $st->bindParam(':address',$address,PDO::PARAM_STR);
    $st->execute();
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_NUM);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0][0];
}

function complexRoomNum($complexIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select count(*) from RoomInComplex
where complexIdx=:complexIdx";

    $st = $pdo->prepare($query);
    $st->bindParam(':complexIdx',$complexIdx,PDO::PARAM_STR);
    $st->execute();
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_NUM);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0][0];
}

function agencyRoomNum($agencyIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select count(*) from AgencyRoom
where agencyIdx=:agencyIdx";

    $st = $pdo->prepare($query);
    $st->bindParam(':agencyIdx',$agencyIdx,PDO::PARAM_STR);
    $st->execute();
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_NUM);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0][0];
}

function rangeComplexNum($roomType,$latitude,$longitude,$scale)
{
    $pdo = pdoSqlConnect();
    $query = "select count(*) from Complex
where
kindOfBuilding regexp :roomType
and isDeleted = 'N'
and latitude >= (:latitude-(:scale/100))
and latitude <= (:latitude+(:scale/100))
and longitude >= (:longitude-(:scale/100))
and longitude <= (:longitude+(:scale/100))";

    $st = $pdo->prepare($query);
    $st->bindParam(':roomType',$roomType,PDO::PARAM_STR);
    $st->bindParam(':latitude',$latitude,PDO::PARAM_STR);
    $st->bindParam(':longitude',$longitude,PDO::PARAM_STR);
    $st->bindParam(':scale',$scale,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_NUM);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0][0];
}

function rangeAgencyNum($latitude,$longitude,$scale)
{
    $pdo = pdoSqlConnect();
    $query = "select count(*) from Agency
where
latitude >= (:latitude-(:scale/100))
and latitude <= (:latitude+(:scale/100))
and longitude >= (:longitude-(:scale/100))
and longitude <= (:longitude+(:scale/100))
";

    $st = $pdo->prepare($query);
    $st->bindParam(':latitude',$latitude,PDO::PARAM_STR);
    $st->bindParam(':longitude',$longitude,PDO::PARAM_STR);
    $st->bindParam(':scale',$scale,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_NUM);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0][0];
}


function rangeRoomNum($roomType,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax,$latitude,$longitude,$scale)
{
    $pdo = pdoSqlConnect();
    $query = "select count(*) from Room as R
         left join AgencyRoom as AR
                   on R.roomIdx = AR.roomIdx
         left join Agency as A
                   on A.agencyIdx = AR.agencyIdx
         left join UserHeart as UH
                   on R.roomIdx = UH.roomIdx
left join (select agencyIdx, count(agencyIdx) as agencyRoomNum from AgencyRoom
group by agencyIdx) as ARN
on ARN.agencyIdx = A.agencyIdx
where kindOfRoom regexp :roomType and left(maintenanceCost, 1) >= :maintenanceCostMin and left(maintenanceCost, 1) <= :maintenanceCostMax
and left(exclusiveArea, char_length(exclusiveArea)-1) >= :exclusiveAreaMin and left(exclusiveArea, char_length(exclusiveArea)-1) <= :exclusiveAreaMax
and R.isDeleted = 'N'
and R.latitude >= (:latitude-(:scale/100))
and R.latitude <= (:latitude+(:scale/100))
and R.longitude >= (:longitude-(:scale/100))
and R.longitude <= (:longitude+(:scale/100))";

    $st = $pdo->prepare($query);
    $st->bindParam(':roomType',$roomType,PDO::PARAM_STR);
    $st->bindParam(':maintenanceCostMin',$maintenanceCostMin,PDO::PARAM_STR);
    $st->bindParam(':maintenanceCostMax',$maintenanceCostMax,PDO::PARAM_STR);
    $st->bindParam(':exclusiveAreaMin',$exclusiveAreaMin,PDO::PARAM_STR);
    $st->bindParam(':exclusiveAreaMax',$exclusiveAreaMax,PDO::PARAM_STR);
    $st->bindParam(':latitude',$latitude,PDO::PARAM_STR);
    $st->bindParam(':longitude',$longitude,PDO::PARAM_STR);
    $st->bindParam(':scale',$scale,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_NUM);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0][0];
}

function addressComplexList($roomType,$address)
{
    $pdo = pdoSqlConnect();
    $query = "select COALESCE(C.complexIdx, \"null\")     as complexIdx,
       COALESCE(C.complexName, \"null\")     as complexName,
       COALESCE(C.complexAddress, \"null\")     as complexAddress,
       COALESCE(CI.complexImg, \"https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%EB%B0%A9%20%EA%B8%B0%EB%B3%B8%EC%9D%B4%EB%AF%B8%EC%A7%80.PNG?alt=media&token=ac7a7438-5dde-4666-bccd-6ab0c07d0f36\") as complexImg,
       COALESCE(RN.roomNum, \"0\")     as roomNum,
       COALESCE(C.kindOfBuilding, \"null\")     as kindOfBuilding,
       COALESCE(concat(C.householdNum,'세대'), \"null\")     as householdNum,
       COALESCE(C.completionDate, \"null\")     as completionDate
from Complex as C
         left join (select COM.complexName,
                           COM.complexAddress,
                           COM.kindOfBuilding,
                           COM.householdNum,
                           COM.completionDate,
                           CI.complexIdx,
                           CI.complexImg as complexImg
                    from (select complexIdx, Max(createdAt) as createdAt
                          from ComplexImg
                          group by complexIdx) as C
                             left join ComplexImg as CI
                                       on CI.complexIdx = CI.complexIdx and C.createdAt = CI.createdAt
                             left join Complex as COM
                                       on COM.complexIdx = CI.complexIdx
) as CI
                   on CI.complexIdx = C.complexIdx
         left join (select complexIdx, count(complexIdx) as roomNum
                    from RoomInComplex
                    group by complexIdx) as RN
                   on RN.complexIdx = C.complexIdx
where C.isDeleted = \"N\"
and C.complexAddress like concat('%',:address,'%')
and C.kindOfBuilding regexp :roomType";

    $st = $pdo->prepare($query);
    $st->bindParam(':roomType',$roomType,PDO::PARAM_STR);
    $st->bindParam(':address',$address,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function addressAgencyList($address)
{
    $pdo = pdoSqlConnect();
    $query1 = "select A.agencyIdx,
       COALESCE(A.agencyName,'null') as agencyName,
       COALESCE(A.agencyAddress,'null') as agencyAddress,
       COALESCE(A.agencyComment,'null') as agencyComment,
       COALESCE(AM.agencyMemberProfileImg,'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%ED%94%84%EB%A1%9C%ED%95%84%20%EA%B8%B0%EB%B3%B8%EC%82%AC%EC%A7%84.PNG?alt=media&token=7e94ef45-54cc-4cfa-9b2d-8c091d953914') as agencyBossImg,
       A.latitude,
       A.longitude
from Agency as A
         left join (select agencyIdx,
                           COALESCE(agencyMemberProfileImg,
                                    \"https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%ED%94%84%EB%A1%9C%ED%95%84%20%EA%B8%B0%EB%B3%B8%EC%82%AC%EC%A7%84.PNG?alt=media&token=7e94ef45-54cc-4cfa-9b2d-8c091d953914\") as agencyMemberProfileImg
                    from AgencyMember
                    where agencyMemberPosition = \"대표공인중개사\") as AM
                   on AM.agencyIdx = A.agencyIdx
where A.agencyAddress like concat('%',:address,'%') and A.isDeleted='N'";

    $st = $pdo->prepare($query1);
    $st->bindParam(':address',$address,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $result = array();
    //행을 한줄씩 읽음
    while($row = $st -> fetch()) {
        //한줄 읽은 행에 거기에 맞는 해시태그 추가
        $pdo2 = pdoSqlConnect();
        $query2="select COALESCE(R.roomIdx, \"null\") as roomIdx,
       COALESCE(concat('월세 ',R.monthlyRent), \"null\") as monthlyRent,
       COALESCE(R.lease, \"null\") as lease,
       COALESCE(R.kindOfRoom, \"null\") as kindOfRoom,
       COALESCE(R.thisFloor, \"null\") as thisFloor,
       COALESCE(concat(R.exclusiveArea,\"㎡\"), \"null\") as exclusiveArea,
       case when R.maintenanceCost=0 then 'null' else concat('관리비 ',R.maintenanceCost,'만') end as maintenanceCost,
       COALESCE(RI.roomImg, \"https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%EB%B0%A9%20%EA%B8%B0%EB%B3%B8%EC%9D%B4%EB%AF%B8%EC%A7%80.PNG?alt=media&token=ac7a7438-5dde-4666-bccd-6ab0c07d0f36\") as roomImg
from Room as R
left join AgencyRoom as AR
on R.roomIdx = AR.roomIdx
left join (select CI.roomIdx, CI.roomImg as roomImg
from (select roomIdx, Min(roomImgIdx) as roomImgIdx
from RoomImg
group by roomIdx) as C
left join RoomImg as CI
on CI.roomIdx = CI.roomIdx and C.roomImgIdx = CI.roomImgIdx) as RI
on RI.roomIdx=R.roomIdx
where AR.agencyIdx = :agencyIdx and R.isDeleted = \"N\" and R.sold =\"N\"
limit 2";
        $st2 = $pdo2->prepare($query2);
        $st2->bindParam(':agencyIdx',$row['agencyIdx'],PDO::PARAM_STR);
        $st2->execute();
        $st2->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st2->fetchAll();


        if($res){
            $row["roomlist"] = $res;
        }else{
            $row["roomlist"] = 'null';
        }

        $result[] = $row;

    }

    $st = null;
    $pdo = null;

    return $result;
}


function rangeAgencyList($latitude,$longitude,$scale)
{
    $pdo = pdoSqlConnect();
    $query1 = "select A.agencyIdx,
       COALESCE(A.agencyName,'null') as agencyName,
       COALESCE(A.agencyAddress,'null') as agencyAddress,
       COALESCE(A.agencyComment,'null') as agencyComment,
       COALESCE(AM.agencyMemberProfileImg,'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%ED%94%84%EB%A1%9C%ED%95%84%20%EA%B8%B0%EB%B3%B8%EC%82%AC%EC%A7%84.PNG?alt=media&token=7e94ef45-54cc-4cfa-9b2d-8c091d953914') as agencyBossImg,
       A.latitude,
       A.longitude
from Agency as A
         left join (select agencyIdx,
                           COALESCE(agencyMemberProfileImg,
                                    'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%ED%94%84%EB%A1%9C%ED%95%84%20%EA%B8%B0%EB%B3%B8%EC%82%AC%EC%A7%84.PNG?alt=media&token=7e94ef45-54cc-4cfa-9b2d-8c091d953914') as agencyMemberProfileImg
                    from AgencyMember
                    where agencyMemberPosition = \"대표공인중개사\") as AM
                   on AM.agencyIdx = A.agencyIdx
where A.isDeleted ='N'
and A.latitude >= (:latitude-(:scale/100))
and A.latitude <= (:latitude+(:scale/100))
and A.longitude >= (:longitude-(:scale/100))
and A.longitude <= (:longitude+(:scale/100))";

    $st = $pdo->prepare($query1);
    $st->bindParam(':latitude',$latitude,PDO::PARAM_STR);
    $st->bindParam(':longitude',$longitude,PDO::PARAM_STR);
    $st->bindParam(':scale',$scale,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);


    $result = array();
    //행을 한줄씩 읽음
    while($row = $st -> fetch()) {
        $pdo2 = pdoSqlConnect();
        $query2="select COALESCE(R.roomIdx, \"null\") as roomIdx,
       COALESCE(concat('월세 ',R.monthlyRent), \"null\") as monthlyRent,
       COALESCE(R.lease, \"null\") as lease,
       COALESCE(R.kindOfRoom, \"null\") as kindOfRoom,
       COALESCE(R.thisFloor, \"null\") as thisFloor,
       COALESCE(concat(R.exclusiveArea,\"㎡\"), \"null\") as exclusiveArea,
       case when R.maintenanceCost=0 then 'null' else concat('관리비 ',R.maintenanceCost,'만') end as maintenanceCost,
       COALESCE(RI.roomImg, 'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%EB%B0%A9%20%EA%B8%B0%EB%B3%B8%EC%9D%B4%EB%AF%B8%EC%A7%80.PNG?alt=media&token=ac7a7438-5dde-4666-bccd-6ab0c07d0f36') as roomImg
from Room as R
left join AgencyRoom as AR
on R.roomIdx = AR.roomIdx
left join (select CI.roomIdx, CI.roomImg as roomImg
from (select roomIdx, Min(roomImgIdx) as roomImgIdx
from RoomImg
group by roomIdx) as C
left join RoomImg as CI
on CI.roomIdx = CI.roomIdx and C.roomImgIdx = CI.roomImgIdx) as RI
on RI.roomIdx=R.roomIdx
where AR.agencyIdx = :agencyIdx and R.isDeleted = \"N\" and R.sold =\"N\"
limit 2";
        $st2 = $pdo2->prepare($query2);
        $st2->bindParam(':agencyIdx',$row['agencyIdx'],PDO::PARAM_STR);
        $st2->execute();
        $st2->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st2->fetchAll();


        if($res){
            $row["roomlist"] = $res;
        }else{
            $row["roomlist"] = 'null';
        }

        $result[] = $row;

    }

    $st = null;
    $pdo = null;

    return $result;
}



function rangeComplexList($roomType,$latitude,$longitude,$scale)
{
    $pdo = pdoSqlConnect();
    $query = "select COALESCE(C.complexIdx, \"null\")     as complexIdx,
       COALESCE(C.complexName, \"null\")     as complexName,
       COALESCE(C.complexAddress, \"null\")     as complexAddress,
       COALESCE(CI.complexImg, \"https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%EB%B0%A9%20%EA%B8%B0%EB%B3%B8%EC%9D%B4%EB%AF%B8%EC%A7%80.PNG?alt=media&token=ac7a7438-5dde-4666-bccd-6ab0c07d0f36\") as complexImg,
       COALESCE(RN.roomNum, \"0\")     as roomNum,
       COALESCE(C.kindOfBuilding, \"null\")     as kindOfBuilding,
       COALESCE(concat(C.householdNum,'세대'), \"null\")     as householdNum,
       COALESCE(C.completionDate, \"null\")     as completionDate
from Complex as C
         left join (select COM.complexName,
                           COM.complexAddress,
                           COM.kindOfBuilding,
                           COM.householdNum,
                           COM.completionDate,
                           CI.complexIdx,
                           CI.complexImg as complexImg
                    from (select complexIdx, Max(createdAt) as createdAt
                          from ComplexImg
                          group by complexIdx) as C
                             left join ComplexImg as CI
                                       on CI.complexIdx = CI.complexIdx and C.createdAt = CI.createdAt
                             left join Complex as COM
                                       on COM.complexIdx = CI.complexIdx
) as CI
                   on CI.complexIdx = C.complexIdx
         left join (select complexIdx, count(complexIdx) as roomNum
                    from RoomInComplex
                    group by complexIdx) as RN
                   on RN.complexIdx = C.complexIdx
where C.isDeleted = \"N\"
and C.kindOfBuilding regexp :roomType
and C.latitude >= (:latitude-(:scale/100))
and C.latitude <= (:latitude+(:scale/100))
and C.longitude >= (:longitude-(:scale/100))
and C.longitude <= (:longitude+(:scale/100))";

    $st = $pdo->prepare($query);
    $st->bindParam(':roomType',$roomType,PDO::PARAM_STR);
    $st->bindParam(':latitude',$latitude,PDO::PARAM_STR);
    $st->bindParam(':longitude',$longitude,PDO::PARAM_STR);
    $st->bindParam(':scale',$scale,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


function addressRoomList($roomType,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax,$address,$userIdx)
{
    $pdo = pdoSqlConnect();
    $query1 = "select R.roomIdx,
       COALESCE(C.complexName,'null') as complexName,
       COALESCE(concat('월세 ',R.monthlyRent),'null') as monthlyRent,
       COALESCE(R.lease,'null') as lease,
       COALESCE(R.kindOfRoom,'null') as kindOfRoom,
       COALESCE(R.thisFloor,'null') as thisFloor,
       COALESCE(concat(R.exclusiveArea,\"㎡\"), 'null') as exclusiveArea,
       case when R.maintenanceCost=0 then 'null' else concat('관리비 ',R.maintenanceCost,'만') end as maintenanceCost,
       COALESCE(R.roomSummary,'null') as roomSummary,
       COALESCE(R.latitude, 'null') as latitude,
       COALESCE(R.longitude, 'null') as longitude,
       COALESCE(A.agencyIdx,'null') as agencyIdx,
       COALESCE(A.agencyName,'null') as agencyName,
       COALESCE(A.agencyComment,'null') as agencyName,
       COALESCE(AM.agencyMemberProfileImg,'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%ED%94%84%EB%A1%9C%ED%95%84%20%EA%B8%B0%EB%B3%B8%EC%82%AC%EC%A7%84.PNG?alt=media&token=7e94ef45-54cc-4cfa-9b2d-8c091d953914') as agencyBossImg,
       COALESCE(concat(ARN.agencyRoomNum,'개의 방'),'0개의 방') as agencyRoomNum,
       COALESCE(A.quickInquiry,'N') as checkedRoom,
       COALESCE(R.checkedRoom,'N') as checkedRoom,
       COALESCE(R.plus,'N') as plus,
       COALESCE(UH.heart,'N') as heart
from Room as R
left join RoomInComplex as RIC
on R.roomIdx = RIC.roomIdx
left join Complex as C
on RIC.complexIdx = C.complexIdx
left join (select RI.roomIdx, R.roomImg
from (select min(roomImgIdx) as roomImgIdx, roomIdx
from RoomImg
group by roomIdx) as RI
left join RoomImg as R
on R.roomImgIdx = RI.roomImgIdx and R.roomIdx = RI.roomIdx) as RI
on RI.roomIdx = R.roomIdx
left join AgencyRoom as AR
on AR.roomIdx = R.roomIdx
left join Agency as A
on AR.agencyIdx = A.agencyIdx
left join AgencyMember as AM
on A.agencyBossName = AM.agencyMemberName and AM.agencyMemberPosition=\"대표공인중개사\"
left join (select agencyIdx, count(agencyIdx) as agencyRoomNum from AgencyRoom
group by agencyIdx) as ARN
on ARN.agencyIdx = AR.agencyIdx
left join (select roomIdx, heart from UserHeart where isDeleted =\"N\" and roomIdx is not null and userIdx = :userIdx) as UH
on UH.roomIdx = R.roomIdx
where kindOfRoom regexp :roomType and SUBSTRING_INDEX(SUBSTRING_INDEX(maintenanceCost, ' ', -1),'만',1) >= :maintenanceCostMin and SUBSTRING_INDEX(SUBSTRING_INDEX(maintenanceCost, ' ', -1),'만',1) <= :maintenanceCostMax
and left(exclusiveArea, char_length(exclusiveArea)-1) >= :exclusiveAreaMin and left(exclusiveArea, char_length(exclusiveArea)-1) <= :exclusiveAreaMax
and R.roomAddress Like concat('%',:address,'%') and R.isDeleted = 'N'
order by R.plus desc";

    $st = $pdo->prepare($query1);
    $st->bindParam(':roomType',$roomType,PDO::PARAM_STR);
    $st->bindParam(':maintenanceCostMin',$maintenanceCostMin,PDO::PARAM_STR);
    $st->bindParam(':maintenanceCostMax',$maintenanceCostMax,PDO::PARAM_STR);
    $st->bindParam(':exclusiveAreaMin',$exclusiveAreaMin,PDO::PARAM_STR);
    $st->bindParam(':exclusiveAreaMax',$exclusiveAreaMax,PDO::PARAM_STR);
    $st->bindParam(':address',$address,PDO::PARAM_STR);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $result = array();
    //행을 한줄씩 읽음
    while($row = $st -> fetch()) {
        //한줄 읽은 행에 거기에 맞는 해시태그 추가
        $pdo2 = pdoSqlConnect();
        $query2="select hashtag from RoomHashTag
        where roomIdx=:roomIdx";
        $st2 = $pdo2->prepare($query2);
        $st2->bindParam(':roomIdx',$row['roomIdx'],PDO::PARAM_STR);
        $st2->execute();
        $st2->setFetchMode(PDO::FETCH_NUM);
        $hash = $st2->fetchAll();

        //배열형식으로 되어있어 배열을 품
        $hashlist=array();
        for($i=0;$i<count($hash);$i++){
            array_push($hashlist,$hash[$i][0]);
        }

        if($hashlist){
            $row["hashTag"] = $hashlist;
        }else{
            $row["hashTag"] = 'null';
        }

        $query3="select roomImg from RoomImg
        where roomIdx=:roomIdx";
        $st2 = $pdo2->prepare($query3);
        $st2->bindParam(':roomIdx',$row['roomIdx'],PDO::PARAM_STR);
        $st2->execute();
        $st2->setFetchMode(PDO::FETCH_NUM);
        $roomImg = $st2->fetchAll();

        //배열형식으로 되어있어 배열을 품
        $roomImglist=array();
        for($i=0;$i<count($roomImg);$i++){
            array_push($roomImglist,$roomImg[$i][0]);
        }

        if($roomImglist){
            $row["roomImg"] = $roomImglist;
        }else{
            $row["roomImg"] = 'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%EB%B0%A9%20%EA%B8%B0%EB%B3%B8%EC%9D%B4%EB%AF%B8%EC%A7%80.PNG?alt=media&token=ac7a7438-5dde-4666-bccd-6ab0c07d0f36';
        }
        $result[] = $row;

    }

    $st = null;
    $pdo = null;

    return $result;
}



function complexRoomList($complexIdx,$userIdx)
{
    $pdo = pdoSqlConnect();
    $query1 = "select RC.roomIdx,
       C.complexName,
       COALESCE(concat('월세 ',R.monthlyRent),'null') as monthlyRent,
       COALESCE(R.lease,'null') as lease,
       COALESCE(R.kindOfRoom,'null') as roomType,
       COALESCE(R.thisFloor,'null') as thisFloor,
       COALESCE(concat(R.exclusiveArea,\"㎡\"), 'null') as exclusiveArea,
       case when R.maintenanceCost=0 then 'null' else concat('관리비 ',R.maintenanceCost,'만') end as maintenanceCost,
       COALESCE(R.roomSummary,'null') as roomSummary,
       COALESCE(RI.roomImg,'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%EB%B0%A9%20%EA%B8%B0%EB%B3%B8%EC%9D%B4%EB%AF%B8%EC%A7%80.PNG?alt=media&token=ac7a7438-5dde-4666-bccd-6ab0c07d0f36') as roomImg,
       COALESCE(A.quickInquiry,'N') as quickInquiry,
       COALESCE(R.checkedRoom,'N') as checkedRoom,
       COALESCE(UH.heart, 'N') as heart
from (select complexIdx, roomIdx
      from RoomInComplex
      where complexIdx = :complexIdx) as RC
         left join Complex as C
                   on RC.complexIdx = C.complexIdx
         left join Room as R
                   on RC.roomIdx = R.roomIdx
         left join AgencyRoom as AR
                   on AR.roomIdx = RC.roomIdx
         left join Agency as A
                   on AR.agencyIdx = A.agencyIdx
         left join (SELECT userIdx, roomIdx, heart
                    FROM UserHeart
                    where userIdx = :userIdx
) as UH
                   on UH.roomIdx = RC.roomIdx
         left join (select RI.roomIdx, R.roomImg
                    from (select min(roomImgIdx) as roomImgIdx, roomIdx
                          from RoomImg
                          group by roomIdx) as RI
                             left join RoomImg as R
                                       on R.roomImgIdx = RI.roomImgIdx and R.roomIdx = RI.roomIdx) as RI
                   on RI.roomIdx = RC.roomIdx";

    $st = $pdo->prepare($query1);
    $st->bindParam(':complexIdx',$complexIdx,PDO::PARAM_STR);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $result = array();
    //행을 한줄씩 읽음
    while($row = $st -> fetch()) {
        //한줄 읽은 행에 거기에 맞는 해시태그 추가
        $pdo2 = pdoSqlConnect();
        $query2="select hashtag from RoomHashTag
        where roomIdx=:roomIdx";
        $st2 = $pdo2->prepare($query2);
        $st2->bindParam(':roomIdx',$row['roomIdx'],PDO::PARAM_STR);
        $st2->execute();
        $st2->setFetchMode(PDO::FETCH_NUM);
        $hash = $st2->fetchAll();

        //배열형식으로 되어있어 배열을 품
        $hashlist=array();
        for($i=0;$i<count($hash);$i++){
            array_push($hashlist,$hash[$i][0]);
        }

        if($hashlist){
            $row["hashTag"] = $hashlist;
        }else{
            $row["hashTag"] = 'null';
        }

        $result[] = $row;

    }

    $st = null;
    $pdo = null;

    return $result;
}


function agencyRoomList($agencyIdx,$userIdx)
{
    $pdo = pdoSqlConnect();
    $query1 = "select AR.roomIdx,
       COALESCE(C.complexName,'null') as complexName,
       COALESCE(concat('월세 ',R.monthlyRent),'null') as monthlyRent,
       COALESCE(R.lease,'null') as lease,
       COALESCE(R.kindOfRoom,'null') as roomType,
       COALESCE(R.thisFloor,'null') as thisFloor,
       COALESCE(concat(R.exclusiveArea,\"㎡\"), 'null') as exclusiveArea,
       case when R.maintenanceCost=0 then 'null' else concat('관리비 ',R.maintenanceCost,'만') end as maintenanceCost,
       COALESCE(R.roomSummary,'null') as roomSummary,
       COALESCE(RI.roomImg,'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%EB%B0%A9%20%EA%B8%B0%EB%B3%B8%EC%9D%B4%EB%AF%B8%EC%A7%80.PNG?alt=media&token=ac7a7438-5dde-4666-bccd-6ab0c07d0f36') as roomImg,
       COALESCE(A.quickInquiry,'N') as quickInquiry,
       COALESCE(R.checkedRoom,'N') as checkedRoom,
       COALESCE(UH.heart, 'N') as heart
from (select agencyIdx, roomIdx from AgencyRoom where agencyIdx = :agencyIdx) as AR
left join RoomInComplex as RIC
on AR.roomIdx = RIC.roomIdx
left join Complex as C
on RIC.complexIdx = C.complexIdx
left join Room as R
on AR.roomIdx = R.roomIdx
left join Agency as A
on AR.agencyIdx = A.agencyIdx
left join (SELECT userIdx, roomIdx, heart
                    FROM UserHeart
                    where userIdx = :userIdx) as UH
on UH.roomIdx = AR.roomIdx
left join (select RI.roomIdx, R.roomImg
from (select min(roomImgIdx) as roomImgIdx, roomIdx
from RoomImg
group by roomIdx) as RI
left join RoomImg as R
on R.roomImgIdx = RI.roomImgIdx and R.roomIdx = RI.roomIdx) as RI
on RI.roomIdx = AR.roomIdx";

    $st = $pdo->prepare($query1);
    $st->bindParam(':agencyIdx',$agencyIdx,PDO::PARAM_STR);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $result = array();
    //행을 한줄씩 읽음
    while($row = $st -> fetch()) {
        //한줄 읽은 행에 거기에 맞는 해시태그 추가
        $pdo2 = pdoSqlConnect();
        $query2="select hashtag from RoomHashTag
        where roomIdx=:roomIdx";
        $st2 = $pdo2->prepare($query2);
        $st2->bindParam(':roomIdx',$row['roomIdx'],PDO::PARAM_STR);
        $st2->execute();
        $st2->setFetchMode(PDO::FETCH_NUM);
        $hash = $st2->fetchAll();

        //배열형식으로 되어있어 배열을 품
        $hashlist=array();
        for($i=0;$i<count($hash);$i++){
            array_push($hashlist,$hash[$i][0]);
        }

        if($hashlist){
            $row["hashTag"] = $hashlist;
        }else{
            $row["hashTag"] = 'null';
        }
        $result[] = $row;

    }

    $st = null;
    $pdo = null;

    return $result;
}


function rangeRoomList($roomType,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax,$latitude,$longitude,$scale,$userIdx)
{
    $pdo = pdoSqlConnect();
    $query1 = "select R.roomIdx,
       COALESCE(C.complexName,'null') as complexName,
       COALESCE(concat('월세 ',R.monthlyRent),'null') as monthlyRent,
       COALESCE(R.lease,'null') as lease,
       COALESCE(R.kindOfRoom,'null') as kindOfRoom,
       COALESCE(R.thisFloor,'null') as thisFloor,
       COALESCE(concat(R.exclusiveArea,\"㎡\"), 'null') as exclusiveArea,
       case when R.maintenanceCost=0 then 'null' else concat('관리비 ',R.maintenanceCost,'만') end as maintenanceCost,
       COALESCE(R.roomSummary,'null') as roomSummary,
       COALESCE(R.latitude, 'null') as latitude,
       COALESCE(R.longitude, 'null') as longitude,
       COALESCE(A.agencyIdx,'null') as agencyIdx,
       COALESCE(A.agencyName,'null') as agencyName,
       COALESCE(A.agencyComment,'null') as agencyName,
       COALESCE(AM.agencyMemberProfileImg,'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%ED%94%84%EB%A1%9C%ED%95%84%20%EA%B8%B0%EB%B3%B8%EC%82%AC%EC%A7%84.PNG?alt=media&token=7e94ef45-54cc-4cfa-9b2d-8c091d953914') as agencyBossImg,
       COALESCE(concat(ARN.agencyRoomNum,'개의 방'),'0개의 방') as agencyRoomNum,
       COALESCE(A.quickInquiry,'N') as checkedRoom,
       COALESCE(R.checkedRoom,'N') as checkedRoom,
       COALESCE(R.plus,'N') as plus,
       COALESCE(UH.heart,'N') as heart
from Room as R
left join RoomInComplex as RIC
on R.roomIdx = RIC.roomIdx
left join Complex as C
on RIC.complexIdx = C.complexIdx
left join (select RI.roomIdx, R.roomImg
from (select min(roomImgIdx) as roomImgIdx, roomIdx
from RoomImg
group by roomIdx) as RI
left join RoomImg as R
on R.roomImgIdx = RI.roomImgIdx and R.roomIdx = RI.roomIdx) as RI
on RI.roomIdx = R.roomIdx
left join AgencyRoom as AR
on AR.roomIdx = R.roomIdx
left join Agency as A
on AR.agencyIdx = A.agencyIdx
left join AgencyMember as AM
on A.agencyBossName = AM.agencyMemberName and AM.agencyMemberPosition=\"대표공인중개사\"
left join (select agencyIdx, count(agencyIdx) as agencyRoomNum from AgencyRoom
group by agencyIdx) as ARN
on ARN.agencyIdx = AR.agencyIdx
left join (select roomIdx, heart from UserHeart where isDeleted =\"N\" and roomIdx is not null and userIdx = :userIdx) as UH
on UH.roomIdx = R.roomIdx
where kindOfRoom regexp :roomType and SUBSTRING_INDEX(SUBSTRING_INDEX(maintenanceCost, ' ', -1),'만',1) >= :maintenanceCostMin and SUBSTRING_INDEX(SUBSTRING_INDEX(maintenanceCost, ' ', -1),'만',1) <= :maintenanceCostMax
and left(exclusiveArea, char_length(exclusiveArea)-1) >= :exclusiveAreaMin and left(exclusiveArea, char_length(exclusiveArea)-1) <= :exclusiveAreaMax
and R.isDeleted = 'N'
and R.latitude >= (:latitude-(:scale/100))
and R.latitude <= (:latitude+(:scale/100))
and R.longitude >= (:longitude-(:scale/100))
and R.longitude <= (:longitude+(:scale/100))
order by R.plus desc";

    $st = $pdo->prepare($query1);
    $st->bindParam(':roomType',$roomType,PDO::PARAM_STR);
    $st->bindParam(':maintenanceCostMin',$maintenanceCostMin,PDO::PARAM_STR);
    $st->bindParam(':maintenanceCostMax',$maintenanceCostMax,PDO::PARAM_STR);
    $st->bindParam(':exclusiveAreaMin',$exclusiveAreaMin,PDO::PARAM_STR);
    $st->bindParam(':exclusiveAreaMax',$exclusiveAreaMax,PDO::PARAM_STR);
    $st->bindParam(':latitude',$latitude,PDO::PARAM_STR);
    $st->bindParam(':longitude',$longitude,PDO::PARAM_STR);
    $st->bindParam(':scale',$scale,PDO::PARAM_STR);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);

    $result = array();
    //행을 한줄씩 읽음
    while($row = $st -> fetch()) {
        //한줄 읽은 행에 거기에 맞는 해시태그 추가
        $pdo2 = pdoSqlConnect();
        $query2="select hashtag from RoomHashTag
        where roomIdx=:roomIdx";
        $st2 = $pdo2->prepare($query2);
        $st2->bindParam(':roomIdx',$row['roomIdx'],PDO::PARAM_STR);
        $st2->execute();
        $st2->setFetchMode(PDO::FETCH_NUM);
        $hash = $st2->fetchAll();

        //배열형식으로 되어있어 배열을 품
        $hashlist=array();
        for($i=0;$i<count($hash);$i++){
            array_push($hashlist,$hash[$i][0]);
        }

        if($hashlist){
            $row["hashTag"] = $hashlist;
        }else{
            $row["hashTag"] = 'null';
        }

        $query3="select roomImg from RoomImg
        where roomIdx=:roomIdx";
        $st2 = $pdo2->prepare($query3);
        $st2->bindParam(':roomIdx',$row['roomIdx'],PDO::PARAM_STR);
        $st2->execute();
        $st2->setFetchMode(PDO::FETCH_NUM);
        $roomImg = $st2->fetchAll();

        //배열형식으로 되어있어 배열을 품
        $roomImglist=array();
        for($i=0;$i<count($roomImg);$i++){
            array_push($roomImglist,$roomImg[$i][0]);
        }

        if($roomImglist){
            $row["roomImg"] = $roomImglist;
        }else{
            $row["roomImg"] = 'https://firebasestorage.googleapis.com/v0/b/allroom.appspot.com/o/default%2F%EB%B0%A9%20%EA%B8%B0%EB%B3%B8%EC%9D%B4%EB%AF%B8%EC%A7%80.PNG?alt=media&token=ac7a7438-5dde-4666-bccd-6ab0c07d0f36';
        }
        $result[] = $row;

    }

    $st = null;
    $pdo = null;

    return $result;
}


function testPost($name)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO Test (name) VALUES (?);";

    $st = $pdo->prepare($query);
    $st->execute([$name]);

    $st = null;
    $pdo = null;

}

function createRoomLikes($userIdx,$roomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "insert into UserHeart (userIdx,roomIdx,heart) values (:userIdx,:roomIdx,'Y');";

    $st = $pdo->prepare($query);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->bindParam(':roomIdx',$roomIdx,PDO::PARAM_STR);
    $st->execute();

    $st = null;
    $pdo = null;
}

function createComplexLikes($userIdx,$complexIdx)
{
    $pdo = pdoSqlConnect();
    $query = "insert into UserHeart (userIdx,complexIdx,heart) values (:userIdx,:complexIdx,'Y');";

    $st = $pdo->prepare($query);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->bindParam(':complexIdx',$complexIdx,PDO::PARAM_STR);
    $st->execute();

    $st = null;
    $pdo = null;
}



function createCallLog($userIdx,$roomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO UserCallLog (userIdx, roomIdx) VALUES (:userIdx,:roomIdx);";

    $st = $pdo->prepare($query);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->bindParam(':roomIdx',$roomIdx,PDO::PARAM_STR);
    $st->execute();

    $st = null;
    $pdo = null;
}

function createInquireLog($userIdx,$roomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO UserInquiryLog (userIdx, roomIdx) VALUES (:userIdx,:roomIdx);";

    $st = $pdo->prepare($query);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->bindParam(':roomIdx',$roomIdx,PDO::PARAM_STR);
    $st->execute();

    $st = null;
    $pdo = null;
}



function insertUserRoomlog($userIdx,$roomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO UserRoomLog (userIdx, roomIdx) VALUES (:userIdx,:roomIdx);";

    $st = $pdo->prepare($query);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->bindParam(':roomIdx',$roomIdx,PDO::PARAM_STR);
    $st->execute();

    $st = null;
    $pdo = null;
}


function insertUserSearchLog($jwtUserIdx,$roomType,$address)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO UserSearchLog (userIdx, searchLog, roomType) VALUES (:userIdx,:address,:roomType);";

    $st = $pdo->prepare($query);
    $st->bindParam(':userIdx',$jwtUserIdx,PDO::PARAM_STR);
    $st->bindParam(':roomType',$roomType,PDO::PARAM_STR);
    $st->bindParam(':address',$address,PDO::PARAM_STR);
    $st->execute();

    $st = null;
    $pdo = null;
}

function insertComplexNameInUserSearchLog($userIdx,$complexName)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO UserSearchLog (userIdx, searchLog) VALUES (:userIdx,:complexName);";

    $st = $pdo->prepare($query);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->bindParam(':complexName',$complexName,PDO::PARAM_STR);
    $st->execute();

    $st = null;
    $pdo = null;
}



function insertUserComplexLog($userIdx,$complexIdx)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO UserComplexLog (userIdx, complexIdx) VALUES (:userIdx,:complexIdx);";

    $st = $pdo->prepare($query);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->bindParam(':complexIdx',$complexIdx,PDO::PARAM_STR);
    $st->execute();

    $st = null;
    $pdo = null;
}


function isValidUser($userEmail){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM User WHERE userEmail= ? AND isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userEmail]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}

function isValidPhone($userPhone){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM User WHERE userPhone= :userPhone AND isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->bindParam(':userPhone',$userPhone,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}


function isRoomLike($userIdx,$roomIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM UserHeart WHERE userIdx=:userIdx AND roomIdx=:roomIdx AND isDeleted = 'N') AS exist";

    $st = $pdo->prepare($query);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->bindParam(':roomIdx',$roomIdx,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}

function isComplexLike($userIdx,$complexIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM UserHeart WHERE userIdx=:userIdx AND complexIdx=:complexIdx AND isDeleted = 'N') AS exist;;";

    $st = $pdo->prepare($query);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->bindParam(':complexIdx',$complexIdx,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}

function isValidUserIdx($userIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM User WHERE userIdx= ? AND isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}


function isSearchRecently($userIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM UserSearchLog WHERE userIdx=:userIdx AND isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}

function isValidRoomIdx($roomIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM Room WHERE roomIdx= ?) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$roomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}

function isValidComplexIdx($complexIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM Complex WHERE complexIdx= ?) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$complexIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}

function isValidAgencyIdx($agencyIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM Agency WHERE agencyIdx= ?) AS exist;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$agencyIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}

function isValidRoomInComplex($roomIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM RoomInComplex WHERE roomIdx= ?) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$roomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}

function isValidRoomOption($roomIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(select I.iconName,I.iconImg from RoomIcon as R
left join Icon as I
on R.iconIdx = I.iconIdx
where R.roomIdx=? and I.iconType = \"옵션\") AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$roomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}

function isValidRoomSecurity($roomIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(select I.iconName,I.iconImg from RoomIcon as R
left join Icon as I
on R.iconIdx = I.iconIdx
where R.roomIdx=? and I.iconType = \"보안/안전시설\") AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$roomIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}

//UPDATE
function changeRoomLikes($userIdx,$roomIdx){
    $pdo = pdoSqlConnect();
    $query = "update UserHeart
set heart =
    case
        when heart = 'Y' then 'N'
        when heart = 'N' then 'Y'
        end
where userIdx = :userIdx
  and roomIdx = :roomIdx";

    $st = $pdo->prepare($query);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->bindParam(':roomIdx',$roomIdx,PDO::PARAM_STR);
    $st->execute();
    $st = null;
    $pdo = null;
}

function changeComplexLikes($userIdx,$complexIdx){
    $pdo = pdoSqlConnect();
    $query = "update UserHeart
set heart =
    case
        when heart = 'Y' then 'N'
        when heart = 'N' then 'Y'
        end
where userIdx = :userIdx
  and complexIdx = :complexIdx";

    $st = $pdo->prepare($query);
    $st->bindParam(':userIdx',$userIdx,PDO::PARAM_STR);
    $st->bindParam(':complexIdx',$complexIdx,PDO::PARAM_STR);
    $st->execute();
    $st = null;
    $pdo = null;
}


// CREATE
//    function addMaintenance($message){
//        $pdo = pdoSqlConnect();
//        $query = "INSERT INTO MAINTENANCE (MESSAGE) VALUES (?);";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message]);
//
//        $st = null;
//        $pdo = null;
//
//    }




// RETURN BOOLEAN
//    function isRedundantEmail($email){
//        $pdo = pdoSqlConnect();
//        $query = "SELECT EXISTS(SELECT * FROM USER_TB WHERE EMAIL= ?) AS exist;";
//
//
//        $st = $pdo->prepare($query);
//        //    $st->execute([$param,$param]);
//        $st->execute([$email]);
//        $st->setFetchMode(PDO::FETCH_ASSOC);
//        $res = $st->fetchAll();
//
//        $st=null;$pdo = null;
//
//        return intval($res[0]["exist"]);
//
//    }
