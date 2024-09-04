const d = document
export const nomTasaCard = ({ elementToInsert }) => {
  if (!elementToInsert) return
  let fecha = new Date()
  let fechaDeHoy = fecha.toLocaleDateString()
  let card = `<h5 class="card-title">Tasa actual: Dólar a Bolívares</h5>
  <p class="card-text">1 dólar estadounidense = <b class="fs-5">${'tasa del día'}</b> bolívares
      venezolanos al día de
      hoy
      ${fechaDeHoy}</p>
  <p class="card-text">Puedes obtener tipos de cambio entre dólares estadounidenses y
      bolívares venezolanos utilizando exchange-rates.org, que agrega datos de divisas en
      tiempo real de las fuentes más autorizadas.</p>`

  d.getElementById(elementToInsert).insertAdjacentHTML('beforeend', card)
}
