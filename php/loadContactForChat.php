<?php

 function setQuery($matri_id,$type)
    {
          
        if ($type == 'membershipSummary') {
        
        $column = "r.emp_in,ctry.country_name,sts.state_name,(case when r.curr_city = NULL then 'Not Specified'  when r.curr_city = 0 then 'Not Specified'  when r.curr_city = '' then 'Not Specified' else cty.city_name end) as city_name , cd.ct_to, date_format(CONVERT_TZ(cd.ct_contacted_date, @@session.time_zone, '+05:30'),'%d %b, %Y %h:%i %p') receivedDate,r.matri_id,r.gender,(FLOOR(DATEDIFF(date_format(now(),'%Y-%m-%d'), date_format(r.`birthdate`,'%Y-%m-%d'))/365.25)) age,r.height,(case when maxSalary = 0  then 'Not Specified' when maxSalary = 1  then 'No income' when maxSalary = 2  then 'Will tell later' when maxSalary = 5000002  then concat(format(5000000,0,'ta_IN'), ' & ','Above') else concat('Rs','.',concat_ws(' - ',format(minSalary,0,'ta_IN'),format(maxSalary,0,'ta_IN'))) end) as income,r.m_status,r.status,rg.religion_name,c.caste_name,ed.edu_name,o.ocp_name
          ,r.firstname,r.lastname,date_format(CONVERT_TZ(cd.ct_contacted_date, @@session.time_zone, '+05:30'),'%Y-%m-%d %H:%i:%s') mdate,(select date_format(CONVERT_TZ(pactive_dt, @@session.time_zone, '+05:30'),'%Y-%m-%d %H:%i:%s') from payments  where pmatri_id = '".$matri_id."' order by payid desc limit 1 ) dt1, p.pt_image ";
  
        $tables = " contacteddetails cd left join register r ON r.matri_id = cd.ct_to 
 LEFT JOIN education_detail ed ON ed.edu_id = r.edu_detail LEFT JOIN occupation o ON o.ocp_id = r.occupation LEFT JOIN religion rg ON rg.religion_id = r.religion left join caste c ON c.caste_id = r.caste  
  left join country as ctry ON ctry.country_id = r.curr_country
        left join state as sts On sts.state_id = r.curr_state
        left join city as cty On cty.city_id = r.curr_city 
         left join photos as p on p.pt_matri_id = cd.ct_to and p.pt_status = 1";
      

        $where = " cd.ct_from='".$matri_id."' and r.status != '' AND r.status NOT IN('Suspend','deleted') having dt1 <= mdate ";

$orderby = "order by date_format(CONVERT_TZ(cd.ct_contacted_date, @@session.time_zone, '+05:30'),'%Y-%m-%d %H:%i:%s') desc";


         return [
             'column' => $column,
             'statement' => $tables,
             'orderby' => $orderby,
             'where' => $where,
         ];
        }
if ($type == 'membershipHistory') {
        
    $column = "r.emp_in,ctry.country_name,sts.state_name,(case when r.curr_city = NULL then 'Not Specified'  when r.curr_city = 0 then 'Not Specified'  when r.curr_city = '' then 'Not Specified' else cty.city_name end) as city_name , cd.ct_to, date_format(CONVERT_TZ(cd.ct_contacted_date, @@session.time_zone, '+05:30'),'%d %b, %Y %h:%i %p') receivedDate,r.matri_id,r.gender,(FLOOR(DATEDIFF(date_format(now(),'%Y-%m-%d'), date_format(r.`birthdate`,'%Y-%m-%d'))/365.25)) age,r.height,(case when maxSalary = 0  then 'Not Specified' when maxSalary = 1  then 'No income' when maxSalary = 2  then 'Will tell later' when maxSalary = 5000002  then concat(format(5000000,0,'ta_IN'), ' & ','Above') else concat('Rs','.',concat_ws(' - ',format(minSalary,0,'ta_IN'),format(maxSalary,0,'ta_IN'))) end) as income,r.m_status,r.status,rg.religion_name,c.caste_name,ed.edu_name,o.ocp_name
          ,r.firstname,r.lastname,date_format(CONVERT_TZ(cd.ct_contacted_date, @@session.time_zone, '+05:30'),'%Y-%m-%d %H:%i:%s') mdate,(select date_format(CONVERT_TZ(pactive_dt, @@session.time_zone, '+05:30'),'%Y-%m-%d %H:%i:%s') from payments where pmatri_id = '".$matri_id."' order by payid desc limit 1 ) dt1,r.matri_id , p.pt_image";
  
    $tables = " contacteddetails cd left join register r ON r.matri_id = cd.ct_to 
 LEFT JOIN education_detail ed ON ed.edu_id = r.edu_detail LEFT JOIN occupation o ON o.ocp_id = r.occupation LEFT JOIN religion rg ON rg.religion_id = r.religion left join caste c ON c.caste_id = r.caste  
  left join country as ctry ON ctry.country_id = r.curr_country
        left join state as sts On sts.state_id = r.curr_state
        left join city as cty On cty.city_id = r.curr_city 
    left join photos as p on p.pt_matri_id = cd.ct_to and p.pt_status = 1 ";
    
    $where = " cd.ct_from='".$matri_id."' and r.status != '' AND r.status NOT IN('Suspend','deleted') having mdate < dt1 ";
    
    $orderby = " order by date_format(CONVERT_TZ(cd.ct_contacted_date, @@session.time_zone, '+05:30'),'%Y-%m-%d %H:%i:%s') desc";
    


     return [
         'column' => $column,
         'statement' => $tables,
         'orderby' => $orderby,
         'where' => $where,
     ];
    }


   }  // fn end


 function getContacts($type,$matri_id)
{
	global $conn;
$image_base_path = "";
$result = array();        
$data = setQuery($matri_id,$type);
$sql = "SELECT ".$data["column"]." FROM ". $data["statement"]. " WHERE ".$data["where"]." ".$data["orderby"];
$res =  mysqli_query($conn,$sql);

while( $row = mysqli_fetch_assoc( $res )){

	if(empty($row['pt_image'])){
        $profile_image =  "/images/No-Image-icon_".strtolower($row['gender']).".png";
    }
    else if(file_exists($image_base_path."/"."maangal_photos/".$row['matri_id']."/thumbnail_" . $row['pt_image'])){
            $profile_image = "/"."maangal_photos/".$row['matri_id']."/thumbnail_" . $row['pt_image'];
        }
       else if(file_exists($image_base_path."/"."maangal_photos/".$row['matri_id']."/" . $row['pt_image'])){
            $profile_image = "/"."maangal_photos/".$row['matri_id']."/" . $row['pt_image'];
        }else {
            $profile_image =  "/images/No-Image-icon_".strtolower($row['gender']).".png";
        }
        



        unset($row['mdate']);
      unset($row['dt1']);
      unset($row['pt_image']);
       $row['src'] = $profile_image;
        if (isset($row['height']) && !empty($row['height']) ) {
                    $height = (int)$row['height'];
                    $height = floor(($height/2.54)/12)."ft ".round(fmod(($height/2.54),12))."in";
                    $row['height'] = $height;       
   }
	array_push($result, $row);
}

    return $result;
} // fn end









   $sql = "SELECT matri_id,status FROM register WHERE matri_id = '".$_SESSION['matri_id']."' LIMIT 1";
   $res = mysqli_query($conn,$sql);
   $user = mysqli_fetch_assoc($res);

    if (strtolower($user['status']) != "paid") {
            
            die(json_encode([
                "request_status" => "0",
                "message" => "Buy a Premium Membership to Start Chat right away. Get Membership"
              ]));    
    }
   

    $summary = getContacts('membershipSummary',$user['matri_id']);
    $history = getContacts('membershipHistory',$user['matri_id']);

    $data = array_merge($summary,$history);
  
     die(json_encode([
        "request_status" => "1",
         "message" => "success",
         "total_data" => count($data),
         "result" => $data,
    ]));


?>