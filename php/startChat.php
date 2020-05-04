<?php

    function exist_in($matId)
    {
        global $conn;

        $sql = "select show_my_photo,show_my_contact,new_matches,broader_matches,similar_matches from privacy_setting where block_by = '".$matId."' ";
        $res = mysqli_query($conn,$sql);
        $rows = mysqli_num_rows($res);

           if ($rows > 0) {
            $my_photo = mysqli_fetch_assoc($res);
            return array("isRow" => $rows, "show_my_photo_status" => $my_photo['show_my_photo'], "show_my_contact_status" => $my_photo['show_my_contact'], "new_matches" => $my_photo['new_matches'], "broader_matches" => $my_photo['broader_matches'], "similar_matches" => $my_photo['similar_matches']);
   
           } else {
            return array("isRow" => 0);
   
           }
           
    }


     function show_to_whom($matId,$current_login_matri_id)
    {
        global $conn;
        

        $sql = "select count(ei_sender) as counter from expressinterest where (ei_sender = '".$matId."' and ei_receiver = '".$current_login_matri_id."') or (ei_sender= '".$current_login_matri_id."' and ei_receiver = '".$matId."' and receiver_response = 'Accept')";
        $res = mysqli_query($conn,$sql);
        $data = mysqli_fetch_assoc($res);
        return array("isRow" => $data['counter']);
    }

 function get_member_info($to_matri_id)
{
 global $conn;
  $sql = "SELECT firstname, lastname, phone,mobile,phonecode,mobile2,mobile3,email,mobile_verified,status from register where matri_id = '".$to_matri_id."' "
  $res = mysqli_query($conn,$sql);
  $result = mysqli_fetch_assoc($res);
  return $result;
}



public function count_this($data,$type)
{
    global $conn;
switch ($type) {
  case 'count_pc':

  $sql = "select matri_id,religion,caste from register where matri_id= '".$data[0]."' and status NOT IN ('Inactive','deleted') limit 1"
  $result = mysqli_query($conn,$sql);

    break;

    case 'ei':

    $sql = "select ei_sender from expressinterest where ei_sender= '".$data[0]."' and ei_receiver= '".$data[1]."' and receiver_response='Accept'"
  $result = mysqli_query($conn,$sql);


      break;

      case 'count_paid':

        $sql = "select matri_id from register where status NOT IN ('Inactive','deleted','Active') and matri_id=  '".$data[0]."' "
        $result = mysqli_query($conn,$sql);

        break;

        
  case 'contacted_on_block':

      $sql = "select sno from contactedonblock where contacted_from = '".$data[0]."' and contacted_to = '".$data[1]."' "
  $result = mysqli_query($conn,$sql);


    break;


case 'check_ct_to_ct_from':

   $sql = "select ct_from,ct_to from contacteddetails where ct_to like '%".$data[0]."' and ct_from= '".$data[1]."' "
  $result = mysqli_query($conn,$sql);

  break;

  default:
   return 0;
    break;
}  

return mysqli_num_rows($result);

}


 function get_ct_id($to_matri_id,$from_matri_id)
{
    global $conn;
  $result = mysqli_query($conn, "SELECT ct_id as cd FROM `contacteddetails` WHERE ct_to = '".$to_matri_id."' and ct_from = '".$from_matri_id."' ")
  return mysqli_num_rows($result);
}



 function get_cnt($from_matri_id)
{
    global $conn;
  
  $sql = "SELECT plan_duration,pactive_dt,p_no_contacts,r_cnt FROM payments as p WHERE (p.pmatri_id= '".$from_matri_id."' ) order by payid desc limit 1 ";
  $res = mysqli_query($conn,$sql);
  if(mysqli_num_rows($res) == 0){
    return [];
  }else{
    $result = mysqli_fetch_assoc($res);

    return [$result];
  }
}


 function insert_data_to_contact_block_table($from_matri_id,$to_matri_id)
{
    global $conn;
  try {

    $sql = "INSERT INTO contactedonblock (contacted_from,contacted_to) VALUES ('".$from_matri_id."','".$to_matri_id."') "
    mysqli_query($conn,$sql);

  } catch (Exception $e) {
 
  }
}


 function acceptInterest($receiver,$sender)
{
    global $conn;
     try {
       
 
      $sql = "SELECT ei_sender from expressinterest where ei_sender = '".$sender."' and ei_receiver = '".$receiver."' and receiver_response != 'Accept' ";
      $isValid = mysqli_query($conn,$sql);
   if ( mysqli_num_rows($isValid) > 0 ) {
      $sql = "UPDATE expressinterest set receiver_response = 'Accept', sender_response = 'Accept' where ei_sender = '".$sender."' and ei_receiver = '".$receiver."' "
     mysqli_query($conn,$sql);
     return "Interest Accepted";
   }
   return "All things are perfect";
     } catch (Exception $e) {
    
       return "Try Again Later !!";
     }
}




 function isGenderSame($gender,$express_interest_to)
{
    global $conn;
   
   $sql = "SELECT gender from register where matri_id = '".$express_interest_to."' ";
   $res = mysqli_query($conn,$sql);
   $result = mysqli_fetch_assoc($res);
   if ($result['gender'] == $gender) {
     return true;
   } else {
     return false;
   }
   
}


 function insert_data_to_contact_detail_table($from_matri_id,$to_matri_id)
{
    global $conn;
  try {
   
    $sql = "INSERT INTO contacteddetails (ct_from,ct_to,ct_contacted_date)  VALUES('".$from_matri_id."','".$to_matri_id."',now()) ";
    mysqli_query($conn,$sql);
  } catch (Exception $e) {
   
  }
}




