<?php
/**
 * Набор функций для валидации 
 * и отправки запроса на создание пользователя на Yclients, синхронно с Wordpress
 */

add_action('user_register', 'send_user_to_yclients');
	
 function send_user_to_yclients( $user_id )
 {	 
	 // получение токена авторизации
	 $user_token = get_user_token();
	 if($user_token != null){
		 $url = "https://api.yclients.com/api/v1/company/".$GLOBALS['company_id']."/staff/quick";
		 $data = array(
			"name" => ucfirst($_POST['first_name']).' '.ucfirst($_POST['last_name']),
			"specialization" => $_POST['specialization'],
			"position_id" => intval($_POST['category']),
			"user_phone" => $_POST['phone'],
			"is_user_invite" => false //true
		 );
		 //быстрое создание сотрудника 
		 $response = call_api_post($url, $data, $user_token);
		
		 if(!isset($response["success"]) || !$response["success"]){
			 $admes = 'Ошибка соединения с Yclients.';
			 if(isset($response["meta"])){
				 $admes = $response["meta"]['message'];
			 }
			 sync_error_rollback($user_id, $admes, $response);
			 return false;
		 }
		 
		 $yclients_id = $response['data']['id'];
		 
		 update_user_meta( $user_id, 'id_yclients', $yclients_id);
		 update_user_meta( $user_id, 'specialization', $_POST['specialization']);
		 update_user_meta( $user_id, 'category', $_POST['category']);
		 update_user_meta( $user_id , 'phone', $_POST['phone']);
	
		 set_default_services($user_id, intval($_POST['category']), $yclients_id, $user_token);		
	}else{
		 sync_error_rollback($user_id, 'Не получен user token');
	}
}

//rollback в случае ошибки
function sync_error_rollback( $user_id, $admes, $response = array('Unknown'=>'Unknown'))
{
	wp_delete_user($user_id);
	file_put_contents('ycl.log', date('Y-m-d H:i:s')." Автоудаление пользователя. Ошибка синхронизации с Yclients ($admes)".json_encode($response, JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND | LOCK_EX);
	wp_die('Произошла ошибка. Повторите попытку позже. ('.$admes.')');
}

?>