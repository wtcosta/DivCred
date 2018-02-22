<?php

/**
*
*/
class Type extends \HXPHP\System\Model
{
	public static function cadastrar($post)
	{
		$callbackObj = new \stdClass;
		$callbackObj->status = false;
		$callbackObj->errors = array();

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

	public static function atualizar($post='', $idPost='')
	{
		//Cria uma classe vazia pra armazenar o retorno das validações
		$callbackObj = new \stdClass;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		//Salva os dados no banco de dados
		$status = self::find($idPost);

		$status->tipo = $post['tipo'];

		$atualizar = $status->save(false);

		if ($atualizar) {
			$callbackObj->status = true;
			return $callbackObj;
		}

		$errors = $atualizar->errors->get_raw_errors();

		foreach ($errors as $field => $message) {
			array_push($callbackObj->errors, $message[0]);
		}

		return $callbackObj;
	}
}