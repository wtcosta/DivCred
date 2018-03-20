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

	public static function buscaCliente($cliente)
	{
		return self::find(array('conditions' => array('cliente_id = ?', $cliente), 'order' => 'data_cad desc'));
	}

	public static function cadastrar($post, $user_id, $cliente)
	{
		$callbackObj = new \stdClass;
		$callbackObj->divida = false;
		$callbackObj->atd = false;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		$dividasAux = explode(",", $post['dividas']);
		$dividasJson = json_encode($dividasAux);

		$userCad = array(
			'cliente_id' => $cliente,
			'user_id' => $user_id,
			'dividas' => $dividasJson
		);

		$post = array_merge($post, $userCad);


		/*user o $dividasJson para atualizar o status*/
		$statusDate = State::find_by_id($post['status_id']);
		foreach ($dividasAux as $value) {
			$atualizaStatusDivida = Debt::atualizaStatus($value, $statusDate->relacionamento);
			if (is_null($atualizaStatusDivida)) {
				$callbackObj->errors = 'Não foi possível atualizar o status da dívida<br />Verifique o cadastro de status';
				return $callbackObj;
			}
		}

		$cadastrar = self::create($post);

		if ($cadastrar->is_valid()) {
			$callbackObj->status = true;
			$callbackObj->divida = $post;
			$callbackObj->atd = $cadastrar;
			return $callbackObj;
		}

		$errors = $cadastrar->errors->get_raw_errors();

		foreach ($errors as $field => $message) {
			array_push($callbackObj->errors, $message[0]);
		}

		return $callbackObj;
	}
}