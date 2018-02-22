<?php
class IndexController extends \HXPHP\System\Controller
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

		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);

		$_SESSION['user'] = $user;

		if ($role->role == 'empresa' || $role->role == 'cliente') {
			$this->redirectTo('divida', false, false);
		}else{
			$this->redirectTo('empresa', false, false);
		}
	}
}