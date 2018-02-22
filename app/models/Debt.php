<?php

/**
*
*/
class Debt extends \HXPHP\System\Model
{
	public static function cadastrar($post,$cliente_id,$user_id)
	{
		$callbackObj = new \stdClass;
		$callbackObj->divida = '';
		$callbackObj->status = false;
		$callbackObj->errors = array();

		$post_aux = array(
			'user_id' => $user_id,
			'cliente_id' => $cliente_id
			);

		$post = array_merge($post, $post_aux);

		$cadastrar = self::create($post);

		if ($cadastrar->is_valid()) {
			$callbackObj->divida = $cadastrar;
			$callbackObj->status = true;
			return $callbackObj;
		}

		$errors = $cadastrar->errors->get_raw_errors();

		foreach ($errors as $field => $message) {
			array_push($callbackObj->errors, $message[0]);
		}

		return $callbackObj;
	}

	public static function atualizar($post, $divida_id)
	{
		//Cria uma classe vazia pra armazenar o retorno das validações
		$callbackObj = new \stdClass;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		$divida = self::find($divida_id);

		$divida->valor = $post['valor'];
		$divida->vencimento = $post['vencimento'];
		$divida->status_id = $post['status_id'];
		$divida->obs = $post['obs'];

		$atualizar = $divida->save(false);

		if ($atualizar) {
			$callbackObj->status = true;
			return $callbackObj;
		}

		$errors = $cadastrar->errors->get_raw_errors();

		foreach ($errors as $field => $message) {
			array_push($callbackObj->errors, $message[0]);
		}

		return $callbackObj;
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
		$diasAtraso = Debt::diasAtraso($dividaVencimento);

		$vlAtualizado = (((($dividaValor/100)*$multa)+$dividaValor)/100)*(($juros/30)*$diasAtraso)+((($dividaValor/100)*$multa)+$dividaValor);

		return $vlAtualizado;
	}

	public static function atualizaStatus($divida_id, $status_new)
	{
		$divida = self::find($divida_id);
		$divida->status_id = $status_new;
		return $divida->save(false);
	}
}