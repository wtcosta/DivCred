<?php

class RecuperarController extends \HXPHP\System\Controller
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

		$this->auth->redirectCheck(true);

		//Altera o titulo da views
		$this->view->setTitle('SistemaHx - Altere sua senha');

		//Carrega o modulo de mensagens
		$this->load('Modules\Messages', 'password-recovery');
		$this->messages->setBlock('alerts');
	}

	public function solicitarAction()
	{
		$this->view->setFile('index');

		//Filtra se o campo email enviado no form é um email valido, caso contrario insere null na variavel
		$this->request->setCustomFilters(array(
			'email' => FILTER_VALIDATE_EMAIL
		));

		//Recupera o campo email enviado pelo form
		$email = $this->request->post('email');

		//Cria uma variavel para armazenar as mensagens
		$error = null;

		if (!is_null($email) && $email !== false) {
			$validar = Recovery::validar($email);

			if ($validar->status === false) {
				$error = $this->messages->getByCode($validar->code);
			}else{
				$this->load(
					'Services\PasswordRecovery',
					$this->configs->site->url . $this->configs->baseURI . 'recuperar/redefinir/'
				);

				Recovery::create(array(
					'user_id' => $validar->user->id,
					'token' => $this->passwordrecovery->token,
					'status' => 0
				));

				$message = $this->messages->messages->getByCode('link-enviado', array(
					'message' => array(
						$validar->user->name,
						$this->passwordrecovery->link,
						$this->passwordrecovery->link
					)
				));

				$this->load('Services\Email');

				$envioDoEmail = $this->email->send(
					$validar->user->email,
					'WtSystem' . $message['subject'],
					$message['message'] . 'WtSystem',
					array(
						'email' => $this->configs->mail->from_mail,
						'remetente' => $this->configs->mail->from
					)
				);

				var_dump($message);

				if ($envioDoEmail === false) {
					$error = $this->messages->getByCode('email-nao-enviado');
				}
			}
		}

		if (!is_null($error)) {
			//Carrega o erro se existir
			$this->load('Helpers\Alert', $error);
		}else{
			//Caso não exista erro carrega msn de sucesso
			$success = $this->messages->getByCode('link-enviado');

			$this->view->setFile('blank');

			$this->load('Helpers\Alert', $success);
		}
	}

	public function redefinirAction($token)
	{
		$validarToken = Recovery::validarToken($token);

		$error = null;

		if ($validarToken->status === false) {
			$error = $this->messages->getByCode($validarToken->code);
		}else{
			//Se token validado passa o token via variaveu para view
			$this->view->setVar('token', $token);
		}

		if (!is_null($error)) {
			$this->view->setFile('blank');
			$this->load('Helpers\Alert', $error);
		}

	}

	public function alterarSenhaAction($token)
	{
		$this->view->setFile('redefinir');

		$validarToken = Recovery::validarToken($token);

		$error = null;

		if ($validarToken->status === false) {
			$this->view->setFile('blank');
			$error = $this->messages->getByCode($validarToken->code);
		}else{
			$this->view->setVar('token', $token);

			//Se o token validado trata o recebimento do form
			$password = $this->request->post('password');

			if (!is_null($password)) {
				$atualizarSenha = User::atualizarSenha($validarToken->user, $password);

				if ($atualizarSenha === true) {
					//Limpar o token do bd
					Recovery::limpar($validarToken->user->id);

					//Mostra uma view em putra pasta
					$this->view->setPath('login')
					->setFile('index');

					//Mostra msn de sucesso
					$success = $this->messages->getByCode('senha-redefinida');
					$this->load('Helpers\Alert', $success);
				}
			}else{
				//Validar se a senha não foi informada
			}
		}

		if (!is_null($error)) {
			$this->load('Helpers\Alert', $error);
		}
	}
}