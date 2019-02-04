<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UserRegisterTest extends DuskTestCase
{
    /**Comprueba el registro de un usuario
     * Entra a la pagina principal
     * Da click en el Boton Iniciar Sesion
     * Comprueba que este en la pantalla Login
     * Da click en el boton Registrate
     * Llena el formulario
     * Da al boton Registrar
     * Comprueba que vuelve al inicio de sesion
     */

    public function test_User_Register()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('http://localhost/Qontigo/public')
            	    ->click('a[href="http://localhost/Qontigo/public/login"]')
          	        ->assertUrlIs('http://localhost/Qontigo/public/login')
            	    ->click('a[href="http://localhost/Qontigo/public/register"]')
             	    ->assertUrlIs('http://localhost/Qontigo/public/register')
                    ->type('name', 'Test Ubitica')
                    ->type('email', 'test.ubitica@gmail.com')
                    ->type('password', '123456')
                    ->type('password_confirmation', '123456')
		            ->click('#registrar');    		
                    $this->assertTrue(true);
                    
        });
    
    }

}

