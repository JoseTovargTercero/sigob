import {
  confirmarPeticionNomina,
  descargarNominaTxt,
  generarNominaTxt,
  getComparacionNomina,
  getPeticionesNomina,
} from '../api/peticionesNomina.js'
import { nomReportCard } from '../components/nom_report_card.js'
import { createComparationContainer } from '../components/regcon_comparation_container.js'
import { confirmNotification, validateInput } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document
const w = window

let fieldList = {
  'select-correlativo': '',
}

let fieldListErrors = {
  'select-correlativo': {
    value: true,
    message: 'Seleccione una nómina a consultar',
    type: 'text',
  },
}

let nominas = {}

export async function validatePayNomForm({ selectId, consultBtnId, formId }) {
  let requestInfo = await getPeticionesNomina()

  let selectCorrelativo = d.getElementById(selectId)
  let consultCorrelativo = d.getElementById(consultBtnId)
  let payNomForm = d.getElementById(formId)
  // let comparationContainer = d.getElementById('request-comparation-container')

  let selectValues = await requestInfo
    .map((el) => {
      if (Number(el.status) === 1) {
        nominas.correlativo = el.correlativo
        nominas.nombre_nomina = el.nombre_nomina
        return `<option value="${el.correlativo}">${el.correlativo} - ${el.nombre_nomina}</option>`
      }
    })
    .join('')

  selectCorrelativo.insertAdjacentHTML('beforeend', selectValues)

  selectCorrelativo.addEventListener('change', (e) => {
    fieldList = validateInput({
      target: e.target,
      fieldList,
      fieldListErrors,
      type: fieldListErrors[e.target.name].type,
    })
    // console.log(fieldList)
  })

  d.addEventListener('click', async (e) => {
    if (fieldList['select-correlativo'] === '') return

    if (e.target === consultCorrelativo) {
      let nomReportCardElement = d.getElementById('nom-report-card')
      if (nomReportCardElement) nomReportCardElement.remove()

      let result = requestInfo.find(
        (el) => el.correlativo === fieldList['select-correlativo']
      )

      payNomForm.insertAdjacentHTML(
        'beforeend',
        nomReportCard({
          data: result,
        })
      )
    }

    if (e.target.id === 'generar-txt') {
      // let descargatxt = await descargarNominaTxt(
      //   fieldList['select-correlativo']
      // )
      // console.log(descargatxt)
      // console.log(e.target.dataset.correlativo)
      // confirmNotification({
      //   type: NOTIFICATIONS_TYPES.send,
      //   successFunction: confirmarPeticionNomina,
      //   successFunctionParams: e.target.dataset.correlativo,
      //   othersFunctions: [resetInput],
      //   message: '¿Seguro de aceptar esta petición?',
      // })
    }
  })

  // function resetInput() {
  //   let comparationContainer = d.getElementById('request-comparation-container')

  //   if (comparationContainer) comparationContainer.remove()

  //   selectCorrelativo.value = ''
  // }
}

async function crearNominaTxt({ correlativo, identificador }) {
  let archivostxt2 = await generarNominaTxt({
    correlativo,
    identificador,
  })
}
