<?php
/**
 * Удаление пользователя с Wordpress и Yclients
 */

add_action('delete_user', 'delete_user_from_yclients');

function delete_user_from_yclients( $user_id )
{
	// получение токена авторизации
	 $user_token = get_user_token();
	 if($user_token != null){
		 $url = "https://api.yclients.com/api/v1/staff/".$GLOBALS['company_id']."/".get_user_meta( $user_id , 'id_yclients', true); 
		 //запрос по сути ничего кроме true не возвращает
		 $response = call_api_delete($url, $user_token);
     }else{
		 wp_die('Ошибка удаления. Повторите действие позже.');
	 }
}
?>