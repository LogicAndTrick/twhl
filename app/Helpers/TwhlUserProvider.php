<?php namespace App\Helpers;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class TwhlUserProvider extends EloquentUserProvider {


    /**
   	 * Retrieve a user by the given credentials.
   	 *
   	 * @param  array  $credentials
   	 * @return \Illuminate\Contracts\Auth\Authenticatable|null
   	 */
   	public function retrieveByCredentials(array $credentials)
   	{
		if (empty($credentials)) {
			return null;
		}
        // Allow username as an acceptable login key - try it first, otherwise fall back to email address
        $query = $this->createModel()->newQuery();

   		foreach ($credentials as $key => $value)
   		{
            if ($key == 'email') $key = 'name';
   			if ( ! str_contains($key, 'password')) $query->where($key, $value);
   		}

   		$first = $query->first();
        return $first ? $first : parent::retrieveByCredentials($credentials);
   	}

    /**
   	 * Validate a user against the given credentials.
   	 *
   	 * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
   	 * @param  array  $credentials
   	 * @return bool
   	 */
   	public function validateCredentials(UserContract $user, array $credentials)
   	{
   		$plain = $credentials['password'];

        // Classic TWHL password conversion
        // Legacy password: 20 characters max, md5 hashed
        if ($user->legacy_password) {
            $legacy_plain = $plain;
            if (strlen($legacy_plain) > 20) $legacy_plain = substr($legacy_plain, 0, 20);
            $legacy_pass = md5(strtolower(trim($legacy_plain)));
            if ($user->legacy_password == $legacy_pass) {
				// Users with a legacy password won't be able to do anything until they reset their password.
                return true;
            }
        }

   		return $this->hasher->check($plain, $user->getAuthPassword());
   	}
}