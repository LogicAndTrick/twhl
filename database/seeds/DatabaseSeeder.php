<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder {

	public function run()
	{
		Model::unguard();

        $this->call('DeleteEverythingSeeder');
        $this->call('UserTableSeeder');
        $this->call('ForumTableSeeder');
	}

}

class DeleteEverythingSeeder extends Seeder {

    public function run()
    {
        DB::table('forum_posts')->delete();
        DB::table('forum_threads')->delete();
        DB::table('forums')->delete();
        DB::table('users')->delete();

        DB::statement('alter table forum_posts AUTO_INCREMENT = 1');
        DB::statement('alter table forum_threads AUTO_INCREMENT = 1');
        DB::statement('alter table forums AUTO_INCREMENT = 1');
        DB::statement('alter table users AUTO_INCREMENT = 1');
    }
}

class UserTableSeeder extends Seeder {

    public function run()
    {
        \App\Models\Accounts\User::create([
            'name' => 'admin',
            'email' => 'admin@twhl.info',
            'password' => bcrypt('admin'),
        ]);
    }
}

class ForumTableSeeder extends Seeder {

    public function run()
    {
        $forum = \App\Models\Forums\Forum::create([
            'name' => 'General',
            'description' => 'This is the general forum'
        ]);

        $thread = new \App\Models\Forums\ForumThread([
            'user_id' => 1,
            'title' => 'This is a test thread'
        ]);

        $thread = $forum->threads()->save($thread);

        $post = new \App\Models\Forums\ForumPost([
            'forum_id' => $forum->id,
            'user_id' => 1,
            'content_text' => $this->get_post_text()
        ]);

        $post = $thread->posts()->save($post);

        $thread->last_post_id = $post->id;
        $thread->save();

        $forum->last_post_id = $post->id;
        $forum->save();
    }

    private function get_post_text() {
        return "[cat:Test]
[cat:Test2]

[[ToDo|CLICK HERE FOR THE TODO LIST]]
---

This is the main wiki page with some formatting.

= Header 1 =
== Header 2 ==
=== Header 3 ===
==== Header ~\&& 4 ====
===== 5 Header =====

* .
** ..
* .
** ..

[b]bold[/b][u]underline[/u][i]italic[/i][font size=22 color=red]large red font[/font]

[b]I'm trying to break the parser


by putting in extra lines.[/b]

Auto-linking URL: http://twhl.info
http://twhl.info
Auto-linking email: example@example.com
example@example.com
Non-auto-linking: [url]http://twhl.info[/url] [url=http://twhl.info]TWHL[/url]

[blue]blah[/blue]
[green]blah[/green]
[purple]blah[/purple]
[pre]blah[/pre]
[pre]blah blah
blah blah

blah        blah[/pre]

asd


Properly formed quotes:

[quote]asdf[/quote]
[quote=asdf][quote][quote=1234]asdf[/quote][/quote][/quote]
[quote]asdf[quote]asdf[/quote]asdf[quote]asdf[quote]asdf[/quote]asdf[/quote]asdf[quote]asdf[/quote]asdf[/quote]

Broken quotes:
[quote]asdf[/quote][/quote]
[quote]asdf[quote][quote]
[quote]asdf[/quote][quote]

Markdown quotes
> quote!
> same quote!

> new quote!
>> nested quote???
>>> more nesting? madness!
>> outside one level
>>>> woah what is this
>>> insanity

asd

:D :( :) :X O_O
:)123 123:) 123:)123

* bullet point
** [u]sub bullet 1[/u]
** sub bullet 2
*** sub-sub-bullet
# numbered point
## num numbered
##* bullet in a number
##*# number in a bullet in a number etc etc

Preformatted text

Still the same preformatted text

New preformatted text block

