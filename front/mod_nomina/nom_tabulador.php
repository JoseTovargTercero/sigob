<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document</title>
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <form action="" id="tabulator-primary-form">
      <input
        class="tabulator-input"
        type="text"
        name="nombre"
        id=""
        placeholder="NOMBRE DE TABULADOR"
      />
      <input
        class="tabulator-input"
        type="number"
        name="grados"
        id=""
        placeholder="GRADO"
      />
      <input
        class="tabulator-input"
        type="number"
        name="pasos"
        id=""
        placeholder="PASOS"
      />
      <input
        class="tabulator-input"
        type="number"
        name="aniosPasos"
        id=""
        placeholder="AÑOS POR PASO"
      />
      <button id="tabulator-btn">SIGUIENTE</button>
      <div id="tabulator-matrix"></div>
      <button id="tabulator-save-btn">GENERAR INFO</button>
    </form>
  </body>
  <script type="module" src="app.js"></script>
</html>
