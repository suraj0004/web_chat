<?php


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


 $to_matri_id = $_REQUEST["to_matri_id"];



       $sql = "SELECT matri_id,status,gender FROM register WHERE matri_id = '".$_SESSION['matri_id']."' LIMIT 1";
        $res = mysqli_query($conn,$sql);
        $user = mysqli_fetch_assoc($res);

        $from_matri_id = $user['matri_id'];
    
  if (strtolower($user['status']) != "paid") {
           die(json_encode([
                "request_status" => "2",
                 "message" => "Buy a Premium Membership to Start Chat with him/her right away. Get Membership"
            ]));
        }

        // Is already Viewed this
    $isAlreadyViewed = count_this([$to_matri_id, $from_matri_id], "check_ct_to_ct_from");

    if ($isAlreadyViewed > 0) {
             die(json_encode(["request_status" => "0", "message" => "dont show pop up"]));
    }else{
   
           die(json_encode(["request_status" => "1", "message" => "show pop up"]));         
    }





?>