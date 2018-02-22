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
			'status' => State::find('all', array('order' => 'tipo ASC'))
		]);
	}
	public function cadastrarAction($post=null)
	{
		$post = $this->request->post();

		if (!empty($post)) {

			if ($post['tipo'] == 2 && $post['relacionamento'] == 0) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Para cada status de atendimento é necessário informar<br />a qual status de dívida ele pertence'
				));
				$this->view->setFile('index')
				->setVars([
					'status' => State::find('all', array('order' => 'tipo ASC'))
				]);
			}else{
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
						'status' => State::find('all', array('order' => 'tipo ASC'))
					]);
				}
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
				'status' => State::find('all', array('order' => 'tipo ASC'))
			]);
		}
	}

	public function editarAction($status_id)
	{
		$this->view
		->setFile('index')
		->setVars([
			'status' => State::find('all', array('order' => 'tipo ASC')),
			'statusForm' => State::find_by_id($status_id)
		]);
	}

	public function atualizarAction($id_post=null)
	{
		$post = $this->request->post();

		if (!empty($post)) {

			$cadStatus = State::atualizar($post, $id_post);

			if ($cadStatus->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Não foi possível atualizar.<br />Verifique os erros abaixo:',
					$cadStatus->errors
				));
			}else{
				$this->load('Helpers\Alert', array(
					'success',
					'Status atualizado com sucesso!'
				));
				$this->view->setFile('index')
				->setVars([
					'status' => State::find('all', array('order' => 'tipo ASC'))
				]);
			}
		}
	}
}