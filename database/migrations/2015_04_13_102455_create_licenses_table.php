<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLicensesTable extends Migration {

	public function up()
	{
		Schema::create('licenses', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('name');
            $table->text('description');
            $table->integer('orderindex');
		});

        $licenses = [
            [ 'All Rights Reserved','You must contact the author for permission to use this content in your own work.',1                                                                                                                                                                                                                                   ],
            [ 'CC BY','This content is licensed under the Creative Commons BY License. You must give credit to the author if you use it in your own work. http://creativecommons.org/licenses/by/4.0',2                                                                                                                                                    ],
            [ 'CC BY-SA','This content is licensed under the Creative Commons BY-SA License. You must give credit to the author if you use it in your own work. If you modify the content, you must publish your modifications using the same license. http://creativecommons.org/licenses/by-sa/4.0',3                                                    ],
            [ 'CC BY-ND','This content is licensed under the Creative Commons BY-ND License. You must give credit to the author if you use it in your own work. You cannot modify it in any way. http://creativecommons.org/licenses/by-nd/4.0',4                                                                                                          ],
            [ 'CC BY-NC','This content is licensed under the Creative Commons BY-NC License. You must give credit to the author if you use it in your own work. You cannot use it in commercial projects. http://creativecommons.org/licenses/by-nc/4.0',5                                                                                                 ],
            [ 'CC BY-NC-SA','This content is licensed under the Creative Commons BY-NC-SA License. You must give credit to the author if you use it in your own work. If you modify the content, you must publish your modifications using the same license. You cannot use it in commercial projects. http://creativecommons.org/licenses/by-nc-sa/4.0',6 ],
            [ 'CC BY-NC-ND','This content is licensed under the Creative Commons BY-NC-ND License. You must give credit to the author if you use it in your own work. You cannot modify it in any way. You cannot use it in commercial projects. http://creativecommons.org/licenses/by-nc-nd/4.0',7                                                       ],
        ];

        foreach ($licenses as $lic) {
            \App\Models\License::Create([
                'name' => $lic[0],
                'description' => $lic[1],
                'orderindex' => $lic[2]
            ]);
        }
	}

	public function down()
	{
		Schema::drop('licenses');
	}

}
