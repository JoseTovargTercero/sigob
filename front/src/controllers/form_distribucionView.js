import { form_distribucion_form_card } from '../components/form_distribucion_form_card.js'
import { form_distribucion_modificar_form_card } from '../components/form_distribucion_modificar_card.js'
import {
  ejerciciosLista,
  validarEjercicioActual,
} from '../components/form_ejerciciosLista.js'

import {
  loadDistribucionTable,
  validateDistribucionTable,
} from './form_distribucionTable.js'
const d = document

export const validateDistribucionView = async () => {
  let btnNewElement = d.getElementById('partida-registrar')

  let ejercicioFiscal = await ejerciciosLista({
    elementToInsert: 'ejercicios-fiscales',
  })

  validateDistribucionTable({ partidas: ejercicioFiscal.partidas })

  d.addEventListener('click', async (e) => {
    if (e.target.id === 'distribucion-registrar') {
      form_distribucion_form_card({
        elementToInset: 'distribucion-view',
        ejercicioFiscal,
      })
    }

    if (e.target.dataset.ejercicioid) {
      // QUITAR CARD SI SE CAMBIA EL AÑO FISCAL
      let formCard = d.getElementById('distribucion-form-card')
      if (formCard) formCard.remove()

      ejercicioFiscal = await validarEjercicioActual({
        ejercicioTarget: e.target,
      })

      loadDistribucionTable(ejercicioFiscal.partidas)
    }
    if (e.target.dataset.editarid) {
      form_distribucion_modificar_form_card({
        elementToInset: 'distribucion-view',
      })
    }
  })
}

// function validateEditButtons() {
//   d.getElementById('partida-registrar').removeAttribute('disabled')

//   let editButtons = d.querySelectorAll('[data-editarid][disabled]')

//   if (editButtons.length < 1) return

//   editButtons.forEach((btn) => {
//     if (btn.hasAttribute('disabled')) {
//       btn.removeAttribute('disabled')
//       btn.textContent = 'Editar'
//     }
//   })
// }
