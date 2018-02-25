<?php

class LogController extends \HXPHP\System\Controller
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
		->setTitle('Log de Alterações')
		->setVars([
			'users' => User::all()
		]);
	}

	public function FiltrarAction()
	{
		$user = $this->request->post();
		$userName = User::find_by_id($user);
		$data = array();
		$nome = array();
		$acao = array();

		$empresas_cad = Company::find('all', array('conditions' => array('user_id = ?', $user)));
		if (count($empresas_cad) > 0) {
			foreach ($empresas_cad as $empresa) {
				$data[] = date("d/m/Y H:m:s", strtotime($empresa->data_cad));
				$nome[] = $empresa->empresa;
				$acao[] = 1;
			}
		}

		$empresas_up = Company::find('all', array('conditions' => array('user_up = ?', $user)));
		if (count($empresas_up) > 0) {
			foreach ($empresas_up as $empresa) {
				$data[] = date("d/m/Y H:m:s", strtotime($empresa->data_up));
				$nome[] = $empresa->empresa;
				$acao[] = 2;
			}
		}

		$cliente_cad = Client::find('all', array('conditions' => array('user_id = ?', $user)));
		if (count($cliente_cad) > 0) {
			foreach ($cliente_cad as $cliente) {
				$data[] = date("d/m/Y H:m:s", strtotime($cliente->data_cad));
				$nome[] = $cliente->nome;
				$acao[] = 3;
			}
		}

		$cliente_up = Client::find('all', array('conditions' => array('user_up = ?', $user)));
		if (count($cliente_up) > 0) {
			foreach ($cliente_up as $cliente) {
				$data[] = date("d/m/Y H:m:s", strtotime($cliente->data_up));
				$nome[] = $cliente->nome;
				$acao[] = 4;
			}
		}

		$debt_cad = Debt::find('all', array('conditions' => array('user_id = ?', $user)));
		if (count($debt_cad) > 0) {
			foreach ($debt_cad as $debto) {
				$cliente = Client::find_by_id($debto->cliente_id);
				$data[] = date("d/m/Y H:m:s", strtotime($debto->data_cad));
				$nome[] = $cliente->nome;
				$acao[] = 5;
			}
		}

		$debt_up = Debt::find('all', array('conditions' => array('user_up = ?', $user)));
		if (count($debt_up) > 0) {
			foreach ($debt_up as $debto) {
				$cliente = Client::find_by_id($debto->cliente_id);
				$data[] = date("d/m/Y H:m:s", strtotime($debto->data_up));
				$nome[] = $cliente->nome;
				$acao[] = 6;
			}
		}

		$call_cad = Call::find('all', array('conditions' => array('user_id = ?', $user)));
		if (count($call_cad) > 0) {
			foreach ($call_cad as $call) {
				$cliente = Client::find_by_id($call->cliente_id);
				$data[] = date("d/m/Y H:m:s", strtotime($call->data_cad));
				$nome[] = $cliente->nome;
				$acao[] = 7;
			}
		}

		$call_up = Call::find('all', array('conditions' => array('user_up = ?', $user)));
		if (count($call_up) > 0) {
			foreach ($call_up as $call) {
				$cliente = Client::find_by_id($call->cliente_id);
				$data[] = date("d/m/Y H:m:s", strtotime($call->data_up));
				$nome[] = $cliente->nome;
				$acao[] = 8;
			}
		}

		//$dados = array('data' => $data, 'nome' => $nome, 'acao' => $acao );

		$dados = array();
		foreach ($data as $k => $value){
			$dados[$k] = array(
				'data' => $value,
				'cliente'   => $nome[$k],
				'acao'   => $acao[$k]
			);
		}


		$this->view
		->setFile('index')
		->setTitle('Log de Alterações')
		->setVars([
			'logs' => self::orderArray($dados, 'data'),
			'user' => $userName
		]);
	}

	function orderArray($array, $on, $order=SORT_DESC){
		$new_array = array();
		$sortable_array = array();

		if (count($array) > 0) {
			foreach ($array as $k => $v) {
				if (is_array($v)) {
					foreach ($v as $k2 => $v2) {
						if ($k2 == $on) {
							$sortable_array[$k] = $v2;
						}
					}
				} else {
					$sortable_array[$k] = $v;
				}
			}

			switch ($order) {
				case SORT_ASC:
				asort($sortable_array);
				break;
				case SORT_DESC:
				arsort($sortable_array);
				break;
			}

			foreach ($sortable_array as $k => $v) {
				$new_array[$k] = $array[$k];
			}
		}

		return $new_array;
	}

	public function acao($idAcao)
	{
		switch ($idAcao) {
			case '1':
			return 'Cadastro de empresa';
			break;
			case '2':
			return 'Edição de empresa';
			break;
			case '3':
			return 'Cadastro de cliente';
			break;
			case '4':
			return 'Edição de cliente';
			break;
			case '5':
			return 'Cadastro de dívida';
			break;
			case '6':
			return 'Edição de dívida';
			break;
			case '7':
			return 'Cadastro de atendimento';
			break;
			default:
				return 'Ação não cadastrada!<br />Verifique com o Desenvolvedor.';
			break;
		}
	}
}