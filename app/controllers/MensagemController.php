<?php

class MensagemController extends \HXPHP\System\Controller
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
		->setVars([
			'mensagens' => Message::all()
		]);
	}

	public function retornoAction(){
		/*
		* Exemplo retorno.aspx, todos os parâmetros são passados via methodo POST
		*
		* Parâmetro de retorno enviado via método POST
		*
		* data_callback = Conteúdo das mensagens via método POST
		*
		* Parâmetros contidos no em data_callback
		*
		* codigo_campanha;celular;resposta;data_resposta;status;interno_key
		*
		* [0] codigo_campanha = Código da campanha retornado pela função sendsms (Tipo de dados Inteiro)
		* [1] celular = Número do celular de retonro (Tipo de dados String)
		* [2] resposta = Mensagem SMS retornada (Tipo de Dados String)
		* [3] data_resposta = Data de recebimento da mensagem (Tipo de Dados String - Formato: yyyy-MM-dd HH:mm:ss)
		* [4] status = Status da entrega (Tipo de Dados String)
		* [5] interno_key = Código de referencia passado pela função sendsms (Tipo de Dados String)
		*/

		try{
			if (isset($_REQUEST['data_callback']))
			{
				$linhas =  preg_split( '/\r\n|\r|\n/', $_REQUEST['data_callback']);

				$content = '';

				foreach ($linhas as $linha)
				{
					if ($linha != '')
					{
						$conteudo = explode(';', $linha);

						if (count($conteudo) == 6)
						{
							$codigo_campanha = $conteudo[0];
							$celular = $conteudo[1];
							$resposta = $conteudo[2];
							$data_resposta = $conteudo[3];
							$status = $conteudo[4];
							$interno_key = $conteudo[5];

							$content .= 'Código campanha: ' . $codigo_campanha . chr(13). chr(10) .
							'Celular: ' . $celular . chr(13). chr(10) .
							'Resposta: ' . $resposta . chr(13). chr(10) .
							'Data Resposta: ' . $data_resposta . chr(13). chr(10).
							'Status: ' . $status . chr(13). chr(10) .
							'Interno Key: ' . $interno_key . chr(13). chr(10) .chr(13). chr(10);
						}
					}
				}

				echo $content;
			}
		}
		catch (Exception $ex)
		{
			echo $ex->getMessage();
		}
	}
}