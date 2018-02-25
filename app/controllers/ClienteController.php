<?php

class ClienteController extends \HXPHP\System\Controller
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

		if ($role->role == 'cliente') {
			$cliente = Client::find(array('conditions' => array('idUserCliente = ?', $user_id)));
			self::filtrarAction($cliente->id);
		}elseif ($role->role == 'empresa') {
			$empresa = Company::find(array('conditions' => array('idUserEmpresa = ?', $user_id)));
			self::empresaAction($empresa->id);
		}else{
			$this->view
			->setVars([
				'clientes' => Client::all()
			]);
		}
	}

	public function cadastrarAction($empresa='')
	{
		//Redireciona para uma view
		if ($empresa != '') {
			$this->view
			->setFile('cadastrar')
			->setVars([
				'empresa' => $empresa
			]);
		}else{
			$this->view->setFile('cadastrar');
		}

		//Filtra/valida dados do form
		$this->request->setCustomFilters(array(
			'email' => FILTER_VALIDATE_EMAIL
		));

		$post = $this->request->post();

		//Verifica se o POST não está vazio e chama o model
		if (!empty($post) && $post['cpf'] !== null) {

			$user_id = $this->auth->getUserId();

			$existeUser = User::find(array('conditions' => array('username = ?', $post['cpf'])));

			if (!is_null($existeUser)) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Usário do cliente já está criado, verifique com o administrador!'
				));
				return;
			}else{
				//Cadastra o user
				$cadUser = array(
					'name' => $post['nome'],
					'email' => $post['email'],
					'username' => $post['cpf'],
					'password' => 'divcred',
					'role_id' => 4
				);

				$cadUserData = User::cadastrar($cadUser);

				echo "<pre>";
				var_dump($post);
				echo "<hr />";
				var_dump($cadUserData);
				echo "</pre>";

				if ($cadUserData->status == false) {
					$this->load('Helpers\Alert', array(
						'danger',
						'Não foi possível cadastrar o cliente.<br />Verifique os erros abaixo:',
						$cadUserData->errors
					));
					return;
				}
			}

			$cadCliente = Client::cadastrar($post, $user_id, $cadUserData->user->id);

			if ($cadCliente->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Não foi possível efetuar seu cadastro.<br />Verifique os erros abaixo:',
					$cadCliente->errors
				));
				return;
			}else{
				$this->load('Helpers\Alert', array(
					'success',
					'Cliente cadastrada com sucesso!'
				));
				$this->view->setFile('cobranca')
				->setVars([
					'clientes' => Client::find_by_id($cadCliente->cliente->id)
				]);
			}
		}
	}

	public function editarAction($cliente)
	{
		$clienteData = Client::find_by_id($cliente);

		$this->view
		->setFile('cadastrar')
		->setVars([
			'cliente' => $clienteData,
			'idCliente' => $clienteData->idusercliente
		]);

		$user_id = $this->auth->getUserId();

		$this->request->setCustomFilters(array(
			'email' => FILTER_VALIDATE_EMAIL
		));

		$post = $this->request->post();

		if (!empty($post)) {
			$atualizaCliente = Client::atualizar($post, $cliente);

			if ($atualizaCliente->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Ops! Não foi possível atualizar o cadastro. <br> Verifique os erros abaixo:',
					$atualizaCliente->errors
				));
			}else{
				$this->load('Helpers\Alert', array(
					'success',
					'Cliente editado com sucesso!'
				));
				$this->view->setFile('index')
				->setVars([
					'clientes' => Client::all()
				]);
			}
		}
	}

	public function filtrarAction($cliente)
	{
		$this->view
		->setFile('cobranca')
		->setVars([
			'clientes' => Client::find_by_id($cliente)
		]);
	}

	public function empresaAction($empresa)
	{
		$this->view
		->setFile('empresa')
		->setVars([
			'clientes' => Client::find(
				'all',
				array(
					'conditions' => array('companies_id = ?', $empresa),
					'order' => 'data_cad desc'
				)
			),
			'empresa' => Company::find_by_id($empresa)
		]);
	}

	public function cadLogAction($cliente)
	{
		$post = $this->request->post();
		$tipo = $post['tipo'];
		unset($post['tipo']);
		unset($post['vencimento']);
		unset($post['valor']);

		if (!empty($post)) {

			$user_id = $this->auth->getUserId();

			$cadLog = Call::cadastrar($post, $user_id, $cliente);
			$msn = "";

			//Verifica a necessidade de gerar boleto
			if ($tipo == 'geraBoleto') {

				$clienteData = Client::find_by_id($cliente);

				if (!$clienteData->user_boleto) {
					$cadCliente = Payment::cad_cliente($clienteData);
					$idClienteBoleto = $cadCliente->id;
				}else{
					$idClienteBoleto = $clienteData->user_boleto;
				}

				$erroBoleto = true;
				foreach ($_POST['vencimento'] as $key => $value) {
					if ($value != '') {
						$dadosBoleto['user_boleto'] = $idClienteBoleto;
						$dadosBoleto['tipo'] = 'BOLETO';
						$dadosBoleto['vencimento'] = $value;
						$dadosBoleto['valor'] = $_POST['valor'][$key];
						$dadosBoleto['descricao'] = 'Boleto pagamento negociação DivCred';
						$dadosBoleto['id_atd'] = $cadLog->atd->id;
						$dadosBoleto['id_cli'] = $clienteData->id;
						$geraBoleto = Payment::gera_pagamento($dadosBoleto);

						if ($geraBoleto->status != true) {
							$erroBoleto = true;
						}
					}else{
						break;
					}
				}
				if ($erroBoleto == true) {
					$msn = "<br />Boletos enviado com sucesso!";
				}else{
					$msn = false;
					$this->load('Helpers\Alert', array(
						'danger',
						'Não foi possível enviar o pagamento.<br />Verifique os erros abaixo:',
						$geraBoleto->errors
					));
				}
			}

			if ($cadLog->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Não foi possível efetuar seu cadastro.<br />Verifique os erros abaixo:',
					$cadLog->errors
				));
			}else{
				$this->load('Helpers\Alert', array(
					'success',
					'Atendimento cadastrado com sucesso!'.$msn
				));

				$file = 'cobranca';
				$this->view
				->setFile($file)
				->setVars([
					'clientes' => Client::find_by_id($cliente)
				]);
			}
		}
	}

	public function cadastrarDividaAction($cliente_id)
	{
		$this->view
		->setFile('cobranca')
		->setVars([
			'clientes' => Client::find_by_id($cliente_id)
		]);

		$user_id = $this->auth->getUserId();
		$post = $this->request->post();

		if (!empty($post)) {
			$tipos = $_POST['tipo'];
			unset($post['tipo']);

			$cadastrarDivida = Debt::cadastrar($post, $cliente_id, $user_id);

			if (count($tipos) > 0) {
				foreach ($tipos as $tipo) {
					$tipoDivida = Types_debt::cadastrar($cadastrarDivida->divida->id, $tipo);
				}
			}

			if ($cadastrarDivida->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Ops! Não foi possível efetuar seu cadastro.<br />Verifique os erros abaixo:',
					$cadastrarDivida->errors
				));
			}else{
				$this->view
				->setFile('cobranca')
				->setVars([
					'clientes' => Client::find_by_id($cliente_id)
				]);
			}
		}
	}

	public function editarDividaAction($cliente_id, $divida_id)
	{
		$this->view
		->setFile('cobranca')
		->setVars([
			'clientes' => Client::find_by_id($cliente_id)
		]);

		$post = $this->request->post();

		if (!empty($post)) {
			if (isset($_POST['tipo'])) {
				$tipos = $_POST['tipo'];
				unset($post['tipo']);
			}else{
				$tipos = null;
			}

			$atualizarDivida = Debt::atualizar($post, $divida_id);

			if (count($tipos) > 0) {
				$tipoDivida = Types_debt::atualizar($divida_id, $tipos);
			}

			if ($atualizarDivida->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Ops! Não foi possível efetuar seu cadastro.<br />Verifique os erros abaixo:',
					$atualizarDivida->errors
				));
			}else{
				$this->view
				->setFile('cobranca')
				->setVars([
					'clientes' => Client::find_by_id($cliente_id)
				]);
			}
		}
	}
}