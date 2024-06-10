import { getPeticionesNomina } from '../api/peticionesNomina.js'
import { validateInput } from '../helpers/helpers.js'

const d = document
const w = window

let fieldList = {
  consultar_nomina: '',
  grupo: '',
}

let fieldListErrors = {
  consultar_nomina: {
    value: true,
    message: 'Seleccione una nómina a consultar',
    type: 'text',
  },
}

let nominas = {}

export async function validateRequestNomForm({ selectId }) {
  let selectNom = d.getElementById(selectId)
  let requestInfo = await getPeticionesNomina()

  let selectValues = await requestInfo
    .map((el) => {
      nominas.correlativo = el.correlativo
      nominas.nombre_nomina = el.nombre_nomina
      return `<option value="${el.correlativo}">${el.correlativo} - ${el.nombre_nomina}</option>`
    })
    .join('')

  selectNom.insertAdjacentHTML('beforeend', selectValues)

  selectNom.addEventListener('change', (e) => {})
}
