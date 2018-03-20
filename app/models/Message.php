<?php

class Message extends \HXPHP\System\Model
{
	private static function config($tipo)
	{
		switch ($tipo) {
			case 'url':
			return 'http://54.173.24.177/painel/api.ashx?';
			break;
			case 'lgn':
			return urlencode('11940074794');
			break;
			case 'pwd':
			return urlencode('725592');
			break;
			case 'msn1':
			return urlencode('mensagem teste 1');
			break;
			case 'msn2':
			return urlencode('mensagem teste 1');
			break;
		}
	}

	public static function cad_cliente($cliente)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, self::config('url')."/customers");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);

		curl_setopt($ch, CURLOPT_POST, TRUE);

		curl_setopt($ch, CURLOPT_POSTFIELDS, "{
			\"name\": \"".$cliente->nome."\",
			\"email\": \"".$cliente->email."\",
			\"phone\": \"\",
			\"mobilePhone\": \"".$cliente->celular."\",
			\"cpfCnpj\": \"".$cliente->cpf."\",
			\"postalCode\": \"\",
			\"address\": \"\",
			\"addressNumber\": \"\",
			\"complement\": \"\",
			\"province\": \"\",
			\"externalReference\": \"".$cliente->id."\",
			\"notificationDisabled\": false,
			\"additionalEmails\": \"\"
		}");

		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type: ".self::config('type'),
			"access_token: ".self::config('token')
		));

		$response = curl_exec($ch);
		curl_close($ch);

		$retorno = json_decode($response);

		if (isset($retorno->erros)) {
			return $retorno->errors[0]->description;
		}else{
			Client::user_boleto($retorno->id, $cliente->id);
		}

		return $retorno;
	}

	public static function busca_cliente($nome)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, self::config('url')."/customers?name=".$nome);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);

		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"access_token: ".self::config('token')
		));

		$response = curl_exec($ch);
		curl_close($ch);

		return json_decode($response);
	}

	public static function gera_pagamento($dados)
	{
		$callbackObj = new \stdClass;
		$callbackObj->boleto = false;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::config('url')."/payments");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "{
			\"customer\": \"".$dados['user_boleto']."\",
			\"billingType\": \"".$dados['tipo']."\",
			\"dueDate\": \"".$dados['vencimento']."\",
			\"value\": ".$dados['valor'].",
			\"description\": \"".$dados['descricao']."\",
			\"externalReference\": \"".$dados['id_atd']."\",
			\"remoteIp\": \"".$_SERVER['REMOTE_ADDR']."\"
		}");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type: ".self::config('type'),
			"access_token: ".self::config('token')
		));
		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		$retorno = json_decode($response);

		if (isset($retorno->id)) {
			$cadBoleto['pagamento_id'] = $retorno->id;
			$cadBoleto['cliente_id'] = $dados['id_cli'];
			$cadBoleto['atendimento_id'] = $dados['id_atd'];
			$cadBoleto['data_criacao'] = $retorno->dateCreated;
			$cadBoleto['valor'] = $retorno->value;
			$cadBoleto['valor_liquido'] = $retorno->netValue;
			$cadBoleto['tipo'] = $retorno->billingType;
			$cadBoleto['status'] = $retorno->status;
			$cadBoleto['vencimento'] = $retorno->dueDate;
			$cadBoleto['pagamento'] = $retorno->paymentDate;
			$cadBoleto['fatura'] = $retorno->invoiceUrl;
			$cadBoleto['boleto'] = $retorno->bankSlipUrl;

			$cadBoletoRetorno = self::create($cadBoleto);

			if ($cadBoletoRetorno->is_valid()) {
				$callbackObj->boleto = $cadBoletoRetorno;
				$callbackObj->status = true;
				return $callbackObj;
			}

			$errors = $cadBoletoRetorno->errors->get_raw_errors();
			foreach ($errors as $field => $message) {
				array_push($callbackObj->errors, $message[0]);
			}
			return $callbackObj;
		}else{
			$callbackObj->errors = "Erro ao gerar o pagamento!";
			return $callbackObj;
		}
	}

	public static function status($status)
	{
		switch ($status) {
			case 'PENDING':
			return 'Pendente';
			break;
			case 'CONFIRMED':
			return 'Confirmado';
			break;
			case 'RECEIVED':
			return 'Recebido';
			break;
			case 'OVERDUE':
			return 'Atrasado';
			break;
			case 'REFUNDED':
			return 'Estornado';
			break;

			default:
			return 'Pendente';
			break;
		}
	}
}