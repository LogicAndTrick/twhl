<?php namespace App\Providers;

use Blade;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider {

    public function register()
   	{
           //
   	}

    public function createSensibleOpenMatcher($function)
    {
        return '/(?<!\w)(\s*)@'.$function.'\s*\((.*)\)/';
    }

    /*
     Blade::directive('datetime', function($expression) {
         return "<?php echo with{$expression}->format('m/d/Y H:i'); ?>";
     });
    */

	public function boot()
	{
        // {? code ?}
        Blade::extend(function($view, $compiler) {
            return preg_replace('/\{\?(.*?)\?\}/is', '<?php $1 ?>', $view);
        });

        // <hc> and </hc>
        Blade::extend(function($view, $compiler) {
            $r = $view;
            $r = preg_replace('/<hc>/is', '<div class="header-container">', $r);
            $r = preg_replace('/<hc class=["\'](.*?)["\']>/is', '<div class="header-container $1">', $r);
            $r = preg_replace('/<\/hc>/is', '</div>', $r);
            return $r;
        });

        // @avatar(user type show_name show_title show_border)
        Blade::extend(function($view, $compiler) {
            $pattern = $this->createBladeTemplatePattern('avatar');
            return preg_replace_callback($pattern, function($matches) {
                $parameters = $this->parseBladeTemplatePattern($matches, ['user', 'type'], ['show_image' => 'true', 'show_name' => 'true', 'show_title' => 'true', 'show_border' => 'false', 'link' => 'true', 'classes' => '' ]);
                $user = $parameters['user'];
                $class = $parameters['type'];
                $border = $parameters['show_border'] == 'true' ? 'border' : '';
                $img = $class != 'text' && $parameters['show_image'] == 'true';
                $name = $parameters['show_name'] == 'true';
                $title = $class == 'full' && $parameters['show_title'] == 'true';
                $link = $parameters['link'] == 'true';
                $extra_classes = $parameters['classes'];
                return "{$matches[1]}<span class=\"avatar $class $border $extra_classes\" title=\"<?php echo {$user}->name; ?>\">" .
                ($link ? "\n<a href=\"<?php echo act('user', 'view', {$user}->id); ?>\">" : '') .
                ($img ? "\n<img src=\"<?php echo {$user}->getAvatarUrl('$class'); ?>\" alt=\"<?php echo {$user}->name; ?>\"/>" : '') .
                ($name ? "\n<span class=\"name\"><?php echo {$user}->name; ?></span>" : "") .
                ($link ? "</a>" : '').
                ($title ? "<?php if ({$user}->title_custom) { ?><span class=\"title\"><?php echo {$user}->title_text; ?></span><?php } ?>" : "") .
                "</span>";
            }, $view);
        });

        // @date(date)
        Blade::extend(function($view, $compiler) {
            $pattern = $this->createBladeTemplatePattern('date');
            return preg_replace_callback($pattern, function($matches) {
                $parameters = $this->parseBladeTemplatePattern($matches, ['date'], ['format' => 'nice']);
                $date = $parameters['date'];
                $format = $parameters['format'];
                $func = $format == 'nice' ? 'diffForHumans()' : "format('$format')";
                $func = "(!{$date} ? 'Never' : {$date}->{$func})";
                $raw = "(!{$date} ? 'Never' : {$date}->format('Y-m-d H:i:s T'))";
                return "{$matches[1]}<span class='nice-date' title='<?php echo {$raw}; ?>'><span class='formatted'><?php echo {$func}; ?></span><span class='raw'><?php echo {$raw}; ?></span></span>";
            }, $view);
        });

        // @title(page_title)
        Blade::extend(function($view, $compiler) {
            $pattern = '/(?<!\w)\s*@title\((.*)\)(?!\w)/';
            return preg_replace_callback($pattern, function($matches) {
                $page_title = $matches[1];
                return '<?php $page_title = ('. $page_title .'); ?>';
            }, $view);
        });

        // @form(url)
        Blade::extend(function($view, $compiler) {
            $pattern = $this->createBladeTemplatePattern('form');
            return preg_replace_callback($pattern, function($matches) {
                $parameters = $this->parseBladeTemplatePattern($matches, ['url'], ['method' => 'post', 'upload' => false]);
                $url = $parameters['url'];
                $method = $parameters['method'];
                $upload = $parameters['upload'] ? "enctype='multipart/form-data'" : '';
                return "{$matches[1]}<form action='<?php echo url(\"$url\"); ?>' method='$method' $upload><input type='hidden' name='_token' value='<?php echo csrf_token(); ?>'>";
            }, $view);
        });

        // @endform
        Blade::extend(function($view, $compiler) {
            $pattern = '/(?<!\w)(\s*)@endform(\s*)/';
            return preg_replace($pattern, '$1</form>$2', $view);
        });

        // @hidden(name:mapped_name $model)
        Blade::extend(function($view, $compiler) {
            $pattern = $this->createBladeTemplatePattern('hidden');
            return preg_replace_callback($pattern, function($matches) {
                $parameters = $this->parseBladeTemplatePattern($matches, ['name']);

                $expl_name = explode(':', $parameters['name']);
                $name = $expl_name[0];
                $mapped_name = array_get($expl_name, 1, $name);

                $var = array_get($parameters, '$', 'null');
                $var = array_get($parameters, 'value', $var);
                $collect = "<?php echo \\App\\Providers\\BladeServiceProvider::CollectValue($var, '$mapped_name', '$name'); ?>";

                return "{$matches[1]}<input type='hidden' name='$mapped_name' value='$collect'>";
            }, $view);
        });

        // @text(name:mapped_name $model) = Label
        Blade::extend(function($view, $compiler) {
            $pattern = $this->createBladeTemplatePattern('text');
            return preg_replace_callback($pattern, function($matches) {
                $parameters = $this->parseBladeTemplatePattern($matches, ['name'], ['format' => null, 'placeholder' => ''], 'label');

                $expl_name = explode(':', $parameters['name']);
                $name = $expl_name[0];
                $mapped_name = array_get($expl_name, 1, $name);
                $name_array = "['$name', '$mapped_name']";
                $format = $parameters['format'];

                $label = BladeServiceProvider::esc( array_get($parameters, 'label', $name) );
                $id = $this->generateHtmlId($name);
                $var = array_get($parameters, '$', 'null');
                $var = array_get($parameters, 'value', $var);
                $placeholder = BladeServiceProvider::esc( $parameters['placeholder'] );
                if (!$placeholder) $placeholder = $label;

                $collect = "<?php echo \\App\\Providers\\BladeServiceProvider::CollectValue($var, '$mapped_name', '$name', '$format'); ?>";
                $error_class = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorClass(\$errors, $name_array); ?>";
                $error_message = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorMessageIfExists(\$errors, $name_array); ?>";

                return "{$matches[1]}<div class='form-group $error_class'><label for='$id'>$label</label>" .
                "<input type='text' class='form-control' id='$id' name='$mapped_name' value='$collect' placeholder='$placeholder' />" .
                "$error_message</div>";
            }, $view);
        });

        // @password(name:mapped_name $model) = Label
        Blade::extend(function($view, $compiler) {
            $pattern = $this->createBladeTemplatePattern('password');
            return preg_replace_callback($pattern, function($matches) {
                $parameters = $this->parseBladeTemplatePattern($matches, ['name'], ['format' => null, 'placeholder' => 'Password'], 'label');

                $expl_name = explode(':', $parameters['name']);
                $name = $expl_name[0];
                $mapped_name = array_get($expl_name, 1, $name);
                $name_array = "['$name', '$mapped_name']";
                $format = $parameters['format'];

                $label = BladeServiceProvider::esc( array_get($parameters, 'label', $name) );
                $id = $this->generateHtmlId($name);
                $var = array_get($parameters, '$', 'null');
                $var = array_get($parameters, 'value', $var);

                $collect = "<?php echo \\App\\Providers\\BladeServiceProvider::CollectValue($var, '$mapped_name', '$name', '$format'); ?>";
                $error_class = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorClass(\$errors, $name_array); ?>";
                $error_message = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorMessageIfExists(\$errors, $name_array); ?>";

                return "{$matches[1]}<div class='form-group $error_class'><label for='$id'>$label</label>" .
                "<input type='password' class='form-control' id='$id' name='$mapped_name' value='$collect' placeholder='${parameters['placeholder']}' />" .
                "$error_message</div>";
            }, $view);
        });

        // @select(name:mapped_name $model items=$values) = Label
        Blade::extend(function($view, $compiler) {
            $pattern = $this->createBladeTemplatePattern('select');
            return preg_replace_callback($pattern, function($matches) {
                $parameters = $this->parseBladeTemplatePattern($matches, ['name', 'items'], ['name_key' => null, 'value_key' => null], 'label');

                $expl_name = explode(':', $parameters['name']);
                $name = $expl_name[0];
                $mapped_name = array_get($expl_name, 1, $name);
                $name_array = "['$name', '$mapped_name']";

                $items = $parameters['items'];
                $name_key = $parameters['name_key'];
                $value_key = $parameters['value_key'];

                $label = BladeServiceProvider::esc( array_get($parameters, 'label', $name) );
                $id = $this->generateHtmlId($name);
                $var = array_get($parameters, '$', 'null');
                $var = array_get($parameters, 'value', $var);

                $print =
                "<?php
                    \$selected_value = \\App\\Providers\\BladeServiceProvider::CollectValue($var, '$mapped_name', '$name');
                    foreach ($items as \$k => \$v) {
                        \$key = '$name_key' ? \$v['$name_key'] : \$k;
                        \$val = '$value_key' ? \$v['$value_key'] : \$v;
                        echo '<'.'option value=\"' . e(\$key) . '\" ' . (\$key == \$selected_value ? 'selected' : '') . '>' . e(\$val) . '</'.'option>';
                    }
                ?>";
                $error_class = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorClass(\$errors, $name_array); ?>";
                $error_message = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorMessageIfExists(\$errors, $name_array); ?>";

                return "{$matches[1]}<div class='form-group $error_class'><label for='$id'>$label</label>" .
                "<select class='form-control' id='$id' name='$mapped_name'>$print</select>" .
                "$error_message</div>";
            }, $view);
        });

        // @checkbox(name:mapped_name $model) = Label
        Blade::extend(function($view, $compiler) {
            $pattern = $this->createBladeTemplatePattern('checkbox');
            return preg_replace_callback($pattern, function($matches) {
                $parameters = $this->parseBladeTemplatePattern($matches, ['name'], [], 'label');

                $expl_name = explode(':', $parameters['name']);
                $name = $expl_name[0];
                $mapped_name = array_get($expl_name, 1, $name);
                $name_array = "['$name', '$mapped_name']";

                $label = BladeServiceProvider::esc( array_get($parameters, 'label', $name) );
                $id = $this->generateHtmlId($name);
                $var = array_get($parameters, '$', 'null');
                $var = array_get($parameters, 'value', $var);

                $collect = "<?php echo \\App\\Providers\\BladeServiceProvider::CollectValue($var, '$mapped_name', '$name') ? 'checked' : ''; ?>";
                $error_class = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorClass(\$errors, $name_array); ?>";
                $error_message = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorMessageIfExists(\$errors, $name_array); ?>";

                return "{$matches[1]}<div class='checkbox $error_class'><label for='$id'>" .
                "<input type='checkbox' id='$id' name='$mapped_name' $collect />" .
                " $label</label>$error_message</div>";
            }, $view);
        });

        // @radio(name:mapped_name $model) = Label
        Blade::extend(function($view, $compiler) {
            $pattern = $this->createBladeTemplatePattern('radio');
            return preg_replace_callback($pattern, function($matches) {
                $parameters = $this->parseBladeTemplatePattern($matches, ['name'], [], 'label');

                $expl_name = explode(':', $parameters['name']);
                $name = $expl_name[0];
                $mapped_name = array_get($expl_name, 1, $name);
                $name_array = "['$name', '$mapped_name']";

                $label = BladeServiceProvider::esc( array_get($parameters, 'label', $name) );
                $id = $this->generateHtmlId($name);
                $var = array_get($parameters, '$', 'null');
                $var = array_get($parameters, 'value', $var);

                $collect = "<?php echo \\App\\Providers\\BladeServiceProvider::CollectValue($var, '$mapped_name', '$name') ? 'checked' : ''; ?>";
                $error_class = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorClass(\$errors, $name_array); ?>";
                $error_message = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorMessageIfExists(\$errors, $name_array); ?>";

                return "{$matches[1]}<div class='radio $error_class'><label for='$id'>" .
                "<input type='radio' id='$id' name='$mapped_name' $collect />" .
                " $label</label>$error_message</div>";
            }, $view);
        });

        // @textarea(name:mapped_name $model) = Label
        Blade::extend(function($view, $compiler) {
            $pattern = $this->createBladeTemplatePattern('textarea');
            return preg_replace_callback($pattern, function($matches) {
                $parameters = $this->parseBladeTemplatePattern($matches, ['name'], ['class' => ''], 'label');

                $expl_name = explode(':', $parameters['name']);
                $name = $expl_name[0];
                $mapped_name = array_get($expl_name, 1, $name);
                $name_array = "['$name', '$mapped_name']";
                $class = $parameters['class'];

                $label = BladeServiceProvider::esc( array_get($parameters, 'label', $name) );
                $id = $this->generateHtmlId($name);
                $var = array_get($parameters, '$', 'null');
                $var = array_get($parameters, 'value', $var);

                $collect = "<?php echo \\App\\Providers\\BladeServiceProvider::CollectValue($var, '$mapped_name', '$name'); ?>";
                $error_class = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorClass(\$errors, $name_array); ?>";
                $error_message = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorMessageIfExists(\$errors, $name_array); ?>";

                return "{$matches[1]}<div class='form-group $error_class'><label for='$id'>$label</label>" .
                "<textarea class='form-control $class' id='$id' name='$mapped_name'>$collect</textarea>" .
                "$error_message</div>";
            }, $view);
        });

        // @autocomplete(name:mapped_name url $model) = Label
        Blade::extend(function($view, $compiler) {
            $pattern = $this->createBladeTemplatePattern('autocomplete');
            return preg_replace_callback($pattern, function($matches) {
                $parameters = $this->parseBladeTemplatePattern($matches, ['model_name', 'url'], ['clearable' => false, 'multiple' => false, 'placeholder' => '', 'id' => 'id', 'text' => 'name'], 'label');

                $expl_name = explode(':', $parameters['model_name']);
                $name = $expl_name[0];
                $mapped_name = array_get($expl_name, 1, $name);
                $name_array = "['$name', '$mapped_name']";

                $parameters['url'] = "<?php echo url('{$parameters['url']}') ?>";
                //if (!$parameters['placeholder']) $parameters['placeholder'] = array_get($parameters, 'label', $name);
                $label = BladeServiceProvider::esc( array_get($parameters, 'label', $name) );
                $id = $this->generateHtmlId($name);
                $var = array_get($parameters, '$', 'null');
                $var = array_get($parameters, 'value', $var);

                $collect = "<?php \$sel_value = \\App\\Providers\\BladeServiceProvider::CollectValue($var, '$mapped_name', '$name'); " .
                'if (!is_array($sel_value)) $sel_value = [$sel_value]; ' .
                'foreach ($sel_value as $sv) { if ($sv) echo "<option value=\'$sv\' selected=\'selected\'>$sv</option>"; } ?>';
                $error_class = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorClass(\$errors, $name_array); ?>";
                $error_message = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorMessageIfExists(\$errors, $name_array); ?>";

                $json_args = json_encode($parameters);
                $multiple = $parameters['multiple'] ? 'multiple' : '';

                return "{$matches[1]}<div class='form-group $error_class'><label for='$id'>$label</label>" .
                "<div class='controls'><select class='autocomplete' id='$id' name='$mapped_name' $multiple>$collect</select></div>" .
                "$error_message</div><script type='text/javascript'>$(function() {" .
                "$('#$id').autocomplete($json_args);" .
                "});</script>";
            }, $view);
        });

        // @file(name:mapped_name) = Label
        Blade::extend(function($view, $compiler) {
            $pattern = $this->createBladeTemplatePattern('file');
            return preg_replace_callback($pattern, function($matches) {
                $parameters = $this->parseBladeTemplatePattern($matches, ['name'], [], 'label');

                $expl_name = explode(':', $parameters['name']);
                $name = $expl_name[0];
                $mapped_name = array_get($expl_name, 1, $name);
                $name_array = "['$name', '$mapped_name']";

                $label = BladeServiceProvider::esc( array_get($parameters, 'label', $name) );
                $id = $this->generateHtmlId($name);
                $var = array_get($parameters, '$', 'null');
                $var = array_get($parameters, 'value', $var);

                $error_class = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorClass(\$errors, $name_array); ?>";
                $error_message = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorMessageIfExists(\$errors, $name_array); ?>";

                return "{$matches[1]}<div class='form-group $error_class'><label for='$id'>$label</label>" .
                "<input type='file' id='$id' name='$mapped_name' />" .
                "$error_message</div>";
            }, $view);
        });

        // @submit = Label
        Blade::extend(function($view, $compiler) {
            $pattern = $this->createBladeTemplatePattern('submit');
            return preg_replace_callback($pattern, function($matches) {
                $parameters = $this->parseBladeTemplatePattern($matches, [], [], 'label');

                $label = BladeServiceProvider::esc( array_get($parameters, 'label', 'Submit') );

                return "{$matches[1]}<button type='submit' class='btn btn-default'>$label</button>";
            }, $view);
        });
	}

    private function generateHtmlId($name) {
        return BladeServiceProvider::esc( preg_replace('/[^a-z0-9]/i', '_', $name) ) . '_' . rand(10000, 99999);
    }

    private function createBladeTemplatePattern($name) {
        return '/(?<!\w)(\s*)@' . $name . '\s*?(?:\(([^)]*)\))?(?: ?= ?([^\r\n]*))?(?!\w)/';
    }

    private function parseBladeTemplatePattern($matches, $required = [], $named = [], $eq = false) {
        $ret = [
            '_' => []
        ];
        $i = 0;
        $parameters = isset($matches[2]) ? explode(' ', $matches[2]) : [];
        $creq = count($required);
        for (; $i < $creq; $i++) {
            $ret[$required[$i]] = $parameters[$i];
        }
        $n = [];
        for (; $i < count($parameters); $i++) {
            if (!$parameters[$i]) continue;
            $spl = explode('=', $parameters[$i]);
            if (count($spl) == 2) $n[$spl[0]] = $spl[1];
            else if ($parameters[$i][0] == '$') $ret['$'] = $parameters[$i];
            else $ret['_'][] = $parameters[$i];
        }
        foreach ($named as $k => $v) {
            if (array_key_exists($k, $n)) $ret[$k] = $n[$k];
            else $ret[$k] = $v;
        }
        if ($eq && isset($matches[3]) && $matches[3]) $ret[$eq] = $matches[3];
        return $ret;
    }

    public static function FirstErrorMessage($errors, $names) {
        if (!is_array($names)) $names = [$names];
        foreach ($names as $n) {
            $n = str_replace('[]', '', $n);
            if ($errors->has($n)) return $errors->first($n);
        }
        return null;
    }

    public static function ErrorMessageIfExists($errors, $names) {
        $message = BladeServiceProvider::FirstErrorMessage($errors, $names);
        return $message ? '<p class="help-block">' . BladeServiceProvider::esc($message) . "</p>" : '';
    }

    public static function ErrorClass($errors, $names) {
        $message = BladeServiceProvider::FirstErrorMessage($errors, $names);
        return $message ? 'has-error' : '';
    }

    public static function CollectValue($value, $req_name, $model_name, $format = null) {
        $val = null;
        $req_name = str_replace('[]', '', $req_name);
        $model_name = str_replace('[]', '', $model_name);
        if (Request::old($req_name)) $val = Request::old($req_name);
        else if ($value instanceof Model) $val = $value->{$model_name};
        else $val = $value;

        if ($val instanceof Collection) {
            $val = $val->map(function($x) { return $x->id; })->toArray();
        }
        if ($val instanceof Carbon && $format) {
            $val = $val->format($format);
        }

        return BladeServiceProvider::esc($val);
    }

    public static function esc($value) {
        if (!$value) return $value;
        if (is_array($value)) {
            $esc = [];
            foreach ($value as $v) $esc[] = BladeServiceProvider::esc($v);
            return $esc;
        }
        if (!is_string($value)) return $value;
        return str_replace("'", '&#39;', htmlspecialchars($value));
    }

}
