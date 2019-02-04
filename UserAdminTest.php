<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UserAdminTest extends DuskTestCase
{
    /**
     * Entra en la pagina principal
     * Da click en el boton de inicio de sesion
     * Se loguea como Administrador
     * Da click en el boton Iniciar Session
     * Entra en la sesion Administrador
     * Da click al menu Operaciones: pendientes, Por Verificar, Proceso de envio y Realizada
     * Da click al boton Ajustes: Contraseña, Notificaciones
     * Da click a Cambiar precio del dolar
     * Da click a Resumen de operaciones
     * Da click a Cerrar sesion
     * Se redirige a la pagina principal
     *
     * @return void
     */
    public function test_UserAdmin()
    {
        $this->browse(function (Browser $browser) {
              $browser->visit('http://localhost/Qontigo/public')
	      ->click('a[href="http://localhost/Qontigo/public/login"]')
	      ->assertUrlIs('http://localhost/Qontigo/public/login')
	      ->type('email', 'savk@gmail.com')
	      ->type('password', '12345678')  
	      ->click('button.btn.btn2')  
	      ->assertUrlIs('http://localhost/Qontigo/public/Admin/home')
	      ->click('.fa.fa-files-o.fa-fw') 
	      ->click('a[href="http://localhost/Qontigo/public/Admin/orders/pending"]') 
	      ->assertUrlIs('http://localhost/Qontigo/public/Admin/orders/pending')
	      ->click('a[href="http://localhost/Qontigo/public/Admin/orders/por_verificar"]') 
	      ->assertUrlIs('http://localhost/Qontigo/public/Admin/orders/por_verificar')
	      ->click('a[href="http://localhost/Qontigo/public/Admin/orders/sendingProcess"]') 
	      ->assertUrlIs('http://localhost/Qontigo/public/Admin/orders/sendingProcess')
	      ->click('a[href="http://localhost/Qontigo/public/Admin/orders/ready"]') 
	      ->assertUrlIs('http://localhost/Qontigo/public/Admin/orders/ready')
	      ->click('.fa.fa-cog.fa-fw') 
	      ->click('a[href="http://localhost/Qontigo/public/Admin/changePassword"]')
	      ->assertUrlIs('http://localhost/Qontigo/public/Admin/changePassword')
	      ->click('a[href="http://localhost/Qontigo/public/Admin/Notification"]')
	      ->assertUrlIs('http://localhost/Qontigo/public/Admin/Notification')
	      ->click('a[href="http://localhost/Qontigo/public/Admin/changePriceDolar"]')
	      ->assertUrlIs('http://localhost/Qontigo/public/Admin/changePriceDolar')
	      ->click('a[href="http://localhost/Qontigo/public/Admin/summary"]')
	      ->assertUrlIs('http://localhost/Qontigo/public/Admin/summary')
	      ->click('a[href="http://localhost/Qontigo/public/logout"]')
	      ->assertUrlIs('http://localhost/Qontigo/public/login');



        });
    }
}

