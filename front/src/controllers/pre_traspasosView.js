import {
  ejerciciosLista,
  validarEjercicioActual,
} from '../components/form_ejerciciosLista.js'
import { pre_traspasosForm_card } from '../components/pre_traspasosForm_card.js'
import { toastNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

import { validateTraspasosTable } from './pre_traspasosTable.js'

const d = document
const w = window
export const validateTraspasosView = async () => {
  let ejercicioFiscal = await ejerciciosLista({
    elementToInsert: 'ejercicios-fiscales',
  })

  validateTraspasosTable({
    id_ejercicio: ejercicioFiscal ? ejercicioFiscal.id : null,
  })

  d.addEventListener('click', async (e) => {
    if (e.target.id === 'traspasos-registrar') {
      if (!ejercicioFiscal) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'No hay un ejercicio fiscal seleccionado',
        })
      }
      pre_traspasosForm_card({
        elementToInsert: 'traspasos-view',
        ejercicioFiscal,
      })
    }
    if (e.target.dataset.ejercicioid) {
      ejercicioFiscal = await validarEjercicioActual({
        ejercicioTarget: e.target,
      })
    }
  })
}
