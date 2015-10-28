<?php

class BBCodeTest extends TestCase {

    /*
    public function testDatabase() {
        $post = \App\Models\Forums\ForumPost::find(3);
        $input = $post->content_text;
        $output = app('bbcode')->Parse($input);
        $this->assertEquals('', $output);
    }
    */

    public function testMultiStarBug()
    {
        $input = "* * * * *";
        $expected = "<ul><li>* * * *</li></ul>";
        $output = app('bbcode')->Parse($input);
        $this->assertEquals($expected, $output);
    }

    public function testBlankListBug()
    {
        $input = "*";
        $expected = "*";
        $output = app('bbcode')->Parse($input);
        $this->assertEquals($expected, $output);
    }

    public function testPoorlyFormattedListBug()
    {
        $input = "* 1\n------------------------------------------------------";
        $expected = "<ul><li>1</li></ul>\n<hr />";
        $output = app('bbcode')->Parse($input);
        $this->assertEquals($expected, $output);
    }

    public function testQuoteBug()
    {
        $input = '[b]#include "userdata.h"[/b]';
        $expected = '<strong>#include &quot;userdata.h&quot;</strong>';
        $output = app('bbcode')->Parse($input);
        $this->assertEquals($expected, $output);
    }

    public function testMdTables()
    {
        $input = [
            "|= Heading\n|- Cell",
        ];
        $expected = [
            '<table class="table table-bordered"><tr><th>Heading</th></tr><tr><td>Cell</td></tr></table>',
        ];
        for ($i = 0; $i < count($input); $i++) {
            $output = app('bbcode')->Parse($input[$i]);
            $this->assertEquals($expected[$i], $output);
        }
    }

    public function testWikiImages()
    {
        $input = [
            '[img:test.png]',
        ];
        $expected = [
            '<img src="/wiki/embed/test.png" />',
        ];
        for ($i = 0; $i < count($input); $i++) {
            $output = app('bbcode')->Parse($input[$i]);
            $this->assertEquals($expected[$i], $output);
        }
    }

    public function testWikiLinks()
    {
        $input = [
            '[[example]]',
            '[[example|this is an example]]',
            '[[example#bookmark]]',
            '[[example#bookmark|this is an example]]',
        ];
        $expected = [
            '<a href="/wiki/example">example</a>',
            '<a href="/wiki/example">this is an example</a>',
            '<a href="/wiki/example#bookmark">example</a>',
            '<a href="/wiki/example#bookmark">this is an example</a>',
        ];
        for ($i = 0; $i < count($input); $i++) {
            $output = app('bbcode')->Parse($input[$i]);
            $this->assertEquals($expected[$i], $output);
        }
    }

    public function testQuickLinks()
    {
        $input = [
            '[http://example.com]',
            '[http://example.com|example]',
        ];
        $expected = [
            '<a href="http://example.com">http://example.com</a>',
            '<a href="http://example.com">example</a>',
        ];
        for ($i = 0; $i < count($input); $i++) {
            $output = app('bbcode')->Parse($input[$i]);
            $this->assertEquals($expected[$i], $output);
        }
    }

    public function testInlineList()
    {
        $input = '* [s]Projects[/s] [b]put on hold until after release[/b]';
        $output = app('bbcode')->Parse($input);
        $this->assertEquals('<ul><li><span class="strikethrough">Projects</span> <strong>put on hold until after release</strong></li></ul>', $output);
    }

    public function testEmpty()
    {
        $input = '';
        $output = app('bbcode')->Parse($input);
        $this->assertEquals('', $output);
    }

    public function testState() {
        $state = new \App\Helpers\BBCode\State('1 [b]2[/b] 3');

        $str = $state->ScanTo('[');
        $this->assertEquals('1 ', $str);
        $this->assertFalse($state->Done());

        $token = $state->GetToken();
        $this->assertEquals('b', $token);
        $this->assertFalse($state->Done());

        $str = $state->Next();
        $this->assertEquals('[', $str);
        $this->assertFalse($state->Done());
        $this->assertFalse($state->GetToken());

        $str = $state->ScanTo('[');
        $this->assertEquals('b]2', $str);
        $this->assertFalse($state->Done());

        $token = $state->GetToken();
        $this->assertEquals('/b', $token);
        $this->assertFalse($state->Done());

        $str = $state->Next();
        $this->assertEquals('[', $str);
        $this->assertFalse($state->Done());
        $this->assertFalse($state->GetToken());

        $str = $state->ScanTo('[');
        $this->assertEquals('/b] 3', $str);
        $this->assertTrue($state->Done());
    }

    public function testMdQuote() {

        $input = "1\n> 2\n3\n\n4";
        $output = app('bbcode')->Parse($input);
        $this->assertEquals("1\n<blockquote>2<br>\n3</blockquote>\n4", $output);
    }

    public function testSimple()
    {
        $input = '1 [b]2[/b] 3';
        $output = app('bbcode')->Parse($input);
        $this->assertEquals('1 <strong>2</strong> 3', $output);

        $input = '[b]2[/b] 3';
        $output = app('bbcode')->Parse($input);
        $this->assertEquals('<strong>2</strong> 3', $output);

        $input = '[b]2[/b] 3';
        $output = app('bbcode')->Parse($input);
        $this->assertEquals('<strong>2</strong> 3', $output);
    }

