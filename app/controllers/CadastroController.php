<?php

class CadastroController extends \HXPHP\System\Controller
{
	public function __construct($configs)
	{
		parent::__construct($configs);

		$this->load(
			'Services\Auth',
			$configs->auth->after_login,
			$configs->auth->after_logout,
			true
		);

		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);

		$this->load(
			'Helpers\Menu',
			$this->request,
			$this->configs,
			$role->role
		);

		$this->view->setTitle('DIVCRED - Cadastro de novo usuário');
	}

	public function cadastrarAction()
	{
		//Redireciona para uma view
		$this->view->setFile('index');

		//Filtra/valida dados do form
		$this->request->setCustomFilters(array(
			'email' => FILTER_VALIDATE_EMAIL
		));

		$post = $this->request->post();

		//Verifica se o POST não está vazio e chama o model
		if (!empty($post)) {
			$cadastrarUsuario = User::cadastrar($post);

			if ($cadastrarUsuario->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Ops! Não foi possível efetuar seu cadastro.<br />Verifique os erros abaixo:',
					$cadastrarUsuario->errors
				));
			}else{
				$this->load('Helpers\Alert', array(
					'success',
					'Cadastro realizado com sucesso!'
				));
				$this->view
				->setPath('usuarios')
				->setFile('index')
				->setVars([
					'users' => User::all()
				]);
			}
		}
	}
}