<?php
/**
*
*/
class LoginAttempt extends \HXPHP\System\Model
{
	public function TotalDeTentativas($user_id)
	{
		return count(self::find_all_by_user_id($user_id));
	}

	public function TentativasRestantes($user_id)
	{
		return intval(5-self::TotalDeTentativas($user_id));
	}

	public function RegistrarTentativas($user_id)
	{
		self::create(array(
			'user_id' => $user_id
		));
	}

	public function LimparTentativas($user_id)
	{
		self::delete_all(array(
			'conditions' => array(
				'user_id = ?',
				$user_id
			)
		));
	}

	public function ExistemTentativas($user_id)
	{
		return self::TotalDeTentativas($user_id) < 5 ? true : false;
	}
}