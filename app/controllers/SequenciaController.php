<?php
class SequenciaController extends \HXPHP\System\Controller
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
			'seq' => Type::find('all')
		]);
	}

	public function cadastrarAction($post=null)
	{
		$post = $this->request->post();

		if (!empty($post)) {
			$cadSeq = Type::cadastrar($post);

			if ($cadSeq->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Não foi possível efetuar seu cadastro.<br />Verifique os erros abaixo:',
					$cadSeq->errors
				));
			}else{
				$this->load('Helpers\Alert', array(
					'success',
					'Sequencia cadastrada com sucesso!'
				));
				$this->view->setFile('index')
				->setVars([
					'seq' => Type::find('all')
				]);
			}
		}
	}

	public function excluirAction($seq_id)
	{
		if (is_numeric($seq_id)) {
			$seqValid = Types_debt::find('all', array('conditions' => array('tipo_id = ?', $seq_id)));
			if (count($seqValid) > 0) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Essa sequencia está em uso e não pode ser excluida!<br />Para mais detalhes contate o administrador.'
				));

				$this->view->setFile('index')
				->setVars([
					'seq' => Type::find('all')
				]);
			}else{
				$seq = Type::find_by_id($seq_id);

				$seq->delete();

				$this->load('Helpers\Alert', array(
					'success',
					'Sequencia excluida com sucesso!'
				));

				$this->view->setFile('index')
				->setVars([
					'seq' => Type::find('all')
				]);
			}
		}
	}

	public function editarAction($status_id)
	{
		$this->view
		->setFile('index')
		->setVars([
			'status' => Type::find('all'),
			'seqForm' => Type::find_by_id($status_id)
		]);
	}

	public function atualizarAction($id_post=null)
	{
		$post = $this->request->post();

		if (!empty($post)) {

			$cadSeq = Type::atualizar($post, $id_post);

			if ($cadSeq->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Não foi possível atualizar.<br />Verifique os erros abaixo:',
					$cadSeq->errors
				));
			}else{
				$this->load('Helpers\Alert', array(
					'success',
					'Sequencia atualizado com sucesso!'
				));
				$this->view->setFile('index')
				->setVars([
					'status' => Type::find('all')
				]);
			}
		}
	}
}