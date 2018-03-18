<?php
class Document extends \HXPHP\System\Model
{
	public static function cadastrar($documentos, $empresa_id)
	{
		$docs = self::find(array('conditions' => array('companies_id = ?', $empresa_id)));
		if (@!$docs->id) {
			self::criar($empresa_id);
			$docs = self::find(array('conditions' => array('companies_id = ?', $empresa_id)));
			self::editar($docs, $documentos);
		}else{
			self::editar($docs, $documentos);
		}
	}

	public static function criar($empresa_id)
	{
		$insert = new Document();
		$insert->companies_id = $empresa_id;
		$insert->save();
	}

	public static function editar($docs, $atributos)
	{
		self::limpar($docs);
		$post = self::find($docs->id);
		foreach ($atributos as $value) {
			$post->update_attributes(array($value => 1));
		}
	}

	public static function limpar($docs)
	{
		$post = self::find($docs->id);
		$post->contrato_assinado = 0;
		$post->contrato_social = 0;
		$post->comprovante_endereco = 0;
		$post->conta_bancaria = 0;
		$post->procuracao = 0;
		$atualizar = $post->save(false);
	}

	public static function printDoc($doc)
	{
		$retorno = "";
		if ($doc->contrato_assinado == 0) {
			$retorno .= '<li class="list-group-item">Contrato assinad</li>';
		}
		if ($doc->contrato_social == 0) {
			$retorno .= '<li class="list-group-item">Contrato social</li>';
		}
		if ($doc->comprovante_endereco == 0) {
			$retorno .= '<li class="list-group-item">Comnprovante de endereço</li>';
		}
		if ($doc->conta_bancaria == 0) {
			$retorno .= '<li class="list-group-item">Conta bancária</li>';
		}
		if ($doc->procuracao == 0) {
			$retorno .= '<li class="list-group-item">Procuração</li>';
		}

		return $retorno;
	}
}