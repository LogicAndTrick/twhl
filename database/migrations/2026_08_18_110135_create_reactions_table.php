<?php

use App\Models\Reactions\ReactionType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reaction_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 50);
            $table->boolean('is_image');
            $table->string('value', 40);
            $table->integer('orderindex');

            $table->primary('id');
        });
        Schema::create('reactions', function (Blueprint $table) {
            $table->increments('id');
            $table->char('entity_type', 1);
            $table->unsignedInteger('entity_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('reaction_type_id');

            $table->primary('id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('reaction_type_id')->references('id')->on('reaction_types');
            $table->index(['entity_type', 'entity_id']);
        });
        ReactionType::Create([
            'name' => 'Thumbs up',
            'is_image' => false,
            'value' => '👍',
            'orderindex' => 0
        ]);
        ReactionType::Create([
            'name' => 'Thumbs down',
            'is_image' => false,
            'value' => '👎',
            'orderindex' => 1
        ]);
        ReactionType::Create([
            'name' => 'Happy',
            'is_image' => false,
            'value' => '🙂',
            'orderindex' => 2
        ]);
        ReactionType::Create([
            'name' => 'Sad',
            'is_image' => false,
            'value' => '🙁',
            'orderindex' => 3
        ]);
        ReactionType::Create([
            'name' => 'Heart',
            'is_image' => false,
            'value' => '❤️',
            'orderindex' => 4
        ]);
        ReactionType::Create([
            'name' => 'Lambda',
            'is_image' => true,
            'value' => 'lambda.png',
            'orderindex' => 10
        ]);
        ReactionType::Create([
            'name' => 'AAATRIGGER',
            'is_image' => true,
            'value' => 'gabe.png',
            'orderindex' => 11
        ]);
        ReactionType::Create([
            'name' => 'Pride',
            'is_image' => false,
            'value' => '🏳️‍🌈',
            'orderindex' => 20
        ]);
        ReactionType::Create([
            'name' => 'Trans rights',
            'is_image' => false,
            'value' => '🏳️‍⚧️',
            'orderindex' => 21
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('reactions');
        Schema::dropIfExists('reaction_types');
    }
};
