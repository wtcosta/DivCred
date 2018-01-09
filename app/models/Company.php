<?php

/**
*
*/
class Company extends \HXPHP\System\Model
{
	static $belongs_to = array(
		array('role')
	);

	static $validates_presence_of = array(
		array(
			'empresa',
			'message' => 'O nome da empresa é obrigatório.'
		),
		array(
			'email',
			'message' => 'O e-mail é um campo obrigatório.'
		),
		array(
			'cnpj',
			'message' => 'O CNPJ é um campo obrigatório.'
		)
	);

	static $validates_uniqueness_of = array(
		array(
			'cnpj',
			'message' => 'Já existe um CNPJ cadastrado!'
		),
		array(
			'email',
			'message' => 'Já existe um e-mail cadastrado!'
		)
	);

	public static function cadastrar($post, $user_id)
	{
		//Cria uma classe vazia pra armazenar o retorno das validações
		$callbackObj = new \stdClass;
		$callbackObj->user = null;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		$userCad = array(
			'user_cad' => $user_id
		);

		$post = array_merge($post, $userCad);

		//Salva os dados no banco de dados
		$cadastrar = self::create($post);

		if ($cadastrar->is_valid()) {
			$callbackObj->user = $cadastrar;
			$callbackObj->status = true;
			return $callbackObj;
		}

		$errors = $cadastrar->errors->get_raw_errors();

		foreach ($errors as $field => $message) {
			array_push($callbackObj->errors, $message[0]);
		}

		return $callbackObj;
	}

	public static function atualizar($post, $emp_id)
	{
		//Cria uma classe vazia pra armazenar o retorno das validações
		$callbackObj = new \stdClass;
		$callbackObj->emp = null;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		$emp = self::find($emp_id);

		if ($emp->email != $post['email']) {
			$exists_mail = self::find_by_email($post['email']);
			if (!is_null($exists_mail) && intval($emp_id) !== intval($exists_mail->id)) {
				array_push($callbackObj->errors, 'Já existe uma empresa com este e-mail cadastrado. Por favor, escolha outro e tente novamente');
				return $callbackObj;
			}
		}

		if ($emp->cnpj !== $post['cnpj']) {
			$exists_cnpj = self::find_by_cnpj($post['cnpj']);
			if (!is_null($exists_cnpj) && intval($emp_id) !== intval($exists_cnpj->id)) {
				array_push($callbackObj->errors, 'Já existe uma empresa com este CNPJ cadastrado. Por favor, escolha outro e tente novamente');
				return $callbackObj;
			}
		}

		$emp->cnpj = $post['cnpj'];
		$emp->empresa = $post['empresa'];
		$emp->contato = $post['contato'];
		$emp->email = $post['email'];
		$emp->endereco = $post['endereco'];
		$emp->telefone = $post['telefone'];
		$emp->celular = $post['celular'];
		$emp->multa = $post['multa'];
		$emp->juros = $post['juros'];

		$atualizar = $emp->save(false);

		if ($atualizar) {
			$callbackObj->emp = $emp;
			$callbackObj->status = true;
			return $callbackObj;
		}

		$errors = $cadastrar->errors->get_raw_errors();

		foreach ($errors as $field => $message) {
			array_push($callbackObj->errors, $message[0]);
		}

		return $callbackObj;
	}
/*
	public static function atualizarSenha($user, $newPassword)
	{
		$user = self::find_by_id($user->id);

		$password = \HXPHP\System\Tools::hashHX($newPassword);

		return $user->update_attributes($password);
	}

	public static function atualizar($user_id, array $post)
	{
		$callbackObj = new \stdClass;
		$callbackObj->user = null;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		if (isset($post['password']) && !empty($post['password'])) {
			$password = \HXPHP\System\Tools::hashHX($post['password']);
			$post = array_merge($post, $password);
		}

		$user = self::find($user_id);

		$user->name = $post['name'];

		$user->email = $post['email'];

		$user->username = $post['username'];

		if (isset($post['salt'])) {
			$user->password = $post['password'];
			$user->salt = $post['salt'];
		}

		$exists_mail = self::find_by_email($post['email']);

		if (!is_null($exists_mail) && intval($user_id) !== intval($exists_mail->id)) {
			array_push($callbackObj->errors, 'Oops! Já existe um usuário com este e-mail cadastrado. Por favor, escolha outro e tente novamente');
			return $callbackObj;
		}

		$exists_username = self::find_by_username($post['username']);

		if (!is_null($exists_username) && intval($user_id) !== intval($exists_username->id)) {
			array_push($callbackObj->errors, 'Oops! Já existe um usuário com o login <strong>' . $post['username'] . '</strong> cadastrado. Por favor, escolha outro e tente novamente');
			return $callbackObj;
		}

		$atualizar = $user->save(false);

		if ($atualizar) {
			$callbackObj->user = $user;
			$callbackObj->status = true;
			return $callbackObj;
		}

		$errors = $cadastrar->errors->get_raw_errors();

		foreach ($errors as $field => $message) {
			array_push($callbackObj->errors, $message[0]);
		}

		return $callbackObj;
	}
	*/
}