public function update_payment($from_matri_id)
{
    global $conn;
 try {
    $chk = 0;
    $sql = "select * from payments where (pmatri_id= '".$from_matri_id."' ) order by payid desc limit 1";
    $result = mysqli_query($conn,$sql);
    if (mysqli_num_rows($result) > 0 ) {
        $row = mysqli_fetch_assoc($result);
       $chk = $row['r_cnt'];
    }

    $inc = $chk + 1;

  

    $sql = "UPDATE payments set  r_cnt = ".$inc." where  pmatri_id = '".$from_matri_id."' "
     mysqli_query($conn,$sql);
    
 } catch (Exception $e) {
  
 }
}



public function check_contact($from_matri_id, $to_matri_id, $gender) {
   
    $divstatus = "block";
    $contd_to = $to_matri_id;
    $ct_from = $from_matri_id;
    $check_ct_to_ct_from = count_this([$contd_to, $ct_from], "check_ct_to_ct_from");
    if ($check_ct_to_ct_from > 0) {
        return "You have already viewed this profile.";
    } else {
        $mid = $from_matri_id;

        //check whether the contacted member is of same gender
        if (!isGenderSame($gender, $contd_to)) {
            insert_data_to_contact_detail_table($mid, $contd_to);
            update_payment($from_matri_id);
        } else {
        }
        return "";
    }
}







    $to_matri_id = $_REQUEST["to_matri_id"];


        $sql = "SELECT matri_id,status,gender FROM register WHERE matri_id = '".$_SESSION['matri_id']."' LIMIT 1";
        $res = mysqli_query($conn,$sql);
        $user = mysqli_fetch_assoc($res);

        $from_matri_id = $user['matri_id'];

  if (strtolower($user['status']) != "paid") {
            die(json_encode([
                "request_status" => "0",
                 "message" => "Buy a Premium Membership to Start Chat with him/her right away. Get Membership"
            ]));
        }


    
        $to_be_show_contact = exist_in($to_matri_id);
        
        $show_contact_to_whom = show_to_whom($to_matri_id, $from_matri_id);
        $to_user_info = get_member_info($to_matri_id);
        // $to_user_info = $to_user_info[0];
    // print_r($to_user_info);die;
    // 
        //get current user status from register table
        $from_user_info = get_member_info($from_matri_id);
        // $from_user_info = $from_user_info[0];
        $count_pc = count_this([$to_matri_id], "count_pc");
        $sql_ei = count_this([$from_matri_id, $to_matri_id], 'ei');
        $sql_rei = count_this([$to_matri_id, $from_matri_id], 'ei');
    
        $count_paid = count_this([$from_matri_id], "count_paid");
        $cd = get_ct_id($to_matri_id, $from_matri_id);
        $ncha = get_cnt($from_matri_id);
        if (count($ncha) == 0) {
            return response()->json([
                "request_status" => "0",
                 "message" => "Buy a Premium Membership to Start Chat with him/her right away. Get Membership"
                ]);
        }
        if (strtolower($user->status) != "paid") {
            return response()->json([
                "request_status" => "0",
                 "message" => "Buy a Premium Membership to Start Chat with him/her right away. Get Membership"
            ]);
        }
      
        $ncha = $ncha[0];
    
    
        $now = date_create(date('Y-m-d'));
        $plan = $ncha->plan_duration;
        $pactive_dt = date_create(date('Y-m-d', strtotime($ncha->pactive_dt)));
        $compeleted_days = date_diff($pactive_dt, $now)->format("%a") - 1;
        $remaining_days = $plan - $compeleted_days;
    
        if (((($ncha->p_no_contacts - $ncha->r_cnt) > 0 and $plan >= $compeleted_days and $from_user_info->status == 'Paid') or ($cd > 0 && $cd != '' && $cd != NULL)) /* && $chk_incom > 0 */ ) {
            if (($count_pc > 0 || $sql_ei > 0 || $sql_rei > 0) && $count_paid > 0) {
                if ($to_be_show_contact['isRow'] > 0 and $to_be_show_contact['show_my_contact_status'] == 1) { //if status 1 and row found with userid in privacy table
                    if ($show_contact_to_whom['isRow'] <= 0) { // if current user e.i or e.i  not accepted
                        $contacted_on_block = count_this([$from_matri_id, $to_matri_id], "contacted_on_block");
                        if ($contacted_on_block <= 0) {
                            insert_data_to_contact_block_table($from_matri_id, $to_matri_id);
                        }
    

                        $message =  $this->check_contact($from_matri_id, $to_matri_id, $user->gender);
              
    
               $data["acceptInterest_response"] = acceptInterest($from_matri_id,$to_matri_id);

                        $data =[
                "request_status" => "1",
                "message" => "success",
                "matri_id" => $to_matri_id
                ];
                return response()->json($data);
                    }
                }


                $data =[
                "request_status" => "1",
                "message" => "success",
                "matri_id" => $to_matri_id
                ];
               $message =  $this->check_contact($from_matri_id, $to_matri_id, $user->gender);
             
    
               $data["acceptInterest_response"] = acceptInterest($from_matri_id,$to_matri_id);
    
    
            } else {
                if ($count_paid == 0) {
                   
    
                    return response()->json([
                        "request_status" => "0",
                         "message" => "Buy a Premium Membership to Start Chat with him/her right away. Get Membership"
                        ]);
    
                } else {

                  $message =  $this->check_contact($from_matri_id, $to_matri_id, $user->gender);
             
    
               $data["acceptInterest_response"] = acceptInterest($from_matri_id,$to_matri_id);
               

                    $data =[
                "request_status" => "1",
                "message" => "success",
                "matri_id" => $to_matri_id
                ];
                return response()->json($data);
                
              
                    
                }
            }
        } else {
         
                return response()->json([
                    "request_status" => "0",
                     "message" => "Buy a Premium Membership to Start Chat him/her right away.   Get Membership"
                    ]);
                
            
        }
    
        return response()->json($data);


?>