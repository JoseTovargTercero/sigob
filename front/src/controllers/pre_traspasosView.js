import {
  ejerciciosLista,
  validarEjercicioActual,
} from '../components/form_ejerciciosLista.js'

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
    if (e.target.dataset.ejercicioid) {
      ejercicioFiscal = await validarEjercicioActual({
        ejercicioTarget: e.target,
      })
    }
  })
}
