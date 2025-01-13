import { generarReporte } from '../api/pre_reportes.js'
import {
  ejerciciosLista,
  validarEjercicioActual,
} from '../components/form_ejerciciosLista.js'
import {
  pre_reporteDocumento,
  pre_reportesLista,
} from '../components/pre_reportesLista.js'

import { confirmNotification, toastNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document
const w = window
export const validateReportesView = async () => {
  let ejercicioFiscal = await ejerciciosLista({
    elementToInsert: 'ejercicios-fiscales',
  })

  let reportesContainer = d.getElementById('reportes-container')

  pre_reportesLista({ elementToInsert: 'reportes-lista' })

  d.addEventListener('click', async (e) => {
    if (e.target.dataset.ejercicioid) {
      ejercicioFiscal = await validarEjercicioActual({
        ejercicioTarget: e.target,
      })
    }

    if (e.target.dataset.tabIds) {
      let items = d.querySelectorAll('[data-tab-ids]')

      items.forEach((a) => {
        if (a.classList.contains('active')) {
          a.classList.remove('active')
        }
      })

      e.target.classList.add('active')

      reportesContainer.innerHTML = ''

      pre_reporteDocumento({
        elementToInsert: reportesContainer.id,
        report: e.target.dataset.tabIds,
        ejercicioId: ejercicioFiscal.id,
      })
    }
  })
}