[[Test]]
[[Test|Test page]]
[['Test Page': With some invalid characters?|This one has some invalid characters and whatnot]]
[[Test#Bookmark]]

[[#Anchor]] Link to bookmark
[[#Anchor|Bookmark]] Link to same bookmark
[[##Anchor]] Definition of bookmark

[http://twhl.info]
[http://twhl.info|TWHL]

block-level image: [img]http://twhl.info/images/logo_final2.jpg[/img]

inline image: [simg]http://twhl.info/images/logo_final2.jpg[/simg]

link image: [url=http://twhl.info][img]http://twhl.info/images/logo_final2.jpg[/img][/url]

None of these pictures should be inline:
[img:twhl.png]
[img:twhl.png|Picture of TWHL]

[img:twhl.png|left|Picture of TWHL]
[img:twhl.png|right|Picture of TWHL]
[img:twhl.png|right|Picture of TWHL]
[img:twhl.png|right|Picture of TWHL]
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin non metus sit amet risus vestibulum porttitor eu eu dui. Sed consectetur consequat nibh, id ornare dui accumsan ac. Aliquam erat volutpat. Ut ornare magna sit amet metus malesuada pharetra. Mauris arcu urna, malesuada ac vehicula in, facilisis ut tellus. Donec velit libero, dignissim sed auctor at, bibendum eget nulla. Nullam tincidunt, lacus sodales volutpat suscipit, nisl lorem condimentum ante, sit amet varius nibh ligula sed dui. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Integer consectetur elementum commodo. Proin non metus id diam elementum imperdiet. Phasellus pellentesque aliquam nisl id consequat. In luctus luctus mattis.

[img:twhl.png|thumb|Picture of TWHL]
Integer elementum metus at purus pulvinar vehicula. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Aliquam quam nulla, rutrum at gravida sed, dictum in urna. Aliquam erat volutpat. Ut pulvinar elementum euismod. In eget tortor vitae lacus euismod lacinia. Fusce tempus dignissim urna sed mollis. Cras rhoncus mollis nulla sit amet vehicula. Donec eu viverra quam. Mauris tincidunt lacinia facilisis. Aliquam erat volutpat. Fusce enim tellus, ullamcorper sit amet semper eget, interdum eu leo. Nunc leo eros, euismod quis lacinia in, sodales at felis. Duis blandit odio vel libero sollicitudin ac semper purus lacinia. Pellentesque nisl metus, ultricies et adipiscing nec, dictum nec mauris. Vestibulum ultrices nisl id nulla pellentesque id vehicula risus malesuada.

[img:twhl.png|large|Picture of TWHL]

[img:twhl.png|medium|left|Picture of TWHL]
[img:twhl.png|right|small|Picture of TWHL]
Integer elementum metus at purus pulvinar vehicula. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Aliquam quam nulla, rutrum at gravida sed, dictum in urna. Aliquam erat volutpat. Ut pulvinar elementum euismod. In eget tortor vitae lacus euismod lacinia. Fusce tempus dignissim urna sed mollis. Cras rhoncus mollis nulla sit amet vehicula. Donec eu viverra quam. Mauris tincidunt lacinia facilisis. Aliquam erat volutpat. Fusce enim tellus, ullamcorper sit amet semper eget, interdum eu leo. Nunc leo eros, euismod quis lacinia in, sodales at felis. Duis blandit odio vel libero sollicitudin ac semper purus lacinia. Pellentesque nisl metus, ultricies et adipiscing nec, dictum nec mauris. Vestibulum ultrices nisl id nulla pellentesque id vehicula risus malesuada.

This image should be [img:twhl.png|inline] inline.

Tables:
|= Table Header 1 | Table Header 2 | Table Header 3
|- Row 1 Col 1 | [b]Row 1 Col 2[/b] | Row 1 Col 3
|- Row 2 Col 1 | [i]Row 2 Col 2[/i] | Row 2 Col 3
|- Row 3 Col 1 | Row 3 Col 2 [[Test|Test Page]] | Row 3 Col 3
|- Row 4 Col 1 | [img:twhl.png] | Row 4 Col 3
|= Table Header 1 | Table Header 2 | Table Header 3

Youtubin':

[youtube]oGlhgVz5r6E[/youtube]

[youtube:oGlhgVz5r6E|small|Black Mesa Trailer]";
    }
}
