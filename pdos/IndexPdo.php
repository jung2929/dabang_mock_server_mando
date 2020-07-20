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
       COALESCE(R.sold,\"N\") as sold,
       COALESCE(R.checkedRoom,\"N\") as sold,
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


function complexDetail($complexIdx)
{
    $pdo = pdoSqlConnect();
    $query1 = "select COALESCE(UH.heart, \"N\") as heart,
       COALESCE(C.complexName, \"null\") as complexName,
       COALESCE(C.complexAdress, \"null\") as complexAdress,
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
         left join UserHeart as UH
                   on UH.complexIdx = C.complexIdx
         left join Region as R
                   on R.dongAdress = C.complexAdress";

    //방 이미지 쿼리
    $query2="select COALESCE(complexImg,\"gs://allroom.appspot.com/default/방 기본이미지.PNG\") as complexImg from ComplexImg where complexIdx=:complexIdx";

    //query1 실행
    $st = $pdo->prepare($query1);
    $st->bindParam(':complexIdx',$complexIdx,PDO::PARAM_STR);
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
       COALESCE(roomDesignImg,\"gs://allroom.appspot.com/default/설계도 기본이미지.jpg\") as roomDesignImg,
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
       COALESCE(CI.complexImg,\"gs://allroom.appspot.com/default/방 기본이미지.PNG\") as complexImg
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

function agencyDetail($agencyIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select COALESCE(agencyComment, \"null\") as agencyComment,
       COALESCE(quickInquiry, \"N\") as quickInquiry,
       COALESCE(agencyName, \"null\") as agencyName,
       COALESCE(agencyBossName, \"null\") as agencyBossName,
       COALESCE(mediationNumber, \"null\") as mediationNumber,
       COALESCE(companyRegistrationNumber, \"null\") as companyRegistrationNumber,
       COALESCE(agencyBossPhone, \"null\") as agencyBossPhone,
       COALESCE(agencyAdress, \"null\") as agencyAdress,
       COALESCE(DATE_FORMAT(joinDate,\"%Y년 %m월 %d일\"), \"null\") as joinDate,
       COALESCE(concat(completedRoom,\"개의 방\"), \"null\") as completedRoom
from Agency
where agencyIdx = :agencyIdx";

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
       COALESCE(agencyMemberProfileImg, \"gs://allroom.appspot.com/default/프로필 기본사진.PNG\") as agencyMemberProfileImg
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

function dongRoomNum($dong)
{
    $pdo = pdoSqlConnect();
    $query = "select count(*) from Room
where roomAdress Like concat('%',:dong,'%')";

    $st = $pdo->prepare($query);
    $st->bindParam(':dong',$dong,PDO::PARAM_STR);
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
and latitude >= (:latitude-(:scale/3))
and latitude <= (:latitude+(:scale/3))
and longitude >= (:longitude-(:scale/3))
and longitude <= (:longitude+(:scale/3))";

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



function dongRoomList($roomType,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax,$dong)
{
    $pdo = pdoSqlConnect();
    $query1 = "select COALESCE(R.roomIdx, \"null\") as roomIdx,
       COALESCE(R.monthlyRent, \"null\") as monthlyRent,
       COALESCE(R.lease, \"null\") as lease,
       COALESCE(R.kindOfRoom, \"null\") as kindOfRoom,
       COALESCE(R.thisFloor, \"null\") as thisFloor,
       COALESCE(concat(R.exclusiveArea,\"㎡\"), \"null\") as exclusiveArea,
       COALESCE(concat('관리비 ',left(R.maintenanceCost,2)), \"null\") as maintenanceCost,
       COALESCE(R.roomSummary, \"null\") as roomSummary,
       COALESCE(R.latitude, \"null\") as latitude,
       COALESCE(R.longitude, \"null\") as longitude,
       COALESCE(AR.agencyIdx, \"null\") as agencyIdx,
       COALESCE(A.agencyName, \"null\") as agencyName,
       COALESCE(A.agencyComment, \"null\") as agencyComment,
       COALESCE(A.agencyBossPhone, \"null\") as agencyBossPhone,
       COALESCE(A.agencyName, \"null\") as agencyName,
       COALESCE(ARN.agencyRoomNum, \"null\") as agencyRoomNum,
       COALESCE(A.quickInquiry, \"N\") as quickInquiry,
       COALESCE(R.checkedRoom, \"N\") as checkedRoom,
       COALESCE(R.plus, \"N\") as plus,
       COALESCE(UH.heart, \"N\") as heart
from Room as R
         left join AgencyRoom as AR
                   on R.roomIdx = AR.roomIdx
         left join Agency as A
                   on A.agencyIdx = AR.agencyIdx
         left join UserHeart as UH
                   on R.roomIdx = UH.roomIdx
left join (select agencyIdx, count(agencyIdx) as agencyRoomNum from AgencyRoom
group by agencyIdx) as ARN
on ARN.agencyIdx = A.agencyIdx
where kindOfRoom regexp :roomType and left(maintenanceCost, 5) >= :maintenanceCostMin and left(maintenanceCost, 5) <= :maintenanceCostMax
and left(exclusiveArea, char_length(exclusiveArea)-1) >= :exclusiveAreaMin and left(exclusiveArea, char_length(exclusiveArea)-1) <= :exclusiveAreaMax
and R.roomAdress Like concat('%',:dong,'%') and R.isDeleted = 'N'
order by R.plus desc";

    $st = $pdo->prepare($query1);
    $st->bindParam(':roomType',$roomType,PDO::PARAM_STR);
    $st->bindParam(':maintenanceCostMin',$maintenanceCostMin,PDO::PARAM_STR);
    $st->bindParam(':maintenanceCostMax',$maintenanceCostMax,PDO::PARAM_STR);
    $st->bindParam(':exclusiveAreaMin',$exclusiveAreaMin,PDO::PARAM_STR);
    $st->bindParam(':exclusiveAreaMax',$exclusiveAreaMax,PDO::PARAM_STR);
    $st->bindParam(':dong',$dong,PDO::PARAM_STR);
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
            $row["roomImg"] = 'gs://allroom.appspot.com/default/방 기본이미지.PNG';
        }
        $result[] = $row;

    }

    $st = null;
    $pdo = null;

    return $result;
}

function complexRoomList($complexIdx)
{
    $pdo = pdoSqlConnect();
    $query1 = "select COALESCE(R.roomIdx, \"null\") as roomIdx,
       COALESCE(R.monthlyRent, \"null\") as monthlyRent,
       COALESCE(R.lease, \"null\") as lease,
       COALESCE(R.kindOfRoom, \"null\") as kindOfRoom,
       COALESCE(R.thisFloor, \"null\") as thisFloor,
       COALESCE(concat(R.exclusiveArea,\"㎡\"), \"null\") as exclusiveArea,
       COALESCE(concat('관리비 ',left(R.maintenanceCost,2)), \"null\") as maintenanceCost,
       COALESCE(R.roomSummary, \"null\") as roomSummary,
       COALESCE(R.latitude, \"null\") as latitude,
       COALESCE(R.longitude, \"null\") as longitude,
       COALESCE(AR.agencyIdx, \"null\") as agencyIdx,
       COALESCE(A.agencyName, \"null\") as agencyName,
       COALESCE(A.agencyComment, \"null\") as agencyComment,
       COALESCE(A.agencyBossPhone, \"null\") as agencyBossPhone,
       COALESCE(A.agencyName, \"null\") as agencyName,
       COALESCE(ARN.agencyRoomNum, \"null\") as agencyRoomNum,
       COALESCE(A.quickInquiry, \"N\") as quickInquiry,
       COALESCE(R.checkedRoom, \"N\") as checkedRoom,
       COALESCE(R.plus, \"N\") as plus,
       COALESCE(UH.heart, \"N\") as heart
from Room as R
         left join AgencyRoom as AR
                   on R.roomIdx = AR.roomIdx
         left join Agency as A
                   on A.agencyIdx = AR.agencyIdx
         left join UserHeart as UH
                   on R.roomIdx = UH.roomIdx
left join (select agencyIdx, count(agencyIdx) as agencyRoomNum from AgencyRoom
group by agencyIdx) as ARN
on ARN.agencyIdx = A.agencyIdx
left join RoomInComplex as RIC
on RIC.roomIdx = R.roomIdx
where RIC.complexIdx = :complexIdx
order by R.plus desc";

    $st = $pdo->prepare($query1);
    $st->bindParam(':complexIdx',$complexIdx,PDO::PARAM_STR);
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
            $row["roomImg"] = 'gs://allroom.appspot.com/default/방 기본이미지.PNG';
        }
        $result[] = $row;

    }

    $st = null;
    $pdo = null;

    return $result;
}


function agencyRoomList($agencyIdx)
{
    $pdo = pdoSqlConnect();
    $query1 = "select COALESCE(R.roomIdx, \"null\") as roomIdx,
       COALESCE(R.monthlyRent, \"null\") as monthlyRent,
       COALESCE(R.lease, \"null\") as lease,
       COALESCE(R.kindOfRoom, \"null\") as kindOfRoom,
       COALESCE(R.thisFloor, \"null\") as thisFloor,
       COALESCE(concat(R.exclusiveArea,\"㎡\"), \"null\") as exclusiveArea,
       COALESCE(concat('관리비 ',left(R.maintenanceCost,2)), \"null\") as maintenanceCost,
       COALESCE(R.roomSummary, \"null\") as roomSummary,
       COALESCE(R.latitude, \"null\") as latitude,
       COALESCE(R.longitude, \"null\") as longitude,
       COALESCE(AR.agencyIdx, \"null\") as agencyIdx,
       COALESCE(A.agencyName, \"null\") as agencyName,
       COALESCE(A.agencyComment, \"null\") as agencyComment,
       COALESCE(A.agencyBossPhone, \"null\") as agencyBossPhone,
       COALESCE(A.agencyName, \"null\") as agencyName,
       COALESCE(ARN.agencyRoomNum, \"null\") as agencyRoomNum,
       COALESCE(A.quickInquiry, \"N\") as quickInquiry,
       COALESCE(R.checkedRoom, \"N\") as checkedRoom,
       COALESCE(R.plus, \"N\") as plus,
       COALESCE(UH.heart, \"N\") as heart
from Room as R
         left join AgencyRoom as AR
                   on R.roomIdx = AR.roomIdx
         left join Agency as A
                   on A.agencyIdx = AR.agencyIdx
         left join UserHeart as UH
                   on R.roomIdx = UH.roomIdx
left join (select agencyIdx, count(agencyIdx) as agencyRoomNum from AgencyRoom
group by agencyIdx) as ARN
on ARN.agencyIdx = A.agencyIdx
where AR.agencyIdx = :agencyIdx
order by R.plus desc";

    $st = $pdo->prepare($query1);
    $st->bindParam(':agencyIdx',$agencyIdx,PDO::PARAM_STR);
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
            $row["roomImg"] = 'gs://allroom.appspot.com/default/방 기본이미지.PNG';
        }
        $result[] = $row;

    }

    $st = null;
    $pdo = null;

    return $result;
}


function rangeRoomList($roomType,$maintenanceCostMin,$maintenanceCostMax,$exclusiveAreaMin,$exclusiveAreaMax,$latitude,$longitude,$scale)
{
    $pdo = pdoSqlConnect();
    $query1 = "select COALESCE(R.roomIdx, \"null\") as roomIdx,
       COALESCE(R.monthlyRent, \"null\") as monthlyRent,
       COALESCE(R.lease, \"null\") as lease,
       COALESCE(R.kindOfRoom, \"null\") as kindOfRoom,
       COALESCE(R.thisFloor, \"null\") as thisFloor,
       COALESCE(concat(R.exclusiveArea,\"㎡\"), \"null\") as exclusiveArea,
       COALESCE(concat('관리비 ',left(R.maintenanceCost,2)), \"null\") as maintenanceCost,
       COALESCE(R.roomSummary, \"null\") as roomSummary,
       COALESCE(R.latitude, \"null\") as latitude,
       COALESCE(R.longitude, \"null\") as longitude,
       COALESCE(AR.agencyIdx, \"null\") as agencyIdx,
       COALESCE(A.agencyName, \"null\") as agencyName,
       COALESCE(A.agencyComment, \"null\") as agencyComment,
       COALESCE(A.agencyBossPhone, \"null\") as agencyBossPhone,
       COALESCE(A.agencyName, \"null\") as agencyName,
       COALESCE(ARN.agencyRoomNum, \"null\") as agencyRoomNum,
       COALESCE(A.quickInquiry, \"N\") as quickInquiry,
       COALESCE(R.checkedRoom, \"N\") as checkedRoom,
       COALESCE(R.plus, \"N\") as plus,
       COALESCE(UH.heart, \"N\") as heart
from Room as R
         left join AgencyRoom as AR
                   on R.roomIdx = AR.roomIdx
         left join Agency as A
                   on A.agencyIdx = AR.agencyIdx
         left join UserHeart as UH
                   on R.roomIdx = UH.roomIdx
left join (select agencyIdx, count(agencyIdx) as agencyRoomNum from AgencyRoom
group by agencyIdx) as ARN
on ARN.agencyIdx = A.agencyIdx
where kindOfRoom regexp :roomType and left(maintenanceCost, 5) >= :maintenanceCostMin and left(maintenanceCost, 5) <= :maintenanceCostMax
and left(exclusiveArea, char_length(exclusiveArea)-1) >= :exclusiveAreaMin and left(exclusiveArea, char_length(exclusiveArea)-1) <= :exclusiveAreaMax
and R.isDeleted = 'N'
and latitude >= (:latitude-(:scale/3))
and latitude <= (:latitude+(:scale/3))
and longitude >= (:longitude-(:scale/3))
and longitude <= (:longitude+(:scale/3))
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
            $row["roomImg"] = 'gs://allroom.appspot.com/default/방 기본이미지.PNG';
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
