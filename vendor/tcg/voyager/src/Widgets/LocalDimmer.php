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
        $local = auth()->user()->local;

        if ($local && $local->estado === 'activo') {
            return view('voyager::dimmer', array_merge($this->config, [
                'icon'   => 'voyager-basket',
                'title'  => "Sistema de Ventas",
                'text'   => "Click en el botón de abajo para ingresar al sistema",
                'button' => [
                    'text' => "Ingresar",
                    'link' => route('sistema'),
                ],
                'image'  => asset('images/bg-login-register.png'),
            ]));
        } else {
            return view('voyager::dimmer', array_merge($this->config, [
                'icon'   => 'voyager-warning',
                'title'  => "Acceso restringido",
                'text'   => "No tenés un local activo asignado. Contactá al administrador.",
                // 'button' => null,
                'image'  => asset('images/bg-login-register.png'),
            ]));
        }
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
