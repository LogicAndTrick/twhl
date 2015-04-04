<?php namespace App\Providers;

use Blade;
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
        // @form(url)
        Blade::extend(function($view, $compiler)
        {
            $pattern = $this->createSensibleOpenMatcher('form');
            return preg_replace_callback($pattern, function($matches) {
                $parameters = explode(' ', $matches[2]);
                $url = url($parameters[0]);
                $method = 'post';
                $tok = '<input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">';
                // other parameters here...
                return $matches[1]."<form action='$url' method='$method'>$tok";
            }, $view);
        });

        // @endform
        Blade::extend(function($view, $compiler) {
            $pattern = $compiler->createPlainMatcher('endform');
            return preg_replace($pattern, '$1</form>$2', $view);
        });

        // @hidden(name:mapped_name $model)
        Blade::extend(function($view, $compiler)
        {
            $pattern = $this->createSensibleOpenMatcher('hidden');
            return preg_replace_callback($pattern, function($matches) {
                $wht = $matches[1];
                $parameters = explode(' ', $matches[2]);
                $param = explode(':', $parameters[0]);
                $name = $param[0];
                $mapped_param = count($param) > 1 ? $param[1] : $name;
                $model_var = array_first($parameters, function($k, $x) { return $x[0] == '$'; }) ?: '$__undefined__';
                $collect_value = "<?php echo (isset($model_var) ? $model_var->$name : null) ?: ''; ?>";
                return "$wht<input type='hidden' name='$mapped_param' value='$collect_value'>";
            }, $view);
        });

        // @text(name:mapped_name $model) = Label
        Blade::extend(function($view, $compiler) {
            $pattern = '/(?<!\w)(\s*)@text\s*\(([^)]*)\)*(?: = ([^\r\n]*))?/';
            return preg_replace_callback($pattern, function($matches) {
                $wht = $matches[1];
                $parameters = explode(' ', $matches[2]);
                $param = explode(':', $parameters[0]);
                $name = $param[0];
                $mapped_param = count($param) > 1 ? $param[1] : $name;
                $label = isset($matches[3]) ? $matches[3] : $name;
                $id = preg_replace('/[^a-z0-9]/i', '_', $name) . '_' . rand(10000, 99999);
                $label = htmlspecialchars($label);
                $model_var = array_first($parameters, function($k, $x) { return $x[0] == '$'; }) ?: '$__undefined__';
                $collect_value = "<?php echo Request::old('$mapped_param') ?: (isset($model_var) ? $model_var->$name : null) ?: ''; ?>";
                return "$wht<div class='form-group<?php echo \$errors->has('$name') || \$errors->has('$mapped_param') ? ' has-error' : ''; ?>'>"
                    ."$wht    <label for='$id'>$label</label>"
                    ."$wht    <input type='text' class='form-control' id='$id' placeholder='$label' name='$mapped_param' value='$collect_value' />"
                    ."$wht    <?php echo \$errors->has('$name') || \$errors->has('$mapped_param') ? '<p class=\'help-block\'>' . (\$errors->first('$name') ?: \$errors->first('$mapped_param')) . '</p>' : ''; ?>"
                ."$wht</div>";
            }, $view);
        });

        // @autocomplete(name:mapped_name url $model ...) = Label
        Blade::extend(function($view, $compiler) {
            $pattern = '/(?<!\w)(\s*)@autocomplete\s*\(([^)]*)\)*(?: = ([^\r\n]*))?/';
            return preg_replace_callback($pattern, function($matches) {
                $wht = $matches[1];
                $parameters = explode(' ', $matches[2]);
                $param = explode(':', $parameters[0]);
                $name = $param[0];
                $mapped_param = count($param) > 1 ? $param[1] : $name;
                $url = url($parameters[1]);
                $label = isset($matches[3]) ? $matches[3] : $name;
                $id = preg_replace('/[^a-z0-9]/i', '_', $name) . '_' . rand(10000, 99999);
                $label = htmlspecialchars($label);
                $model_var = array_first($parameters, function($k, $x) { return $x[0] == '$'; }) ?: '$__undefined__';
                $kvs = ['id' => 'id', 'name' => 'name', 'placeholder' => $label];
                for ($i = 0; $i < count($parameters); $i++) {
                    $p = $parameters[$i];
                    if (!strstr($p, '=')) continue;
                    $split = explode('=', $p);
                    $kvs[$split[0]] = $split[1];
                }
                $kvs['url'] = $url;
                $json_args = json_encode($kvs);
                $collect_value = "<?php
                    \$sel_value = Request::old('$mapped_param') ?: (isset($model_var) ? $model_var->$name : null) ?: null;
                    echo \$sel_value ? '<option value=\'' . \$sel_value . '\' selected>' . '</option>' : '';
                ?>";
                return "$wht<div class='form-group<?php echo \$errors->has('$name') || \$errors->has('$mapped_param') ? ' has-error' : ''; ?>'>"
                    ."$wht    <label for='$id'>$label</label>"
                    ."$wht    <div class='controls'><select class='autocomplete' id='$id' name='$mapped_param'>$collect_value</select></div>"
                    ."$wht    <?php echo \$errors->has('$name') || \$errors->has('$mapped_param') ? '<p class=\'help-block\'>' . (\$errors->first('$name') ?: \$errors->first('$mapped_param')) . '</p>' : ''; ?>"
                ."$wht</div><script type='text/javascript'>$(function() {
                        $('#$id').autocomplete($json_args);
                    });</script>";
            }, $view);
        });

        // @submit
        Blade::extend(function($view, $compiler) {
            $pattern = '/(?<!\w)(\s*)@submit(?:\(([^)]*)\))?(\s*)/';
            return preg_replace_callback($pattern, function($matches) {
                $wht = $matches[1];
                $text = isset($matches[1]) ? trim($matches[1]) : '';
                if (!$text) $text = 'Submit';
                return "$wht<button type='submit' class='btn btn-default'>$text</button>";
            }, $view);
        });
	}

}
