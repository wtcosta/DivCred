<?php

/**
*
*/
class Recovery extends \HXPHP\System\Model
{
	static $belongs_to = array(
		array('user')
	);

	public static function validar($user_email)
	{
		//Callback para retorno de validações
		$callback_obj = new \stdClass;
		$callback_obj->user = null;
		$callback_obj->code = null;
		$callback_obj->status = false;

		//Verifica se o email existe e retorna array com dados do user
		$user_exists = User::find_by_email($user_email);

		if (!is_null($user_exists)) {
			//Retorna id do user
			$callback_obj->status = true;
			$callback_obj->user = $user_exists;

			//Apaga tokens de recuperações antigos
			self::delete_all(array(
				'conditions' => array(
					'user_id = ?',
					$user_exists->id
				)
			));
		}else{
			$callback_obj->code = 'nenhum-usuario-encontrado';
		}

		return $callback_obj;
	}

	public static function validarToken($token)
	{
		$callback_obj = new \stdClass;
		$callback_obj->user = null;
		$callback_obj->code = null;
		$callback_obj->status = false;

		$validar = self::find_by_token($token);

		if (!is_null($validar)) {
			$callback_obj->status = true;
			$callback_obj->user = $validar->user;
		}else{
			$callback_obj->code = 'token-invalido';
		}

		return $callback_obj;
	}

	public static function limpar($user_id)
	{
		return self::delete_all(array(
			'conditions' => array(
				'user_id = ?',
				$user_id
			)
		));
	}
}