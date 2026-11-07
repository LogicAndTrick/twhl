<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Translation\PotentiallyTranslatedString;

class StopForumSpam implements ValidationRule, DataAwareRule
{
    protected array $data = [];

    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Use the StopForumSpam API to try and stop spammers from creating an account
        $ip = request()->ip();
        $name = $this->data['name'] ?? null;
        $email = $this->data['email'] ?? null;
        $url = "https://api.stopforumspam.org/api";

        try {
            $json = Http::timeout(5)
                ->asForm()
                ->post($url, [
                    'ip' => $ip,
                    'username' => $name,
                    'email' => $email,
                    'confidence' => true,
                    'json' => true
                ])
                ->json();
        } catch (\Exception $e) {
            \Sentry\Laravel\Integration::captureUnhandledException($e);
            return; // allow if the api is down
        }

        // API's not working
        if (!$json || !isset($json['success'])) return;

        $keys = ['ip', 'username', 'email'];
        foreach ($keys as $key) {
            if (!isset($json[$key])) continue;

            $data = $json[$key];
            if (!is_array($data) || !isset($data['appears']) || !isset($data['confidence'])) continue;

            // If the API is more than 40% confident then block the user
            if ($data['appears'] === 1 && $data['confidence'] >= 40) {
                $fail('Registration was unsuccessful, try again later.'); // Why should they get any more detailed information?
                return;
            }
        }
    }
}
