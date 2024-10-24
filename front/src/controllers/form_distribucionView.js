import { getFormPartidas } from '../api/partidas.js'
import { eliminarDistribucion } from '../api/pre_distribucion.js'
import { getSectores } from '../api/sectores.js'
import { form_distribucion_form_card } from '../components/form_distribucion_form_card.js'
import { form_distribucion_modificar_form_card } from '../components/form_distribucion_modificar_card.js'
import {
  ejerciciosLista,
  validarEjercicioActual,
} from '../components/form_ejerciciosLista.js'
import { confirmNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

import {
  deleteDistribucionRow,
  loadDistribucionTable,
  validateDistribucionTable,
} from './form_distribucionTable.js'
const d = document

export const validateDistribucionView = async () => {
  let btnNewElement = d.getElementById('partida-registrar')

  let ejercicioFiscal = await ejerciciosLista({
    elementToInsert: 'ejercicios-fiscales',
  })

  console.log(ejercicioFiscal)

  validateDistribucionTable({ partidas: ejercicioFiscal.distribucion_partidas })

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

      loadDistribucionTable(ejercicioFiscal.distribucion_partidas)
    }
    if (e.target.dataset.editarid) {
      let partidas = await getFormPartidas()

      console.log(partidas, ejercicioFiscal.partidas)
      form_distribucion_modificar_form_card({
        elementToInset: 'distribucion-view',
        partidas: partidas.fullInfo,
        distribucionPartidas: ejercicioFiscal.distribucion_partidas,
      })
    }

    if (e.target.dataset.eliminarid) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.delete,
        message: '¿Desea eliminar esta distribución?',
        successFunction: async function () {
          let res = await eliminarDistribucion(e.target.dataset.eliminarid)
          if (res.success) {
            let row = e.target.closest('tr')
            deleteDistribucionRow({ row })
          }
        },
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
