<?php

class Call extends \HXPHP\System\Model
{
	static $belongs_to = array(
		array('debt')
	);

	public static function busca($cpf)
	{
		return self::find('all',array('conditions' => array('cpf = ?', $cpf), 'order' => 'data_cad desc'));
	}

	public static function cadastrar($post, $user_id, $cpf)
	{
		$callbackObj = new \stdClass;
		$callbackObj->divida = false;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		$userCad = array(
			'cpf' => $cpf,
			'user_cad' => $user_id
		);

		$post = array_merge($post, $userCad);

		/*
		//Atualiza o status da divida - DESATIVADO!
		$statusDate = State::find_by_id($post['status']);
		$atualizaStatusDivida = Debt::atualizaStatus($divida, $statusDate->relacionamento);
		if (is_null($atualizaStatusDivida)) {
			$callbackObj->errors = 'Não foi possível atualizar o status da dívida<br />Verifique o cadastro de status';
			return $callbackObj;
		}
		*/

		$cadastrar = self::create($post);

		if ($cadastrar->is_valid()) {
			$callbackObj->status = true;
			$callbackObj->divida = $post;
			return $callbackObj;
		}

		$errors = $cadastrar->errors->get_raw_errors();

		foreach ($errors as $field => $message) {
			array_push($callbackObj->errors, $message[0]);
		}

		return $callbackObj;
	}
}