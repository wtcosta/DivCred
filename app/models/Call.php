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
		$callbackObj = new \stdClass;
		$callbackObj->divida = false;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		$userCad = array(
			'divida' => $divida,
			'user_cad' => $user_id
		);

		$post = array_merge($post, $userCad);

		$statusDate = State::find_by_id($post['status']);

		$atualizaStatusDivida = Debt::atualizaStatus($divida, $statusDate->relacionamento);

		if (is_null($atualizaStatusDivida)) {
			$callbackObj->errors = 'Não foi possível atualizar o status da dívida<br />Verifique o cadastro de status';
			return $callbackObj;
		}

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