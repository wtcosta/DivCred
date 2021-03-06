<?php
class UsuariosController extends \HXPHP\System\Controller
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

		$this->view
		->setFile('index')
		->setVars([
			'user' => $user,
			'users' => User::all()
		]);
	}

	public function bloquearAction($user_id)
	{
		if (is_numeric($user_id)) {
			$user = User::find_by_id($user_id);

			if (!is_null($user)) {
				$user->status = 0;
				$user->save();

				$this->view->setVar('users', User::all());
			}
		}
	}

	public function desbloquearAction($user_id)
	{
		if (is_numeric($user_id)) {
			$user = User::find_by_id($user_id);

			if (!is_null($user)) {
				$user->status = 1;
				$user->save();

				$this->view->setVar('users', User::all());
			}
		}
	}

	public function excluirAction($user_id)
	{
		$empresa = Company::find(array('conditions' => array('idUserEmpresa = ?', $user_id)));
		if (is_numeric($user_id) && @!$empresa->id) {
			$user = User::find_by_id($user_id);
			if (!is_null($user)) {
				$this->load('Helpers\Alert', array(
					'success',
					'Usuário excluido com sucesso!'
				));
				$user->delete();
				$this->view->setVar('users', User::all());
			}
		}else{
			$this->load('Helpers\Alert', array(
				'danger',
				'Usário não pode ser excluido!<br />Verifique se ele não está associado a uma empresa.'
			));
			$this->view->setVar('users', User::all());
		}
	}
}