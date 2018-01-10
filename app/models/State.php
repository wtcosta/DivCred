<?php

class State extends \HXPHP\System\Model
{
	static $belongs_to = array(
		array('debt'),
		array('call')
	);

	static $validates_presence_of = array(
		array(
			'tipo',
			'message' => 'O tipo do status é obrigatório.'
		),
		array(
			'nome',
			'message' => 'O nome é um campo obrigatório.'
		)
	);

	public static function cadastrar($post)
	{
		//Cria uma classe vazia pra armazenar o retorno das validações
		$callbackObj = new \stdClass;
		$callbackObj->status = false;
		$callbackObj->errors = array();

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