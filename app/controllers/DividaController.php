<?php
class DividaController extends \HXPHP\System\Controller
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

		if ($role->role == 'empresa' || $role->role == 'cliente') {
			$file = $role->role;

			if ($file == 'empresa') {
				$empUser = Company::find('all',array('conditions' => array('idUserEmpresa = ?', $user_id)));
				$dividas = Debt::find('all',array('conditions' => array('empresa = ?', $empUser[0]->id)));
			}elseif ($file == 'cliente') {
				$dividas = Debt::all();
			}else{
				$dividas = '';
			}
		}else{
			$file = 'index';
			$dividas = Debt::all();
		}

		$this->view->setTitle('DivCred - Dívidas')
		->setFile($file)
		->setVars([
			'dividas' => $dividas
		]);
	}
	public function cadastrarAction($post=null)
	{
		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);

		if ($role->role == 'empresa') {
			$empresas = Company::find('all',array('conditions' => array('idUserEmpresa = ?', $user_id)));
		}else{
			$empresas = Company::all();
		}

		$option = array();
		foreach ($empresas as $value) {
			$option[$value->id] = $value->empresa;
		}

		$this->view->setTitle('DivCred - Cadastrar Dívidas')
		->setVars(['option' => $option])
		->setFile('cadastrar');

		$post = $this->request->post();

		if (!empty($post)) {

			$user_id = $this->auth->getUserId();
			$cadDivida = Debt::cadastrar($post, $user_id);

			if ($cadDivida->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Não foi possível efetuar seu cadastro.<br />Verifique os erros abaixo:',
					$cadDivida->errors
				));
			}else{
				$this->load('Helpers\Alert', array(
					'success',
					'Dívida cadastrada com sucesso!'
				));

				if ($role->role == 'empresa' || $role->role == 'cliente') {
					$file = $role->role;

					if ($file == 'empresa') {
						$empUser = Company::find('all',array('conditions' => array('idUserEmpresa = ?', $user_id)));
						$dividas = Debt::find('all',array('conditions' => array('empresa = ?', $empUser[0]->id)));
					}elseif ($file == 'cliente') {
						$dividas = Debt::all();
					}else{
						$dividas = '';
					}
				}else{
					$file = 'index';
					$dividas = Debt::all();
				}

				$this->view->setFile($file)
				->setVars([
					'dividas' => $dividas
				]);
			}
		}
	}

	public function editarAction($divida)
	{
		$this->view->setFile('cadastrar');

		$user_id = $this->auth->getUserId();

		$post = $this->request->post();

		$empresas = Company::all();
		$option = array();
		foreach ($empresas as $value) {
			$option[$value->id] = $value->empresa;
		}

		$this->view->setTitle('DivCred - Editar Dívida')
		->setVars([
			'divida' => Debt::find($divida),
			'option' => $option
		]);

		$post = $this->request->post();

		if (!empty($post)) {
			$atualizaDivida = Debt::atualizar($post, $divida);

			if ($atualizaDivida->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Ops! Não foi possível atualizar o cadastro. <br> Verifique os erros abaixo:',
					$atualizaDivida->errors
				));
			}else{
				$this->load('Helpers\Alert', array(
					'success',
					'Dívida editada com sucesso!'
				));
				$this->view->setFile('index')
				->setVars([
					'dividas' => Debt::all()
				]);
			}
		}
	}

	public function excluirAction($divida_id)
	{
		if (is_numeric($divida_id)) {
			$divida = Debt::find_by_id($divida_id);

			if (!is_null($divida)) {
				$divida->delete();

				$this->load('Helpers\Alert', array(
					'success',
					'Dívida excluida com sucesso!'
				));
				$this->view->setFile('index')
				->setVars([
					'dividas' => Debt::all()
				]);
			}
		}
	}

	public function filtrarAction($empresa_id)
	{
		$empresa = Company::find_by_id($empresa_id);
		$this->view->setFile('index')
		->setVars(
			[
				'dividas' => Debt::find('all',array('conditions' => array('empresa = ?', $empresa_id))),
				'empresa' => $empresa
			]
		);
	}

	public function filtrarCpfAction($cpf)
	{
		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);

		$cpf = str_replace("aa", "/", $cpf);

		if ($role->role == 'empresa' || $role->role == 'cliente') {
			$file = $role->role;

			if ($file == 'empresa') {
				$empUser = Company::find('all',array('conditions' => array('idUserEmpresa = ?', $user_id)));
				$dividas = Debt::find('all',array('conditions' => array('empresa = ? AND cpf = ?', $empUser[0]->id, $cpf)));
			}elseif ($file == 'cliente') {
				$dividas = Debt::all();
			}else{
				$dividas = '';
			}
		}else{
			$file = 'cobranca';
			$dividas = Debt::find('all',array('conditions' => array('cpf = ?', $cpf)));
		}

		$this->view->setTitle('DivCred - Dívidas')
		->setFile($file)
		->setVars([
			'dividas' => $dividas
		]);
	}

	public function cadLogAction($cpf)
	{
		$post = $this->request->post();
		$divida = Debt::find(array('conditions' => array('cpf = ?', $cpf)));
		/*
		echo "<pre>";
		var_dump($post);
		echo "<hr />";
		var_dump($divida);
		echo "</pre>";
		*/
		if (!empty($post)) {

			$user_id = $this->auth->getUserId();

			//Verifica a necessidade de gerar boleto
			if ($post['tipo'] == 'geraBoleto') {
				$geraBoleto = true;
			}else{
				$geraBoleto = false;
			}

			//Retira campo tipo para inserir as informações no banco
			unset($post['tipo']);

			$cadLog = Call::cadastrar($post, $user_id, $cpf);
			/*
			//Gera boleto
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://www.asaas.com/api/v3/payments");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "{
				\"customer\": \"c44d226495dee483cdc7cf6dba6cc43db7bf092dcb9d2a3180515a2bba96b91a\",
				\"billingType\": \"BOLETO\",
				\"dueDate\": \"".date('Y-m-d')."\",
				\"value\": ".$post['vlNeg'].",
				\"description\": \"Cliente: ".$divida->nome."\",
				\"externalReference\": \"".$divida->cpf."\",
				\"remoteIp\": \"".$_SERVER['REMOTE_ADDR']."\"
			}");
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				"Content-Type: application/json",
				"access_token: c44d226495dee483cdc7cf6dba6cc43db7bf092dcb9d2a3180515a2bba96b91a"
			));
			$response = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);

			echo "<pre>";
			var_dump($httpCode);
			echo "<hr />";
			var_dump($response);
			echo "</pre>";
			*/
			if ($cadLog->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Não foi possível efetuar seu cadastro.<br />Verifique os erros abaixo:',
					$cadLog->errors
				));
			}else{
				$this->load('Helpers\Alert', array(
					'success',
					'Atendimento cadastrado com sucesso!'
				));

				$file = 'cobranca';
				$dividas = Debt::find('all',array('conditions' => array('cpf = ?', $cpf)));
				$this->view->setTitle('DivCred - Dívidas')
				->setFile($file)
				->setVars([
					'dividas' => $dividas
				]);
			}
		}
	}

	public static function diasAtraso($vencimento='')
	{
		$data_inicio = new DateTime($vencimento);
		$data_fim = new DateTime();

		$dateInterval = $data_inicio->diff($data_fim);
		return $dateInterval->days;
	}

	public static function vlAtualizado($dividaValor='', $dividaVencimento='', $multa='', $juros='')
	{
		$diasAtraso = DividaController::diasAtraso($dividaVencimento);

		$vlAtualizado = (((($dividaValor/100)*$multa)+$dividaValor)/100)*(($juros/30)*$diasAtraso)+((($dividaValor/100)*$multa)+$dividaValor);

		return $vlAtualizado;
	}
}