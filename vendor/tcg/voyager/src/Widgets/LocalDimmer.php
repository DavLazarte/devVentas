<?php

namespace TCG\Voyager\Widgets;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use TCG\Voyager\Facades\Voyager;

class LocalDimmer extends BaseDimmer
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        // $count = Voyager::model('User')->count();
        // $string = trans_choice('voyager::dimmer.user', $count);

        return view('voyager::dimmer', array_merge($this->config, [
            'icon'   => 'voyager-basket',
            'title'  => "Sistema de Ventas",
            'text'  => "click en el botón de abajo para ingresar al sistema",
            // 'text'   => __('voyager::dimmer.user_text', ['count' => $count, 'string' => Str::lower($string)]),
            'button' => [
                'text' =>"Ingresar",
                'link' => route('sistema'),
            ],
            'image' => asset('images/bg-login-register.png'),
        ]));
    }

    /**
     * Determine if the widget should be displayed.
     *
     * @return bool
     */
    public function shouldBeDisplayed()
    {
        // return Auth::user()->can('browse', Voyager::model('User'));
        return true;
    }
}
