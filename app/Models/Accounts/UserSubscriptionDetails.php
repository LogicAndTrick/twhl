<?php namespace App\Models\Accounts;

class UserSubscriptionDetails extends UserSubscription {

	protected $table = 'user_subscription_details';
	protected $casts = [
		'is_own_article' => 'boolean',
		'send_email' => 'boolean',
		'send_push_notification' => 'boolean',
	];
}
