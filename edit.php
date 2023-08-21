<?php
/**
 * Редактирование юзера в Wordpress и Yclients
 */

 add_action('edit_user_profile_update','change_user_on_yclients');

function change_user_on_yclients($user_id)
{
	$yclients_id = get_user_meta( $user_id , 'id_yclients', true);
	
	$name = get_user_meta( $user_id , 'first_name', true);
	$surname = get_user_meta( $user_id , 'last_name', true);
	$new_name = ucfirst($_POST['first_name']);
	$new_surname = ucfirst($_POST['last_name']);
	
	$url = "https://api.yclients.com/api/v1/staff/".$GLOBALS['company_id']."/".$yclients_id;
	$parameters = "?";
	$changed = false;//flag
	
	if($new_name != $name || $new_surname != $surname){
		 $changed = true;
		 $parameters .= "name=".$new_name." ".$new_surname."&";	
		 
	}
	//if($new_spec != $specialization){
		 //$changed = true; 
		 $parameters .= "specialization=".$_POST['specialization']."&";	
	//}
	/*
	if($_POST['photo'] != '' && isset($_POST['photo'])){
		update_user_meta( $user_id, 'photo', $_POST['photo'] );
		$parameters .= "avatar_big=".$_POST['photo']."&";	
	}*/
	
	//if($changed){
		$user_token = get_user_token();
		$response = call_api_put($url.$parameters, $user_token);
		
		custom_change_user_position($yclients_id, $user_token);
		
		clear_base_and_psy_services($user_id);
		set_default_services($user_id, intval($_POST['category']), $yclients_id, $user_token);
	
		update_user_meta( $user_id, 'specialization', $_POST['specialization']);
//	}
	
}

function custom_change_user_position($yclients_id, $user_token)
{
	$positions = get_default_positions();
	$pos_title = '';
	
	foreach($positions as $title => $id){
		if($id == intval($_POST['category'])){
			$pos_title = $title;
		}
	}
	
	$data = array(
			"id" => $yclients_id,
			"position" => array("id" => intval($_POST['category']), "title" => $pos_title),
			"position_id" => intval($_POST['category']),
	              );
	
	$url = "https://yclients.com/api/v1/company/".$GLOBALS['company_id']."/staff/".$yclients_id;
	$response = call_api_patch($url, $data, $user_token);
	
}
//rework
function clear_base_and_psy_services($user_id){
	$user_token = get_user_token();
	$yclients_id = get_user_meta( $user_id , 'id_yclients', true);
	$base_id = get_user_meta( $user_id , 'id_service_base_yclients', true);
	$psy_id = get_user_meta( $user_id , 'id_service_psy_yclients', true);
	
	$url_base = "https://api.yclients.com/api/v1/company/".$GLOBALS['company_id']."/services/".$base_id."/staff/".$yclients_id;
	call_api_delete($url_base, $user_token);
	
	$url_psy = "https://api.yclients.com/api/v1/company/".$GLOBALS['company_id']."/services/".$psy_id."/staff/".$yclients_id;
	call_api_delete($url_psy, $user_token);
}
?>
