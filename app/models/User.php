<?php

/**
*
*/
class User extends \HXPHP\System\Model
{
	static $belongs_to = array(
		array('role')
	);
	static $validates_presence_of = array(
		array(
			'name',
			'message' => 'O nome é um campo obrigatório.'
		),
		array(
			'email',
			'message' => 'O e-mail é um campo obrigatório.'
		),
		array(
			'username',
			'message' => 'O usuário é um campo obrigatório.'
		),
		array(
			'password',
			'message' => 'A senha é um campo obrigatório.'
		)
	);

	static $validates_uniqueness_of = array(
		array(
			'username',
			'message' => 'Já existe um usuário cadastrado!'
		),
		array(
			'email',
			'message' => 'Já existe um e-mail cadastrado!'
		)
	);

	public static function cadastrar($post)
	{
		//Cria uma classe vazia pra armazenar o retorno das validações
		$callbackObj = new \stdClass;
		$callbackObj->user = null;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		//Recupera o role_id de user
		$role = Role::find_by_role('User');
		if (is_null($role)) {
			array_push($callbackObj->errors, 'A role user não existe. Contato o administrator');
			return $callbackObj;
		}
		$user_data = array(
			'role_id' => $role->id,
			'status' => 1
		);

		//Cria a senha criptografada usando o HXPHP
		$password = \HXPHP\System\Tools::hashHX($post['password']);

		//Insere a senha criptografada e role no array do post
		$post = array_merge($post, $user_data, $password);

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

	public function login($post)
	{
		//Cria uma classe vazia pra armazenar o retorno das validações
		$callbackObj = new \stdClass;
		$callbackObj->user = null;
		$callbackObj->status = false;
		$callbackObj->code = array();
		$callbackObj->tentativas_restantes = null;

		$user = self::find_by_username($post['username']);

		if (!is_null($user)) {
			$password = \HXPHP\System\Tools::hashHX($post['password'], $user->salt);

			if ($user->status === 1) {
				if (LoginAttempt::ExistemTentativas($user->id)) {
					if ($password['password'] === $user->password) {
						$callbackObj->user = $user;
						$callbackObj->status = true;

						LoginAttempt::LimparTentativas($user->id);
					}else{
						if (LoginAttempt::TentativasRestantes($user->id) <= 3) {
							$callbackObj->code = 'tentativas-esgotando';
							$callbackObj->tentativas_restantes = LoginAttempt::TentativasRestantes($user->id);
						}else{
							$callbackObj->code = 'dados-incorretos';
						}
						LoginAttempt::RegistrarTentativas($user->id);
					}
				}else{
					$callbackObj->code = 'usuario-bloqueado';

					$user->status = 0;
					$user->save();
				}
			}else{
				$callbackObj->code = 'usuario-bloqueado';
			}
		}else{
			$callbackObj->code = 'usuario-inexistente';
		}

		return $callbackObj;
	}

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
}