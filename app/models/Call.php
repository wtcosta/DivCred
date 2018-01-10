<?php

class Call extends \HXPHP\System\Model
{
	static $belongs_to = array(
		array('debt')
	);

	public static function busca($divida)
	{
		return self::find('all',array('conditions' => array('divida = ?', $divida), 'order' => 'data_cad desc'));
	}

	public static function cadastrar($post, $user_id, $divida)
	{
		//Cria uma classe vazia pra armazenar o retorno das validações
		$callbackObj = new \stdClass;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		$userCad = array(
			'divida' => $divida,
			'user_cad' => $user_id
		);

		$post = array_merge($post, $userCad);

		//Salva os dados no banco de dados
		$cadastrar = self::create($post);

		if ($cadastrar->is_valid()) {
			$callbackObj->status = true;
			return $callbackObj;
		}

		$errors = $cadastrar->errors->get_raw_errors();

		foreach ($errors as $field => $message) {
			array_push($callbackObj->errors, $message[0]);
		}

		return $callbackObj;
	}
}