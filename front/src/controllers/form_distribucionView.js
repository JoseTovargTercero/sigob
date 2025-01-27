import { getFormPartidas } from '../api/partidas.js'
import { eliminarDistribucion } from '../api/pre_distribucion.js'
import { getSectores } from '../api/sectores.js'
import { form_distribucion_form_card } from '../components/form_distribucion_form_card.js'
import { form_distribucion_modificar_form_card } from '../components/form_distribucion_modificar_card.js'
import {
  ejerciciosLista,
  validarEjercicioActual,
} from '../components/form_ejerciciosLista.js'
import { confirmNotification, toastNotification } from '../helpers/helpers.js'
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

  validateDistribucionTable({
    partidas: ejercicioFiscal ? ejercicioFiscal.distribucion_partidas : [],
    ejercicioStatus: ejercicioFiscal ? ejercicioFiscal.status : false,
  })

  d.addEventListener('click', async (e) => {
    if (e.target.id === 'distribucion-registrar') {
      if (!ejercicioFiscal) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Seleccione un ejercicio fiscal',
        })
        return
      }

      if (Number(ejercicioFiscal.status) === 0) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Ejercicio fiscal cerrado',
        })
        return
      }

      form_distribucion_form_card({
        elementToInset: 'distribucion-view',
        ejercicioFiscal,
        recargarEjercicio: async function () {
          let ejercicioFiscalElement = d.querySelector(
            `[data-ejercicioid="${ejercicioFiscal.id}"]`
          )
          ejercicioFiscal = await validarEjercicioActual({
            ejercicioTarget: ejercicioFiscalElement,
          })
        },
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
      let sectores = await getSectores()

      console.log(partidas, ejercicioFiscal.partidas)
      form_distribucion_modificar_form_card({
        elementToInset: 'distribucion-view',
        partidas: partidas.fullInfo,
        sectores: sectores.fullInfo,
        ejercicioFiscal: ejercicioFiscal,
      })
    }

    if (e.target.dataset.eliminarid) {
      let ejercicioFiscalElement = d.querySelector(
        `[data-ejercicioid="${ejercicioFiscal.id}"]`
      )

      confirmNotification({
        type: NOTIFICATIONS_TYPES.delete,
        message: 'Se eliminará la distribución<br> <b>¿Está seguro?</b>',
        successFunction: async function () {
          let res = await eliminarDistribucion(e.target.dataset.eliminarid)
          if (res.success) {
            let row = e.target.closest('tr')
            deleteDistribucionRow({ row })
            ejercicioFiscal = await validarEjercicioActual({
              ejercicioTarget: ejercicioFiscalElement,
            })

            let formCard = d.getElementById('distribucion-form-card')
            if (formCard) formCard.remove()
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
