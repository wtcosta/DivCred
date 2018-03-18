<?php

/**
*
*/
class Types_debt extends \HXPHP\System\Model
{
	public static function cadastrar($divida,$tipo)
	{
		$callbackObj = new \stdClass;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		$post = array(
			'divida_id' => $divida,
			'tipo_id' => $tipo
		);

		$cadastrar = self::create($post);

		if ($cadastrar->is_valid()) {
			$callbackObj->status = true;
			return $callbackObj;
		}

		$errors = $cadastrar->errors->get_raw_errors();

		foreach ($errors as $field => $message) {
			array_push($callbackObj->errors, $message[0]);
		}

		return $callbackObj;
	}

	public static function atualizar($divida,$tipos)
	{
		$callbackObj = new \stdClass;
		$callbackObj->status = false;
		$callbackObj->errors = array();

		$tiposAnt = Types_debt::find('all', array('conditions' => array('divida_id = ?', $divida)));

		foreach ($tiposAnt as $tipoAnt) {
			$type = Types_debt::find_by_id($tipoAnt->id);
			$type->delete();
		}

		foreach ($tipos as $tipo) {
			$post = array(
				'divida_id' => $divida,
				'tipo_id' => $tipo
			);

			$cadastrar = self::create($post);

			if (!$cadastrar->is_valid()) {
				$callbackObj->status = false;

				$errors = $cadastrar->errors->get_raw_errors();

				foreach ($errors as $field => $message) {
					array_push($callbackObj->errors, $message[0]);
				}

				return $callbackObj;
			}


		}

		return $callbackObj;
	}

	public static function lista($divida)
	{
		$tipos = Types_debt::find('all', array('conditions' => array('divida_id = ?', $divida)));

		$html = '<div class="list-group" style="margin:0">';
		foreach ($tipos as $tipo) {
			$type = Type::find_by_id($tipo->tipo_id);
			$html .= '<a class="list-group-item list-group-item-action list-group-item-info">'.$type->tipo.'</a>';
		}
		$html .= '</div>';

		return $html;
	}

	public static function listaArray($divida)
	{
		$tipos = Types_debt::find('all', array('conditions' => array('divida_id = ?', $divida)));

		$html = array();
		foreach ($tipos as $tipo) {
			$type = Type::find_by_id($tipo->tipo_id);
			$html[] = $type->id;
		}

		return $html;
	}
}