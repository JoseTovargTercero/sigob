import { consultarCompromisos } from '../api/pre_compromisos.js'
import { generarReporte } from '../api/pre_reportes.js'
import {
  ejerciciosLista,
  validarEjercicioActual,
} from '../components/form_ejerciciosLista.js'
import {
  pre_compromisoDocumento,
  pre_compromisosLista,
} from '../components/pre_compromisosLista.js'
import {
  pre_reporteDocumento,
  pre_reportesLista,
} from '../components/pre_reportesLista.js'

const d = document
const w = window
export const validateCompromisosView = async () => {
  let ejercicioFiscal = await ejerciciosLista({
    elementToInsert: 'ejercicios-fiscales',
  })

  let reportesContainer = d.getElementById('reportes-container')

  pre_compromisosLista({ elementToInsert: 'reportes-lista' })

  pre_compromisoDocumento({
    elementToInsert: reportesContainer.id,
    report: false,
    ejercicioId: ejercicioFiscal.id,
  })

  d.addEventListener('click', async (e) => {
    if (e.target.dataset.ejercicioid) {
      ejercicioFiscal = await validarEjercicioActual({
        ejercicioTarget: e.target,
      })
    }

    if (e.target.dataset.tabIds) {
      let compromisos = await consultarCompromisos(ejercicioFiscal.id)
      console.log(compromisos)

      let items = d.querySelectorAll('[data-tab-ids]')

      items.forEach((a) => {
        if (a.classList.contains('active')) {
          a.classList.remove('active')
        }
      })

      e.target.classList.add('active')

      reportesContainer.innerHTML = ''

      pre_compromisoDocumento({
        elementToInsert: reportesContainer.id,
        report: e.target.dataset.tabIds,
        ejercicioId: ejercicioFiscal.id,
      })
    }
  })
}
