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

//READ
function roomDetail($roomIdx)
{
    $pdo = pdoSqlConnect();
    $query1 = "select U.heart,
       R.roomIdx,
       COALESCE(RIC.complexIdx, \"null\") as complexIdx,
       R.sold,
       R.checkedRoom,
       COALESCE(R.roomSummary, \"null\") as roomSummary,
       COALESCE(SN.securityNum, \"0\") as securityNum,
       COALESCE(R.monthlyRent, \"null\") as monthlyRent,
       COALESCE(R.lease, \"null\") as lease,
       COALESCE(R.maintenanceCost, \"null\") as maintenanceCost,
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
       R.roomAdress,
       COALESCE(R.score, \"null\") as score,
       COALESCE(R.scoreComment, \"null\") as scoreComment,
       COALESCE(R.description, \"null\") as description,
       COALESCE(A.agencyIdx, \"null\") as agencyIdx,
       COALESCE(A.agencyBossPhone, \"null\") as agencyBossPhone,
       COALESCE(A.agencyBossName, \"null\") as agencyBossName,
       COALESCE(A.agencyAdress, \"null\") as agencyAdress,
       COALESCE(A.agencyName, \"null\") as agencyName,
       COALESCE(A.agencyMemberName, \"null\") as agencyMemberName,
       COALESCE(A.agencyMemberPosition, \"null\") as agencyMemberPosition,
       COALESCE(A.agencyMemberProfileImg, \"gs://allroom.appspot.com/default/프로필 기본사진.PNG\") as agencyMemberProfileImg,
       COALESCE(A.agencyMemberPhone, \"null\") as complexIdx,
       COALESCE(A.quickInquiry, \"null\") as complexIdx
from (select * from Room where roomIdx = :roomIdx and isDeleted = \"N\") as R
         left join UserHeart as U
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
                           A.agencyAdress,
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
    $query2="select COALESCE(roomImg,\"gs://allroom.appspot.com/default/방 기본이미지.PNG\") as roomImg from RoomImg where roomIdx=:roomIdx";

    //방 해시태그 쿼리
    $query3="select COALESCE(hashTag,\"null\") as roomImg from RoomHashTag where roomIdx=:roomIdx";

    //query1 실행
    $st = $pdo->prepare($query1);
    $st->bindParam(':roomIdx',$roomIdx,PDO::PARAM_STR);
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

function ComplexInRoomDetail($roomIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select R.complexIdx,
       COALESCE(CI.roomDesignImg,\"gs://allroom.appspot.com/default/설계도 기본이미지.jpg\") as roomDesignImg,
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



function testPost($name)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO Test (name) VALUES (?);";

    $st = $pdo->prepare($query);
    $st->execute([$name]);

    $st = null;
    $pdo = null;

}


function isValidUser($id, $pw){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM User WHERE userId= ? AND userPw = ?) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$id, $pw]);
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


// UPDATE
//    function updateMaintenanceStatus($message, $status, $no){
//        $pdo = pdoSqlConnect();
//        $query = "UPDATE MAINTENANCE
//                        SET MESSAGE = ?,
//                            STATUS  = ?
//                        WHERE NO = ?";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message, $status, $no]);
//        $st = null;
//        $pdo = null;
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
