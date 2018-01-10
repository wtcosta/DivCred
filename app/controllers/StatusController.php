<?php
class StatusController extends \HXPHP\System\Controller
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

		$this->view->setTitle('DivCred - Status')
		->setFile('index')
		->setVars([
			'status' => State::find('all', array('order' => 'tipo ASC'))
		]);
	}
	public function cadastrarAction($post=null)
	{
		$post = $this->request->post();

		if (!empty($post)) {

			$user_id = $this->auth->getUserId();
			$cadStatus = State::cadastrar($post);

			if ($cadStatus->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Não foi possível efetuar seu cadastro.<br />Verifique os erros abaixo:',
					$cadStatus->errors
				));
			}else{
				$this->load('Helpers\Alert', array(
					'success',
					'Dívida cadastrada com sucesso!'
				));
				$this->view->setFile('index')
				->setVars([
					'status' => State::all()
				]);
			}
		}
	}

	public function excluirAction($status_id)
	{
		if (is_numeric($status_id)) {
			$status = State::find_by_id($status_id);

			$status->delete();

			$this->load('Helpers\Alert', array(
				'success',
				'Status excluido com sucesso!'
			));

			$this->view->setFile('index')
			->setVars([
				'status' => State::all()
			]);
		}
	}
}