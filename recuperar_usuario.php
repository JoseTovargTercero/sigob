<!DOCTYPE html>
<html lang="en">
<!-- [Head] start -->

<head>
  <title>Sigob</title>
  <!-- [Meta] -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description"
    content="Datta able is trending dashboard template made using Bootstrap 5 design framework. Datta able is available in Bootstrap, React, CodeIgniter, Angular,  and .net Technologies.">
  <meta name="keywords"
    content="Bootstrap admin template, Dashboard UI Kit, Dashboard Template, Backend Panel, react dashboard, angular dashboard">
  <meta name="author" content="Codedthemes">
  <link rel="icon" type="image/png" href="src/assets/images/logo.png">

  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="src/assets/css/style.css" id="main-style-link">
  <link rel="stylesheet" href="src/assets/css/style-preset.css">
  <link rel="stylesheet" href="front/mod_nomina/src/styles/style.css">
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

            <span class="r s"></span>
            <span class="r s"></span>
          </div>
          <div class="card mb-0">
            <form id="recovery-form" class="card-body" autocomplete="off">
              <div class="text-center">
                <a href="#"><img src="src/assets/images/logo.png" width="60px" alt="img"></a>
              </div>

              <h4 class="text-center f-w-500 mt-4 mb-3">Recuperar contraseña</h4>


              <div class="form-floating" id="recovery-form-part-1">
                <input type="email" name="email" class="form-control" placeholder="Consultar Electrónico"
                  id="consultar-correo">
                <label for="consultar-correo">Consultar correo</label>
              </div>

              <div class="form-floating d-none" id="recovery-form-part-2">
                <input type="text" name="token" class="form-control" placeholder="Consultar Electrónico"
                  id="validar-token">
                <label for="validar-token">Validar Token</label>
              </div>

              <div class="d-none" id="recovery-form-part-3">
                <div class="form-floating" id="recovery-form-part-3">
                  <input type="password" name="nuevaContraseña" class="form-control" placeholder="Consultar Electrónico"
                    id="nueva-contraseña">
                  <label for="nueva-contraseña">Nueva contraseña</label>
                </div>
                <div class="form-floating" id="recovery-form-part-3">
                  <input type="password" name="confirmarContraseña" class="form-control"
                    placeholder="Consultar Electrónico" id="confirmar-contraseña">
                  <label for="confirmar-contraseña">Confirmar contraseña</label>
                </div>
              </div>

              <hr class="border border-secondary border-2 opacity-50">

              <div class="form-group d-flex justify-content-between">
                <button class="btn btn-info d-none" id="btn-next">Siguiente</button>
                <button class="btn btn-info " id="btn-consult">Consultar</button>
                <button class="btn btn-info d-none" id="btn-previus">Anterior</button>
              </div>
              <a href="/sigob" class="text-center fs-6 f-w-500 mt-4 mb-3">Volver a inicio de sesión</a>

              <!-- 
              <div class="d-flex justify-content-between align-items-end mt-4">
                Copy


              </div> -->
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

  <script type="module" src="src/recoveryForm.js"></script>
</body>

</html>