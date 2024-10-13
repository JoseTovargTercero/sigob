<!DOCTYPE html>
<html lang="es">
<!-- [Head] start -->

<head>
  <title>Sigob</title>
  <!-- [Meta] -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="author" content="Codedthemes">
  <link rel="icon" type="image/png" href="src/assets/images/logo.png">

  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="src/assets/css/style.css" id="main-style-link">
  <link rel="stylesheet" href="src/assets/css/style-preset.css">
  <script src="src/assets/js/sweetalert2.all.min.js"></script>
</head>
<!-- [Head] end -->
<!-- [Body] Start -->

<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
  <!-- [ Pre-loader ] start -->
  <div class="loader-bg">
    <div class="loader-track">
      <div class="loader-fill"></div>
    </div>
  </div>
  <!-- [ Pre-loader ] End -->

  <div class="auth-main">
    <div class="auth-wrapper v1">
      <div class="auth-form">
        <div class="position-relative my-5">
          <div class="auth-bg">
            <span class="r"></span>
            <span class="r s"></span>
            <span class="r s"></span>
            <span class="r"></span>
          </div>
          <div class="card mb-0">
            <form action="back/global/login_validate.php" id="formLogin" method="POST" class="card-body">
              <div class="text-center">
                <a href="#"><img src="src/assets/images/logo.png" width="60px" alt="img"></a>
              </div>
              <h4 class="text-center f-w-500 mt-4 mb-3">Inicio</h4>
              <div class="form-group mb-3">
                <input type="email" name="email" class="form-control"
                  placeholder="Correo Electrónico">
              </div>
              <div class="form-group mb-3">
                <input type="password" name="password" class="form-control" placeholder="Contraseña">
              </div>
              <div class="d-flex mt-1 justify-content-between align-items-center">

                <a href="recuperar_usuario.php" class="text-secondary f-w-400 mb-0">Olvidó su contraseña?</a>
              </div>
              <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary shadow px-sm-4">Verificar</button>
              </div>
              <div class="d-flex justify-content-between align-items-end mt-4">
                Copy


              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- [ Main Content ] end -->
  <!-- Required Js
  preset-3
  -->

  <script src="src/form.js"></script>
</body>

</html>