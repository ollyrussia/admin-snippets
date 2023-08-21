<?php
/**
 * Набор общих функций
 */

 function get_default_services($position)
{
	/*"Первичная консультация по психосоматике с терапевтом 2 категории" => "10912457",
					   "Консультация с терапевтом 2 категории" => "8065625",*/
	
	$default_services = array( ["name" => "Бонусная консультация", "id" => "8195405"],
							   ["name" => "Учебная консультация", "id" => "12143512"],
							   ["name" => "Консультация для руководителя 40%", "id" => "10381412"],
							   ["name" => "Благотворительная терапия", "id" => "10562075"],
							   ["name" => "Курирует основной психолог", "id" => "12272928"]
					 		  );
	
	$needed_services = get_services_for_position($position);
	$result = array_merge($default_services, $needed_services);
	return $result;
}

function get_services_for_position($position)
{
	$user_token = get_user_token();
	$url = "https://api.yclients.com/api/v1/company/".$GLOBALS['company_id']."/services";
	
	$response = call_api_get($url, $user_token);
	
	$titles = get_services_by_position($position);

	if($titles == null){
		return [];//empty array(admin case)
	}
	
	$needed_list = [];
	$prev_price_base = 0;
	$prev_price_psy = 0;
	$y = $j = 0;
	for($i = 0; $i < count($response['data']); $i++){
		
		if($response['data'][$i]['booking_title'] == $titles['base']){	
			
			if($response['data'][$i]['price_max'] > $prev_price_base){
					$needed_list[0] = array( "name" => $response['data'][$i]['booking_title'],
											 "id" => $response['data'][$i]['id'],
											 "price" => $response['data'][$i]['price_max']);
				$prev_price_base = $response['data'][$i]['price_max'];
			}
			$y++;
		   }
		
		if($response['data'][$i]['booking_title'] == $titles['psy']){	
			if($response['data'][$i]['price_max'] > $prev_price_psy){
					$needed_list[1] = array( "name" => $response['data'][$i]['booking_title'],
											 "id" => $response['data'][$i]['id'],
									         "price" => $response['data'][$i]['price_max']);
				$prev_price_psy = $response['data'][$i]['price_max'];
			}
			$j++;
		   }
	}
	
	return $needed_list;
}

function get_services_by_position($position)
{
	switch(intval($position)){
			case 188070:
				$needed_base = "Консультация с терапевтом высшей категории";
				$needed_psy = "Первичная консультация по психосоматике с терапевтом высшей категории";
				break;
			case 188071:
				$needed_base = "Консультация с терапевтом 2 категории";
				$needed_psy = "Первичная консультация по психосоматике с терапевтом 2 категории";
				break;
			case 188072:
				$needed_base = "Консультация с терапевтом 1 категории";
				$needed_psy = "Первичная консультация по психосоматике с терапевтом 1 категории";
				break; 
			default:
				return null;
	}
	return array('base' => $needed_base, 'psy' => $needed_psy);
}

function get_default_positions()
{
	$positions = array(
	                   "Психолог высшей категории" => 188070,
		               "Психолог 2 категории" => 188071,
		               "Психолог 1 категории" => 188072,
		               //"Администратор" => 190147
	                   );
	return $positions;
}

function set_default_services($user_id, $position, $yclients_id, $user_token)
{	
	 $default_services = get_default_services($position);
	
	 foreach($default_services as $service){
		 
			 //для 2 услуг нужно вычленение цены	 		 
		$url = "https://api.yclients.com/api/v1/company/".$GLOBALS['company_id']."/services/".$service['id']."/staff";
		$data = array(	
				"master_id" => intval($yclients_id),
				"seance_length" => 3600,
				"technological_card_id" => null
		 );
		$response = call_api_post($url, $data, $user_token);
			 
		if (!isset($response["success"]) || !boolval($response["success"])){
			wp_die("Возникла ошибка при назначении услуг. Пожалуйста, повторите процесс вручную на сайте Yclients.");
		}
	  }
	
	  $serv_count = count($default_services);//психология и психосоматика добавляются в конец дефолтного массива
	
	  switch($position){
			case 188070:
			    $offset_base = $serv_count-1;
			    $offset_psy = $serv_count-2;
				break;
			default:
			    $offset_base = $serv_count-2;
			    $offset_psy = $serv_count-1;
				break;
	 }
	 
	 $base = $default_services[$offset_base]["id"];
	 $psy = $default_services[$offset_psy]["id"];
	 $base_price = $default_services[$offset_base]['price'];
	 $psy_price = $default_services[$offset_psy]['price'];
	
	 update_user_meta( $user_id , 'id_service_base_yclients', $base);
	 update_user_meta( $user_id , 'id_service_psy_yclients', $psy);	
	 update_user_meta( $user_id , 'service_base_price', $base_price);
	 update_user_meta( $user_id , 'service_psy_price', $psy_price);	
	  
}
?>