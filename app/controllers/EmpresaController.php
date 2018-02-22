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

		$user_id = $this->auth->getUserId();
		$user = User::find($user_id);
		$role = Role::find($user->role_id);

		if ($role->role == 'cobrança') {
			$this->redirectTo('cliente', false, false);
		}
		if ($role->role == 'cliente') {
			//$cliente = Client::find(array('conditions' => array('idUserCliente = ?', $user_id)));
			$this->redirectTo('cliente', false, false);
		}
		if ($role->role == 'empresa') {
			//$empresa = Company::find(array('conditions' => array('idUserEmpresa = ?', $user_id)));
			$this->redirectTo('cliente', false, false);
		}

		$this->load(
			'Helpers\Menu',
			$this->request,
			$this->configs,
			$role->role
		);

		$this->view
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
		unset($post['documentos']);

		//Verifica se o POST não está vazio e chama o model
		if (!empty($post) && $post['email'] !== null) {

			$user_id = $this->auth->getUserId();

			$existeUser = User::find(array('conditions' => array('username = ?', $post['cnpj'])));

			if (!is_null($existeUser)) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Usário da empresa já está criado, verifique com o administrador!'
				));
				return;
			}else{
				//Cadastra o user
				$cadUser = array(
					'name' => $post['empresa'],
					'email' => $post['email'],
					'username' => $post['cnpj'],
					'password' => 'divcred',
					'role_id' => 3
				);

				$cadUserData = User::cadastrar($cadUser);

				if ($cadUserData->status == false) {
					$this->load('Helpers\Alert', array(
						'danger',
						'Não foi possível cadastrar o cliente.<br />Verifique os erros abaixo:',
						$cadUserData->errors
					));
					return;
				}
			}

			$insertUserEmpresa = array('idUserEmpresa' => $cadUserData->user->id);

			$post = array_merge($post, $insertUserEmpresa);

			$cadEmpresa = Company::cadastrar($post, $user_id, $cadUserData->user->id);

			Document::cadastrar($_POST['documentos'], $cadEmpresa->emp->id);

			if ($cadEmpresa->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Não foi possível efetuar seu cadastro.<br />Verifique os erros abaixo:',
					$cadEmpresa->errors
				));
			}else{
				if (isset($_FILES['contrato']) && !empty($_FILES['contrato']['tmp_name'])) {
					$uploadUserImage = new upload($_FILES['contrato']);
					if ($uploadUserImage->uploaded) {
						$image_name = md5(uniqid());
						$uploadUserImage->file_new_name_body = $image_name;
						$uploadUserImage->file_new_name_ext = 'png';
						$uploadUserImage->resize = true;
						$uploadUserImage->image_x = 500;
						$uploadUserImage->image_ratio_y = true;
						$dir_path = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'empresas' . DS . $cadEmpresa->emp->id . DS;
						$uploadUserImage->process($dir_path);
						if ($uploadUserImage->processed) {
							$uploadUserImage->clean();

							if (!is_null($cadEmpresa->emp->contrato)) {
								unlink($dir_path . $cadEmpresa->emp->contrato);
							}
							$cadEmpresa->emp->contrato = $image_name . '.png';
							$cadEmpresa->emp->save(false);
						}
						else {
							$this->load('Helpers\Alert', array(
								'error',
								'Oops! Não foi possível atualizar a sua imagem de perfil',
								$uploadUserImage->error
							));
						}
					}
				}
				if (isset($_FILES['contrato_social']) && !empty($_FILES['contrato_social']['tmp_name'])) {
					$uploadUserImage = new upload($_FILES['contrato_social']);
					if ($uploadUserImage->uploaded) {
						$image_name = md5(uniqid());
						$uploadUserImage->file_new_name_body = $image_name;
						$uploadUserImage->file_new_name_ext = 'png';
						$uploadUserImage->resize = true;
						$uploadUserImage->image_x = 500;
						$uploadUserImage->image_ratio_y = true;
						$dir_path = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'empresas' . DS . $cadEmpresa->emp->id . DS;
						$uploadUserImage->process($dir_path);
						if ($uploadUserImage->processed) {
							$uploadUserImage->clean();

							if (!is_null($cadEmpresa->emp->contrato_social)) {
								unlink($dir_path . $cadEmpresa->emp->contrato_social);
							}
							$cadEmpresa->emp->contrato_social = $image_name . '.png';
							$cadEmpresa->emp->save(false);
						}
						else {
							$this->load('Helpers\Alert', array(
								'error',
								'Oops! Não foi possível enviar o arquivo com contrato social',
								$uploadUserImage->error
							));
						}
					}
				}
				if (isset($_FILES['cartao_cnpj']) && !empty($_FILES['cartao_cnpj']['tmp_name'])) {
					$uploadUserImage = new upload($_FILES['cartao_cnpj']);
					if ($uploadUserImage->uploaded) {
						$image_name = md5(uniqid());
						$uploadUserImage->file_new_name_body = $image_name;
						$uploadUserImage->file_new_name_ext = 'png';
						$uploadUserImage->resize = true;
						$uploadUserImage->image_x = 500;
						$uploadUserImage->image_ratio_y = true;
						$dir_path = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'empresas' . DS . $cadEmpresa->emp->id . DS;
						$uploadUserImage->process($dir_path);
						if ($uploadUserImage->processed) {
							$uploadUserImage->clean();

							if (!is_null($cadEmpresa->emp->cartao_cnpj)) {
								unlink($dir_path . $cadEmpresa->emp->cartao_cnpj);
							}
							$cadEmpresa->emp->cartao_cnpj = $image_name . '.png';
							$cadEmpresa->emp->save(false);
						}
						else {
							$this->load('Helpers\Alert', array(
								'error',
								'Oops! Não foi possível enviar o arquivo com cartão CNPJ',
								$uploadUserImage->error
							));
						}
					}
				}
				if (isset($_FILES['comprovante_endereco']) && !empty($_FILES['comprovante_endereco']['tmp_name'])) {
					$uploadUserImage = new upload($_FILES['comprovante_endereco']);
					if ($uploadUserImage->uploaded) {
						$image_name = md5(uniqid());
						$uploadUserImage->file_new_name_body = $image_name;
						$uploadUserImage->file_new_name_ext = 'png';
						$uploadUserImage->resize = true;
						$uploadUserImage->image_x = 500;
						$uploadUserImage->image_ratio_y = true;
						$dir_path = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'empresas' . DS . $cadEmpresa->emp->id . DS;
						$uploadUserImage->process($dir_path);
						if ($uploadUserImage->processed) {
							$uploadUserImage->clean();

							if (!is_null($cadEmpresa->emp->comprovante_endereco)) {
								unlink($dir_path . $cadEmpresa->emp->comprovante_endereco);
							}
							$cadEmpresa->emp->comprovante_endereco = $image_name . '.png';
							$cadEmpresa->emp->save(false);
						}
						else {
							$this->load('Helpers\Alert', array(
								'error',
								'Oops! Não foi possível enviar o arquivo com o comprovante de endereço.',
								$uploadUserImage->error
							));
						}
					}
				}
				if (isset($_FILES['autorizacao_terceiro']) && !empty($_FILES['autorizacao_terceiro']['tmp_name'])) {
					$uploadUserImage = new upload($_FILES['autorizacao_terceiro']);
					if ($uploadUserImage->uploaded) {
						$image_name = md5(uniqid());
						$uploadUserImage->file_new_name_body = $image_name;
						$uploadUserImage->file_new_name_ext = 'png';
						$uploadUserImage->resize = true;
						$uploadUserImage->image_x = 500;
						$uploadUserImage->image_ratio_y = true;
						$dir_path = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'empresas' . DS . $cadEmpresa->emp->id . DS;
						$uploadUserImage->process($dir_path);
						if ($uploadUserImage->processed) {
							$uploadUserImage->clean();

							if (!is_null($cadEmpresa->emp->autorizacao_terceiro)) {
								unlink($dir_path . $cadEmpresa->emp->autorizacao_terceiro);
							}
							$cadEmpresa->emp->autorizacao_terceiro = $image_name . '.png';
							$cadEmpresa->emp->save(false);
						}
						else {
							$this->load('Helpers\Alert', array(
								'error',
								'Oops! Não foi possível enviar o arquivo de autorização da conta de terceiro.',
								$uploadUserImage->error
							));
						}
					}
				}

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

		$this->view
		->setVars([
			'emp' => Company::find($empresa),
			'option2' => $option2
		]);

		if (!empty($post)) {

			$atualizaEmp = Company::atualizar($post, $empresa);

			Document::cadastrar($_POST['documentos'], $atualizaEmp->emp->id);

			if ($atualizaEmp->status === false) {
				$this->load('Helpers\Alert', array(
					'danger',
					'Ops! Não foi possível atualizar o cadastro. <br> Verifique os erros abaixo:',
					$atualizaEmp->errors
				));
			}else{
				if (isset($_FILES['contrato']) && !empty($_FILES['contrato']['tmp_name'])) {
					$uploadUserImage = new upload($_FILES['contrato']);
					if ($uploadUserImage->uploaded) {
						$image_name = md5(uniqid());
						$uploadUserImage->file_new_name_body = $image_name;
						$uploadUserImage->file_new_name_ext = 'png';
						$uploadUserImage->resize = true;
						$uploadUserImage->image_x = 500;
						$uploadUserImage->image_ratio_y = true;
						$dir_path = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'empresas' . DS . $atualizaEmp->emp->id . DS;
						$uploadUserImage->process($dir_path);
						if ($uploadUserImage->processed) {
							$uploadUserImage->clean();

							if (!is_null($atualizaEmp->emp->contrato)) {
								unlink($dir_path . $atualizaEmp->emp->contrato);
							}
							$atualizaEmp->emp->contrato = $image_name . '.png';
							$atualizaEmp->emp->save(false);
						}
						else {
							$this->load('Helpers\Alert', array(
								'error',
								'Oops! Não foi possível atualizar a sua imagem de perfil',
								$uploadUserImage->error
							));
						}
					}
				}
				if (isset($_FILES['contrato_social']) && !empty($_FILES['contrato_social']['tmp_name'])) {
					$uploadUserImage = new upload($_FILES['contrato_social']);
					if ($uploadUserImage->uploaded) {
						$image_name = md5(uniqid());
						$uploadUserImage->file_new_name_body = $image_name;
						$uploadUserImage->file_new_name_ext = 'png';
						$uploadUserImage->resize = true;
						$uploadUserImage->image_x = 500;
						$uploadUserImage->image_ratio_y = true;
						$dir_path = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'empresas' . DS . $atualizaEmp->emp->id . DS;
						$uploadUserImage->process($dir_path);
						if ($uploadUserImage->processed) {
							$uploadUserImage->clean();

							if (!is_null($atualizaEmp->emp->contrato_social)) {
								unlink($dir_path . $atualizaEmp->emp->contrato_social);
							}
							$atualizaEmp->emp->contrato_social = $image_name . '.png';
							$atualizaEmp->emp->save(false);
						}
						else {
							$this->load('Helpers\Alert', array(
								'error',
								'Oops! Não foi possível enviar o arquivo com contrato social',
								$uploadUserImage->error
							));
						}
					}
				}
				if (isset($_FILES['cartao_cnpj']) && !empty($_FILES['cartao_cnpj']['tmp_name'])) {
					$uploadUserImage = new upload($_FILES['cartao_cnpj']);
					if ($uploadUserImage->uploaded) {
						$image_name = md5(uniqid());
						$uploadUserImage->file_new_name_body = $image_name;
						$uploadUserImage->file_new_name_ext = 'png';
						$uploadUserImage->resize = true;
						$uploadUserImage->image_x = 500;
						$uploadUserImage->image_ratio_y = true;
						$dir_path = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'empresas' . DS . $atualizaEmp->emp->id . DS;
						$uploadUserImage->process($dir_path);
						if ($uploadUserImage->processed) {
							$uploadUserImage->clean();

							if (!is_null($atualizaEmp->emp->cartao_cnpj)) {
								unlink($dir_path . $atualizaEmp->emp->cartao_cnpj);
							}
							$atualizaEmp->emp->cartao_cnpj = $image_name . '.png';
							$atualizaEmp->emp->save(false);
						}
						else {
							$this->load('Helpers\Alert', array(
								'error',
								'Oops! Não foi possível enviar o arquivo com cartão CNPJ',
								$uploadUserImage->error
							));
						}
					}
				}
				if (isset($_FILES['comprovante_endereco']) && !empty($_FILES['comprovante_endereco']['tmp_name'])) {
					$uploadUserImage = new upload($_FILES['comprovante_endereco']);
					if ($uploadUserImage->uploaded) {
						$image_name = md5(uniqid());
						$uploadUserImage->file_new_name_body = $image_name;
						$uploadUserImage->file_new_name_ext = 'png';
						$uploadUserImage->resize = true;
						$uploadUserImage->image_x = 500;
						$uploadUserImage->image_ratio_y = true;
						$dir_path = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'empresas' . DS . $atualizaEmp->emp->id . DS;
						$uploadUserImage->process($dir_path);
						if ($uploadUserImage->processed) {
							$uploadUserImage->clean();

							if (!is_null($atualizaEmp->emp->comprovante_endereco)) {
								unlink($dir_path . $atualizaEmp->emp->comprovante_endereco);
							}
							$atualizaEmp->emp->comprovante_endereco = $image_name . '.png';
							$atualizaEmp->emp->save(false);
						}
						else {
							$this->load('Helpers\Alert', array(
								'error',
								'Oops! Não foi possível enviar o arquivo com o comprovante de endereço.',
								$uploadUserImage->error
							));
						}
					}
				}
				if (isset($_FILES['autorizacao_terceiro']) && !empty($_FILES['autorizacao_terceiro']['tmp_name'])) {
					$uploadUserImage = new upload($_FILES['autorizacao_terceiro']);
					if ($uploadUserImage->uploaded) {
						$image_name = md5(uniqid());
						$uploadUserImage->file_new_name_body = $image_name;
						$uploadUserImage->file_new_name_ext = 'png';
						$uploadUserImage->resize = true;
						$uploadUserImage->image_x = 500;
						$uploadUserImage->image_ratio_y = true;
						$dir_path = ROOT_PATH . DS . 'public' . DS . 'uploads' . DS . 'empresas' . DS . $atualizaEmp->emp->id . DS;
						$uploadUserImage->process($dir_path);
						if ($uploadUserImage->processed) {
							$uploadUserImage->clean();

							if (!is_null($atualizaEmp->emp->autorizacao_terceiro)) {
								unlink($dir_path . $atualizaEmp->emp->autorizacao_terceiro);
							}
							$atualizaEmp->emp->autorizacao_terceiro = $image_name . '.png';
							$atualizaEmp->emp->save(false);
						}
						else {
							$this->load('Helpers\Alert', array(
								'error',
								'Oops! Não foi possível enviar o arquivo de autorização da conta de terceiro.',
								$uploadUserImage->error
							));
						}
					}
				}

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