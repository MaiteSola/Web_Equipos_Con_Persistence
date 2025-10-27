<?php
/**
 * @title: Proyecto integrador Ev01 - Registro en el sistema.
 * @description:  Script PHP para almacenar un nuevo usuario en la base de datos
 *
 * @version    0.2
 *
 * @author     Ander Frago & Miguel Goyena <miguel_goyena@cuatrovientos.org>
 */

//TODO completa los requiere que necesites

$error = $user = $pass = "";

if (isset($_POST['email'])) {

  // TODO Realiza la lectura de los campos del formulario en $user y $pass
  

  if ($user == "" || $pass == "") {
    $error = "Debes completar todos los campos<br><br>";
  }
  else {
    
    // TODO Inserta el nuevo usuario atraves de un objeto UserDAO, este la utiliza el PersistenceManager
     

    // TODO establece el usuario en sesión con SessionHelper
    

  }
}
?>

<div class="container">
    <form class="form-horizontal" role="form" method="POST" action="signup.php">
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <h2>Por favor registrate</h2>
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <div class="form-group has-danger">
                    <label class="sr-only" for="email">Email:</label>
                    <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                        <div class="input-group-addon" style="width: 2.6rem"><i
                                    class="fa fa-at"></i></div>
                        <input type="text" name="email" class="form-control"
                               id="email"
                               placeholder="vivayo@correo.com"
                               autofocus>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-control-feedback">
                    <span class="text-danger align-middle">
                        <i class="fa fa-close"></i>
                    </span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="sr-only" for="password">Contraseña:</label>
                    <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                        <div class="input-group-addon" style="width: 2.6rem"><i
                                    class="fa fa-key"></i></div>
                        <input type="password" name="password"
                               class="form-control" id="password"
                               placeholder="Password">
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-control-feedback">
                    <span class="text-danger align-middle">
                    </span>
                </div>
            </div>
        </div>

        <div class="row" style="padding-top: 1rem">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <button type="submit" class="btn btn-success"><i
                            class="fa fa-sign-in"></i> Registrar
                </button>
            </div>
        </div>
    </form>
</div>
