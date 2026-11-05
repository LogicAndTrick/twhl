<?php namespace App\Models\Accounts;

class UserNotificationDetails extends UserNotification {

	protected $table = 'user_notification_details';
	protected $casts = [
		'is_unread' => 'boolean',
		'stat_hits' => 'integer',
	];
    protected $formats = [
        'stat_hits' => 'uint32',  
    ];
}
