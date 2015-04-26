<?php namespace App\Providers;

use Blade;
use Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider {

	public function boot()
	{
        //
	}

    public function createSensibleOpenMatcher($function)
    {
        return '/(?<!\w)(\s*)@'.$function.'\s*\((.*)\)/';
    }

	public function register()
	{
        // {? code ?}
        Blade::extend(function($view, $compiler) {
            $pattern = '/\{\?(.*?)\?\}/i';
            return preg_replace('/\{\?(.*?)\?\}/i', '<?php $1 ?>', $view);
        });

        // @form(url)
        Blade::extend(function($view, $compiler) {
            $pattern = $this->createBladeTemplatePattern('form');
            return preg_replace_callback($pattern, function($matches) {
                $parameters = $this->parseBladeTemplatePattern($matches, ['url'], ['method' => 'post', 'upload' => false]);
                $url = url($parameters['url']);
                $method = $parameters['method'];
                $upload = $parameters['upload'] ? "enctype='multipart/form-data'" : '';
                return "{$matches[1]}<form action='$url' method='$method' $upload><input type='hidden' name='_token' value='<?php echo csrf_token(); ?>'>";
            }, $view);
        });

        // @endform
        Blade::extend(function($view, $compiler) {
            $pattern = $compiler->createPlainMatcher('endform');
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
                $parameters = $this->parseBladeTemplatePattern($matches, ['name'], [], 'label');

                $expl_name = explode(':', $parameters['name']);
                $name = $expl_name[0];
                $mapped_name = array_get($expl_name, 1, $name);
                $name_array = "['$name', '$mapped_name']";

                $label = htmlspecialchars( array_get($parameters, 'label', $name) );
                $id = $this->generateHtmlId($name);
                $var = array_get($parameters, '$', 'null');
                $var = array_get($parameters, 'value', $var);

                $collect = "<?php echo \\App\\Providers\\BladeServiceProvider::CollectValue($var, '$mapped_name', '$name'); ?>";
                $error_class = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorClass(\$errors, $name_array); ?>";
                $error_message = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorMessageIfExists(\$errors, $name_array); ?>";

                return "{$matches[1]}<div class='form-group $error_class'><label for='$id'>$label</label>" .
                "<input type='text' class='form-control' id='$id' placeholder='$label' name='$mapped_name' value='$collect' />" .
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

                $label = htmlspecialchars( array_get($parameters, 'label', $name) );
                $id = $this->generateHtmlId($name);
                $var = array_get($parameters, '$', 'null');
                $var = array_get($parameters, 'value', $var);

                $collect = "<?php echo \\App\\Providers\\BladeServiceProvider::CollectValue($var, '$mapped_name', '$name') ? 'checked' : ''; ?>";
                $error_class = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorClass(\$errors, $name_array); ?>";
                $error_message = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorMessageIfExists(\$errors, $name_array); ?>";

                return "{$matches[1]}<div class='checkbox $error_class'><label for='$id'>" .
                "<input type='checkbox' id='$id' name='$mapped_name' $collect />" .
                "$label</label>$error_message</div>";
            }, $view);
        });

        // @textarea(name:mapped_name $model) = Label
        Blade::extend(function($view, $compiler) {
            $pattern = $this->createBladeTemplatePattern('textarea');
            return preg_replace_callback($pattern, function($matches) {
                $parameters = $this->parseBladeTemplatePattern($matches, ['name'], [], 'label');

                $expl_name = explode(':', $parameters['name']);
                $name = $expl_name[0];
                $mapped_name = array_get($expl_name, 1, $name);
                $name_array = "['$name', '$mapped_name']";

                $label = htmlspecialchars( array_get($parameters, 'label', $name) );
                $id = $this->generateHtmlId($name);
                $var = array_get($parameters, '$', 'null');
                $var = array_get($parameters, 'value', $var);

                $collect = "<?php echo \\App\\Providers\\BladeServiceProvider::CollectValue($var, '$mapped_name', '$name'); ?>";
                $error_class = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorClass(\$errors, $name_array); ?>";
                $error_message = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorMessageIfExists(\$errors, $name_array); ?>";

                return "{$matches[1]}<div class='form-group $error_class'><label for='$id'>$label</label>" .
                "<textarea class='form-control' id='$id' placeholder='$label' name='$mapped_name'>$collect</textarea>" .
                "$error_message</div>";
            }, $view);
        });

        // @autocomplete(name:mapped_name url $model) = Label
        Blade::extend(function($view, $compiler) {
            $pattern = $this->createBladeTemplatePattern('autocomplete');
            return preg_replace_callback($pattern, function($matches) {
                $parameters = $this->parseBladeTemplatePattern($matches, ['model_name', 'url'], ['clearable' => false, 'placeholder' => '', 'id' => 'id', 'name' => 'name'], 'label');

                $expl_name = explode(':', $parameters['model_name']);
                $name = $expl_name[0];
                $mapped_name = array_get($expl_name, 1, $name);
                $name_array = "['$name', '$mapped_name']";

                $parameters['url'] = url($parameters['url']);
                if (!$parameters['placeholder']) $parameters['placeholder'] = array_get($parameters, 'label', $name);
                $label = htmlspecialchars( array_get($parameters, 'label', $name) );
                $id = $this->generateHtmlId($name);
                $var = array_get($parameters, '$', 'null');
                $var = array_get($parameters, 'value', $var);

                $collect = "<?php \$sel_value = \\App\\Providers\\BladeServiceProvider::CollectValue($var, '$mapped_name', '$name'); " .
                "echo \$sel_value ? '<option value=\'' . \$sel_value . '\' selected>' . \$sel_value . '</option>' : ''; ?>";
                $error_class = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorClass(\$errors, $name_array); ?>";
                $error_message = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorMessageIfExists(\$errors, $name_array); ?>";

                $json_args = json_encode($parameters);

                return "{$matches[1]}<div class='form-group $error_class'><label for='$id'>$label</label>" .
                "<div class='controls'><select class='autocomplete' id='$id' name='$mapped_name'>$collect</select></div>" .
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

                $label = htmlspecialchars( array_get($parameters, 'label', $name) );
                $id = $this->generateHtmlId($name);
                $var = array_get($parameters, '$', 'null');
                $var = array_get($parameters, 'value', $var);

                $error_class = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorClass(\$errors, $name_array); ?>";
                $error_message = "<?php echo \\App\\Providers\\BladeServiceProvider::ErrorMessageIfExists(\$errors, $name_array); ?>";

                return "{$matches[1]}<div class='form-group $error_class'><label for='$id'>$label</label>" .
                "<input type='file' id='$id' placeholder='$label' name='$mapped_name' />" .
                "$error_message</div>";
            }, $view);
        });

        // @submit = Label
        Blade::extend(function($view, $compiler) {
            $pattern = $this->createBladeTemplatePattern('submit');
            return preg_replace_callback($pattern, function($matches) {
                $parameters = $this->parseBladeTemplatePattern($matches, [], [], 'label');

                $label = htmlspecialchars( array_get($parameters, 'label', 'Submit') );

                return "{$matches[1]}<button type='submit' class='btn btn-default'>$label</button>";
            }, $view);
        });
	}

    private function generateHtmlId($name) {
        return htmlspecialchars( preg_replace('/[^a-z0-9]/i', '_', $name) ) . '_' . rand(10000, 99999);
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
            if ($errors->has($n)) return $errors->first($n);
        }
        return null;
    }

    public static function ErrorMessageIfExists($errors, $names) {
        $message = BladeServiceProvider::FirstErrorMessage($errors, $names);
        return $message ? '<p class="help-block">' . $message . "</p>" : '';
    }

    public static function ErrorClass($errors, $names) {
        $message = BladeServiceProvider::FirstErrorMessage($errors, $names);
        return $message ? 'has-error' : '';
    }

    public static function CollectValue($value, $req_name, $model_name) {
        $val = null;
        if (Request::old($req_name)) $val = Request::old($req_name);
        else if ($value instanceof Model) $val = $value->{$model_name};
        else $val = $value;
        return $val;
    }

}
