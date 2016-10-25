<?php

namespace App\Users;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UsersController implements ControllerProviderInterface
{

  public function connect(Application $app)
  {
    // creates a new controller based on the default route
    $controller = $app['controllers_factory'];

    // la ruta "/users/list"
    $controller->get('/list', function() use($app) {

      // obtiene el nombre de usuario de la sesión
      $user = $app['session']->get('user');

      // obtiene el listado de usuarios
      $users = $app['session']->get('users');
      if (!isset($users)) {
        $users = array();
      }

      // ya ingreso un usuario ?
      if ( isset( $user ) && $user != '' ) {
        // muestra la plantilla
        return $app['twig']->render('Users/users.list.html.twig', array(
          'user' => $user,
          'users' => $users
        ));

      } else {
        // redirige el navegador a "/login"
        return $app->redirect( $app['url_generator']->generate('login'));
      }

    // hace un bind
    })->bind('users-list');

    // la ruta "/users/new"
    $controller->get('/new', function() use($app) {

      // obtiene el nombre de usuario de la sesión
      $user = $app['session']->get('user');

      // ya ingreso un usuario ?
      if ( isset( $user ) && $user != '' ) {
        // muestra la plantilla
        return $app['twig']->render('Users/users.edit.html.twig', array(
          'user' => $user,
          'index' => '',
          'user_to_edit' => array(
              'nombre' => '',
              'apellido' => '',
              'direccion' => '',
              'email' => '',
              'telefono' => ''
            )
        ));

      } else {
        // redirige el navegador a "/login"
        return $app->redirect( $app['url_generator']->generate('login'));
      }

    // hace un bind
    })->bind('users-new');

    // la ruta "/users/edit"
    $controller->get('/edit/{index}', function($index) use($app) {

      // obtiene el nombre de usuario de la sesión
      $user = $app['session']->get('user');

      // obtiene los usuarios de la sesión
      $users = $app['session']->get('users');
      if (!isset($users)) {
        $users = array();
      }

      // no ha ingresado el usuario (no ha hecho login) ?
      if ( !isset( $user ) || $user == '' ) {
        // redirige el navegador a "/login"
        return $app->redirect( $app['url_generator']->generate('login'));

      // no existe un usuario en esa posición ?
      } else if ( !isset($users[$index])) {
        // muestra el formulario de nuevo usuario
        return $app->redirect( $app['url_generator']->generate('users-new') );

      } else {
        // muestra la plantilla
        return $app['twig']->render('Users/users.edit.html.twig', array(
          'user' => $user,
          'index' => $index,
          'user_to_edit' => $users[$index]
        ));

      }

    // hace un bind
    })->bind('users-edit');

    $controller->post('/save', function( Request $request ) use ( $app ){

      // obtiene los usuarios de la sesión
      $users = $app['session']->get('users');
      if (!isset($users)) {
        $users = array();
      }

      // index no está incluido en la petición
      $index = $request->get('index');
      if ( !isset($index) || $index == '' ) {
        // agrega el nuevo usuario
        $users[] = array(
          'nombre' => $request->get('nombre'),
          'apellido' => $request->get('apellido'),
          'direccion' => $request->get('direccion'),
          'email' => $request->get('email'),
          'telefono' => $request->get('telefono')
        );
      } else {
        // modifica el usuario en la posición $index
        $users[$index] = array(
          'nombre' => $request->get('nombre'),
          'apellido' => $request->get('apellido'),
          'direccion' => $request->get('direccion'),
          'email' => $request->get('email'),
          'telefono' => $request->get('telefono')
        );
      }

      // actualiza los datos en sesión
      $app['session']->set('users', $users);

      // muestra la lista de usuarios
      return $app->redirect( $app['url_generator']->generate('users-list') );
    })->bind('users-save');

    $controller->get('/delete/{index}', function($index) use ($app) {

      // obtiene los usuarios de la sesión
      $users = $app['session']->get('users');
      if (!isset($users)) {
        $users = array();
      }

      // no existe un usuario en esa posición ?
      if ( isset($users[$index])) {
        unset ($users[$index]);
        $app['session']->set('users', $users);
      }

      // muestra la lista de usuarios
      return $app->redirect( $app['url_generator']->generate('users-list') );

    })->bind('users-delete');

    return $controller;
  }

}