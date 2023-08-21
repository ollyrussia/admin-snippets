<?php
/**
 * Набор функций для работы с API
 * Ключ для апи хранится здесь
 */

 $GLOBALS['api_user_token'] = "";
$GLOBALS['company_id'] = 542516;

function get_request_headers($user_token)
{
	$api_key = 'rf55gcwyhzpa5h6xeb4c';
	$auth = "Bearer ".$api_key;
	if($user_token != ''){
		$auth.=', User '.$user_token;
	}
	$headers = array(
        "Content-Type: application/json", 
        "Accept: application/vnd.yclients.v2+json",
		"Authorization: ".$auth,
    );
	return $headers;
}

function call_api_get($url, $user_token)
{
	$headers = get_request_headers($user_token);
	
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPGET, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	 curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	
	$response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);
	
	return $result;
}

function call_api_post($url, $data, $user_token = '')
 {
	$headers = get_request_headers($user_token);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);
	
	return $result;
 }

function call_api_delete($url, $user_token)
{
	$headers = get_request_headers($user_token);
	
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $response;
}

function call_api_put($url, $user_token)
{
	$headers = get_request_headers($user_token);
	
    $ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
	$response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
	
    return $response;
}
 
function get_user_token() 
{
	$api_user_token = $GLOBALS['api_user_token'];
	
	if(!isset($api_user_token) || $api_user_token == ""){
		$url = "https://api.yclients.com/api/v1/auth";
		$data = array(
			"login" => 'andrtos@gmail.com',
			"password" => '2o7GMpVL!!4'
		);

		$response = call_api_post($url, $data);

		file_put_contents('ycl.log', date('Y-m-d H:i:s')." Получение токена авторизации ".json_encode($response, JSON_UNESCAPED_UNICODE)."\n", FILE_APPEND | LOCK_EX);
		
		if (isset($response['data']["user_token"])){
			$api_user_token = $response['data']["user_token"];
			return $response['data']["user_token"];
		} 
		return null;
	}else{
	    return $api_user_token;
	} 
}

//кастомный патч-запрос для редактирования должности(не предусмотрено в доках апи)
function call_api_patch($url, $data, $user_token)
{
	$headers = get_request_headers($user_token);
	
    $ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

	$response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
	$result = json_decode($response);
	
    return $result;
}
?>