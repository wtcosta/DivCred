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

		$this->auth->redirectCheck();
		$this->auth->roleCheck(array(
			'administrator'
		));

		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);

		$this->load(
			'Helpers\Menu',
			$this->request,
			$this->configs,
			$role->role
		);

		$this->view->setTitle('HXPHP - Administrativo')
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
		if (is_numeric($user_id)) {
			$user = User::find_by_id($user_id);

			if (!is_null($user)) {
				$user->delete();

				$this->view->setVar('users', User::all());
			}
		}
	}
}