<?php
class EmpresaController extends \HXPHP\System\Controller
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
			'administrator',
			'cobrança',
			'juridico'
		));

		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);

		if ($role->role == 'empresa' || $role->role == 'cliente') {
			$this->redirectTo('divida', false, false);
		}

		$this->load(
			'Helpers\Menu',
			$this->request,
			$this->configs,
			$role->role
		);

		$this->view->setTitle('DivCred - Assessoria Financeira & Cobrança')
		->setFile('index')
		->setVars([
			'empresa' => Company::all()
		]);
	}

	public function cadastrarAction()
	{
		//Redireciona para uma view
		$this->view->setFile('cadastrar');

		//Filtra/valida dados do form
		$this->request->setCustomFilters(array(
			'email' => FILTER_VALIDATE_EMAIL
		));

		$post = $this->request->post();

		//Verifica se o POST não está vazio e chama o model
		if (!empty($post) && $post['email'] !== null) {

			$user_id = $this->auth->getUserId();

			//Cadastra o user
			$cadUser = array(
				'name' => $post['empresa'],
				'email' => $post['email'],
				'username' => $post['cnpj'],
				'password' => $post['password'],
				'role_id' => 3
			);

			$cadUserData = User::cadastrar($cadUser);

			$insertUserEmpresa = array('idUserEmpresa' => $cadUserData->user->id);

			$post = array_merge($post, $insertUserEmpresa);

			unset($post['password']);

			$cadEmpresa = Company::cadastrar($post, $user_id);

			if ($cadEmpresa->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Não foi possível efetuar seu cadastro.<br />Verifique os erros abaixo:',
					$cadEmpresa->errors
				));
			}else{
				$this->load('Helpers\Alert', array(
					'success',
					'Empresa cadastrada com sucesso!'
				));
				$this->view->setFile('index')
				->setVars([
					'empresa' => Company::all()
				]);
			}
		}
	}

	public function editarAction($empresa)
	{
		$this->view->setFile('cadastrar');

		$user_id = $this->auth->getUserId();

		$this->request->setCustomFilters(array(
			'email' => FILTER_VALIDATE_EMAIL
		));

		$post = $this->request->post();

		$users = User::all();
		$option2 = array();
		foreach ($users as $value) {
			$option2[$value->id] = $value->name;
		}

		$this->view->setTitle('DivCred - Editar Empresa')
		->setVars([
			'emp' => Company::find($empresa),
			'option2' => $option2
			]);

		$post = $this->request->post();

		if (!empty($post)) {
			$atualizaEmp = Company::atualizar($post, $empresa);

			if ($atualizaEmp->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Ops! Não foi possível atualizar o cadastro. <br> Verifique os erros abaixo:',
					$atualizaEmp->errors
				));
			}else{
				$this->load('Helpers\Alert', array(
					'success',
					'Empresa editada com sucesso!'
				));
				$this->view->setFile('index')
				->setVars([
					'empresa' => Company::all()
				]);
			}
		}
	}

	public function excluirAction($emp_id)
	{
		$this->auth->roleCheck(array(
			'administrator'
		));

		if (is_numeric($emp_id)) {
			$empresa = Company::find_by_id($emp_id);

			if (!is_null($empresa)) {
				$empresa->delete();

				$this->load('Helpers\Alert', array(
					'success',
					'Empresa excluida com sucesso!'
				));
				$this->view->setFile('index')
				->setVars([
					'empresa' => Company::all()
				]);
			}
		}
	}
}