    public function testScope()
    {
        $input = '1[quote]2[b]3[/b]4[/quote]5';
        $output = app('bbcode')->Parse($input);
        $this->assertEquals('1<blockquote>2<strong>3</strong>4</blockquote>5', $output);

        $input = '1[b]2[quote]3[/quote]4[/b]5';
        $output = app('bbcode')->Parse($input);
        $this->assertEquals('1<strong>2[quote]3[/quote]4</strong>5', $output);
    }

    public function testNested()
    {
        $input = '1[quote]2[quote]3[/quote]4[/quote]5';
        $output = app('bbcode')->Parse($input);
        $this->assertEquals('1<blockquote>2<blockquote>3</blockquote>4</blockquote>5', $output);
    }

    public function testOptions()
    {
        $input = '1[quote=2]3[/quote]4';
        $output = app('bbcode')->Parse($input);
        $this->assertEquals('1<blockquote><strong>2 said:</strong>3</blockquote>4', $output);
    }

    public function testBadNested()
    {
        $input = '1[quote]2[quote]3[quote]4[/quote]5';
        $output = app('bbcode')->Parse($input);
        $this->assertEquals('1[quote]2[quote]3<blockquote>4</blockquote>5', $output);
    }

    public function testUrls()
    {
        $input = [
            '[url=example.com]1[/url]',
            '[url=http://example.com]1[/url]',
            '[url]example.com[/url]',
            '[url]http://example.com[/url]',
            '[url]this is not valid[/url]',
            '[url=this is not valid]1[/url]',
            '[email=test@example.com]1[/email]',
            '[email]test@example.com[/email]'
        ];
        $expected = [
            '<a href="http://example.com">1</a>',
            '<a href="http://example.com">1</a>',
            '<a href="http://example.com">example.com</a>',
            '<a href="http://example.com">http://example.com</a>',
            '[url]this is not valid[/url]',
            '[url=this is not valid]1[/url]',
            '<a href="mailto:test@example.com">1</a>',
            '<a href="mailto:test@example.com">test@example.com</a>'
        ];
        for ($i = 0; $i < count($input); $i++) {
            $output = app('bbcode')->Parse($input[$i]);
            $this->assertEquals($expected[$i], $output);
        }
    }

    public function testMdList()
    {
        $input = [
            "- one\n- two\n- three",
            "* one\n* two\n* three",
            "- one\n* two\n- three",
            "# one\n# two\n# three",
            "* one\n# two\n* three",
            "* one\n** two\n*** three",
            "* one\n** two\n** three",
            "* one\n** two\n* three",
            "* one\n*# two\n*# three",
            "* one\n*# two\n*#* three",
        ];
        $expected = [
            "<ul><li>one</li><li>two</li><li>three</li></ul>",
            "<ul><li>one</li><li>two</li><li>three</li></ul>",
            "<ul><li>one</li><li>two</li><li>three</li></ul>",
            "<ol><li>one</li><li>two</li><li>three</li></ol>",
            "<ul><li>one</li></ul><ol><li>two</li></ol><ul><li>three</li></ul>",
            "<ul><li>one<ul><li>two<ul><li>three</li></ul></li></ul></li></ul>",
            "<ul><li>one<ul><li>two</li><li>three</li></ul></li></ul>",
            "<ul><li>one<ul><li>two</li></ul></li><li>three</li></ul>",
            "<ul><li>one<ol><li>two</li><li>three</li></ol></li></ul>",
            "<ul><li>one<ol><li>two<ul><li>three</li></ul></li></ol></li></ul>",
        ];
        for ($i = 0; $i < count($input); $i++) {
            $output = app('bbcode')->Parse($input[$i]);
            $this->assertEquals($expected[$i], $output);
        }
    }

    public function testEmptyList()
    {
        $input = '*';
        $output = app('bbcode')->Parse($input);
        $this->assertEquals('*', $output);
    }

    public function testMdHeading()
    {
        $input = [
            "= 1 =",
            "== 1 ==",
            "=== 1 ===",
            "==== 1 ====",
            "===== 1 =====",
            "====== 1 ======",
            "======= 1 =======",
            "= [i]1[/i] =",
        ];
        $expected = [
            "<h1>1</h1>",
            "<h2>1</h2>",
            "<h3>1</h3>",
            "<h4>1</h4>",
            "<h5>1</h5>",
            "<h6>1</h6>",
            "<h6>1</h6>",
            "<h1><em>1</em></h1>",
        ];
        for ($i = 0; $i < count($input); $i++) {
            $output = app('bbcode')->Parse($input[$i]);
            $this->assertEquals($expected[$i], $output);
        }
    }

    public function testMdCode()
    {
        $input = " 1";
        $output = app('bbcode')->Parse($input);
        $this->assertEquals("<pre>1</pre>", $output);

        $input = " 1\n  2";
        $output = app('bbcode')->Parse($input);
        $this->assertEquals("<pre>1\n 2</pre>", $output);

        $input = " 1\n2\n 3";
        $output = app('bbcode')->Parse($input);
        $this->assertEquals("<pre>1</pre>\n2\n<pre>3</pre>", $output);

        $input = " [b]1[/b]";
        $output = app('bbcode')->Parse($input);
        $this->assertEquals("<pre>[b]1[/b]</pre>", $output);
    }

    public function testLine()
    {
        $input = '---';
        $output = app('bbcode')->Parse($input);
        $this->assertEquals('<hr />', $output);
    }
}
 