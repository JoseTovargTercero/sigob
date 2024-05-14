<?php
require_once '../../back/sistema_global/session.php';

if (isset($_GET["i"])) {
  $i = $_GET["i"];
} else {
  header("Location: nom_grupos");
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Formulación de nómina</title>
  <!-- [Meta] -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">

</head>
<?php require_once '../includes/header.php' ?>

<body data-pc-preset="preset-1" data-pc-sidebar-caption="true" data-pc-direction="ltr" data-pc-theme="light">
  <div class="loader-bg">
    <div class="loader-track">
      <div class="loader-fill"></div>
    </div>
  </div>

  <?php require_once '../includes/menu.php' ?>
  <!-- [ MENU ] -->

  <?php require_once '../includes/top-bar.php' ?>
  <!-- [ top bar ] -->



  <!-- [ Main Content ] start -->
  <div class="pc-container">
    <div class="pc-content">
      <div class="page-header">
        <div class="page-block">
          <div class="row align-items-center">
            <div class="col-md-12">
              <div class="page-header-title">
                <h5 class="mb-0">Formulación de nómina</h5>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- [ Main Content ] start -->
      <div class="row mb3">
        <!-- [ worldLow section ] start -->
        <div class="col-12">
          <div class="card">
            <div class="card-body p-3">
              <ul class="nav nav-pills nav-justified" role="tablist">
                <li class="nav-item">
                  <a href="#contactDetail" data-bs-toggle="tab" data-toggle="tab" class="nav-link active" aria-selected="true" role="tab"><i class="ph-duotone ph-user-circle"></i> <span class="d-none d-sm-inline">Basico</span></a>
                </li>
                <li class="nav-item">
                  <a href="#jobDetail" data-bs-toggle="tab" data-toggle="tab" class="nav-link icon-btn" aria-selected="false" tabindex="-1" role="tab"><i class="ph-duotone ph-map-pin"></i> <span class="d-none d-sm-inline">Conceptos</span></a>
              </li>
                <li class="nav-item" >
                  <a href="#educationDetail" data-bs-toggle="tab" data-toggle="tab" class="nav-link icon-btn" aria-selected="false" tabindex="-1" role="tab"><i class="ph-duotone ph-graduation-cap"></i> <span class="d-none d-sm-inline">Empleados</span></a>
                </li>
                <li class="nav-item" >
                  <a href="#finish" data-bs-toggle="tab" data-toggle="tab" class="nav-link icon-btn" aria-selected="false" tabindex="-1" role="tab"><i class="ph-duotone ph-check-circle"></i> <span class="d-none d-sm-inline">Resumen general</span></a>
                </li>
              </ul>
            </div>
          </div>
          <div class="card">
            <div class="card-body">
              <div class="tab-content">

                <div class="tab-pane show active" id="contactDetail">
                  <form id="contactForm" method="post" action="#">
                    <div class="text-center">
                      <h3 class="mb-2">Comencemos con la información básica.</h3>
                      <small class="text-muted">
                        Por favor, ingrese la información básica de la nómina.
                      </small>
                    </div>
                    <div class="row mt-4">

                      <div class="col">
                        <div class="row">
                          <div class="col-sm-6">
                            <div class="mb-3"><label class="form-label">Nombre de la nomina</label> <input type="text" class="form-control" placeholder="Enter First Name"></div>
                          </div>
                          <div class="col-sm-6">
                            <div class="mb-3">
                              <label class="form-label">Frecuencia de pago</label>
                              <select class="form-control" id="frecuencia_pago">
                                <option value="">Seleccione</option>
                                <option value="1">Semanal</option>
                                <option value="2">Quincenal</option>
                                <option value="3">Mensual</option>
                                <option value="4">Una vez al mes</option>
                              </select>
                              </div>
                          </div>
                          <div class="col-sm-12">
                            <div class="mb-3">
                              <label class="form-label">Tipo de nomina</label> 
                              <select class="form-control" id="tipo_nomina">
                                <option value="">Seleccione</option>
                                <option value="1">Normal</option>
                                <option value="2">Especial</option>
                              </select>
                              </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                  </form>
                  <div class="d-flex w-100 mt-3">
                    <div class="d-flex m-a">
                      <div class="previous me-2"><a href="javascript:void(0);" class="btn btn-secondary disabled">Regresar</a></div>
                      <div class="next" data-target-form="#contactDetailForm" role="presentation"><a href="#contactDetail" data-bs-toggle="tab" data-toggle="tab" aria-selected="true" role="tab" class="btn btn-secondary mt-3 mt-md-0">Siguiente</a></div>






                    </div>
                  </div>

                </div>
                <div class="tab-pane" id="jobDetail">
                  <form id="jobForm" method="post" action="#">
                    <div class="text-center">
                      <h3 class="mb-2">Tell me something about Home address</h3><small class="text-muted">Let us know your name and email address. Use an address you don't mind other users contacting you at</small>
                    </div>
                    <div class="row mt-4">
                      <div class="col-sm-6">
                        <div class="mb-3"><label class="form-label">Street Name</label> <input type="text" class="form-control" placeholder="Enter Street Name"></div>
                      </div>
                      <div class="col-sm-6">
                        <div class="mb-3"><label class="form-label">Street No</label> <input type="text" class="form-control" placeholder="Enter Street No"></div>
                      </div>
                      <div class="col-sm-6">
                        <div class="mb-3"><label class="form-label">City</label> <input type="text" class="form-control" placeholder="Enter City"></div>
                      </div>
                      <div class="col-sm-6">
                        <div class="mb-3"><label class="form-label">Country</label> <select class="form-select">
                            <option>Select Contry</option>
                            <option>India</option>
                            <option>Rusia</option>
                            <option>Dubai</option>
                          </select></div>
                      </div>
                    </div>
                    <div class="d-flex w-100 mt-3">
                      <div class="d-flex m-a">
                        <div class="previous me-2"><a href="javascript:void(0);" class="btn btn-secondary disabled">Regresar</a></div>
                        <div class="next"><a href="javascript:void(0);" class="btn btn-secondary mt-3 mt-md-0">Siguiente</a></div>
                      </div>
                    </div>
                  </form>
                </div>
                <div class="tab-pane" id="educationDetail">
                  <form id="educationForm" method="post" action="#">
                    <div class="text-center">
                      <h3 class="mb-2">Tell us about your education</h3><small class="text-muted">Let us know your name and email address. Use an address you don't mind other users contacting you at</small>
                    </div>
                    <div class="row">
                      <div class="col-md-12">
                        <div class="mb-3"><label class="form-label" for="schoolName">School Name</label> <input type="text" class="form-control" id="schoolName" placeholder="enter your school name"></div>
                      </div>
                      <div class="col-md-12">
                        <div class="mb-3"><label class="form-label" for="schoolLocation">School Location</label> <input type="text" class="form-control" id="schoolLocation" placeholder="enter your school location"></div>
                      </div>
                    </div>
                    <div class="d-flex w-100 mt-3">
                      <div class="d-flex m-a">
                        <div class="previous me-2"><a href="javascript:void(0);" class="btn btn-secondary disabled">Regresar</a></div>
                        <div class="next"><a href="javascript:void(0);" class="btn btn-secondary mt-3 mt-md-0">Siguiente</a></div>
                      </div>
                    </div>
                  </form>
                </div>
                <div class="tab-pane" id="finish">
                  <div class="row d-flex justify-content-center">
                    <div class="col-lg-6">
                      <div class="text-center"><i class="ph-duotone ph-gift f-50 text-danger"></i>
                        <h3 class="mt-4 mb-3">Thank you !</h3>
                        <div class="mb-3">
                          <div class="form-check d-inline-block"><input type="checkbox" class="form-check-input" id="customCheck1"> <label class="form-check-label" for="customCheck1">I agree with the Terms and Conditions</label></div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="d-flex w-100 mt-3">
                    <div class="d-flex m-a">
                      <div class="previous me-2"><a href="javascript:void(0);" class="btn btn-secondary disabled">Regresar</a></div>
                      <div class="next"><a href="javascript:void(0);" class="btn btn-secondary mt-3 mt-md-0">Siguiente</a></div>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>
        <!-- [ worldLow section ] end -->
        <!-- [ Recent Users ] end -->
      </div>
      <!-- [ Main Content ] end -->
    </div>
  </div>
  <!-- [ Main Content ] end -->
  <script src="../../src/assets/js/plugins/simplebar.min.js"></script>
  <script src="../../src/assets/js/plugins/bootstrap.min.js"></script>
  <script src="../../src/assets/js/fonts/custom-font.js"></script>
  <script src="../../src/assets/js/pcoded.js"></script>
  <script src="../../src/assets/js/plugins/feather.min.js"></script>
  <script src="../../src/assets/js/main.js"></script>
  <script>
    const url_back = '../../back/modulo_nomina/nom_grupos_back.php';
  </script>

</body>

</html>