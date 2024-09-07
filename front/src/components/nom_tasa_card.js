const d = document
export const nomTasaCard = ({ elementToInsert, tasaDelDia }) => {
  if (!elementToInsert) return

  if (d.getElementById('tasa-card-body'))
    d.getElementById('tasa-card-body').innerHTML = ''

  let { descripcion, simbolo, valor } = tasaDelDia
  let fecha = new Date()
  let fechaDeHoy = fecha.toLocaleDateString()
  let card = `
  <div class="card-body slide-up-animation" id="tasa-card-body">
    <h5 class="card-title">Tasa actual: Dólar a Bolívares</h5>
    <p class="card-text">${descripcion} = <b class="fs-5">${valor}</b> bolívares
        venezolanos al día de
        hoy
        ${fechaDeHoy}</p>

        <div class="card-footer">
  <h5 class="mb text-center">¿Es incorrecto? Actualice por favor:</h5>
  <div class="d-flex justify-content-center gap-2">
      <button class="btn btn-secondary" id="tasa-actualizar-manual">Manual</button>
      <button class="btn btn-primary" id="tasa-actualizar-automatico">Automatico</button>
  </div>


</div>
  </div>
`

  d.getElementById(elementToInsert).outerHTML = card
}
