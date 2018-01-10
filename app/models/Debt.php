<?php

/**
*
*/
class Debt extends \HXPHP\System\Model
{

	static $belongs_to = array(
		array('role'),
		array('company')
	);

	static $validates_presence_of = array(
		array(
			'nome',
			'message' => 'O nome é um campo obrigatório.'
		),
		array(
			'cpf',
			'message' => 'O e-mail é um campo obrigatório.'
		),
		array(
			'valor',
			'message' => 'O usuário é um campo obrigatório.'
		)
	);
/*
	static $validates_uniqueness_of = array(
		array(
			'cpf',
			'message' => 'Já existe um usuário cadastrado!'
		),
		array(
			'email',
			'message' => 'Já existe um e-mail cadastrado!'
		)
	);
*/
	public static function cadastrar($post,$user_id)
	{
		//Cria uma classe vazia pra armazenar o retorno das validações
		$callbackObj = new \stdClass;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		$userCad = array(
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

	public static function atualizar($post, $divida_id)
	{
		//Cria uma classe vazia pra armazenar o retorno das validações
		$callbackObj = new \stdClass;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		$divida = self::find($divida_id);

		$divida->cpf = $post['cpf'];
		$divida->empresa = $post['empresa'];
		$divida->nome = $post['nome'];
		$divida->endereco = $post['endereco'];
		$divida->celular = $post['celular'];
		$divida->telefone = $post['telefone'];
		$divida->recado = $post['recado'];
		$divida->valor = $post['valor'];
		$divida->vencimento = $post['vencimento'];
		$divida->status = $post['status'];
		$divida->obs = $post['obs'];

		$atualizar = $divida->save(false);

		if ($atualizar) {
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