import {
  getEntePlanOperativo,
  getEntePlanOperativoId,
} from '../api/form_planOperativo.js'

import {
  ejerciciosLista,
  validarEjercicioActual,
} from '../components/form_ejerciciosLista.js'
import { form_planOperativo_card } from '../components/form_planOperativo_card.js'
import { toastNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import {
  loadPlanOperativo,
  validatePlanOperativoTable,
} from './form_planOperativoTable.js'

const d = document
const w = window

export const validatePlanOperativoView = async () => {
  let ejercicioFiscal = await ejerciciosLista({
    elementToInsert: 'ejercicios-fiscales',
  })

  validatePlanOperativoTable()

  loadPlanOperativo({
    id_ejercicio: ejercicioFiscal ? ejercicioFiscal.id : null,
  })

  if (!ejercicioFiscal) {
    toastNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: 'Seleccione o registre un ejercicio fiscal',
    })
    return
  }

  // let data = await getEntePlanOperativo(ejercicioFiscal.id)
  // console.log(data)

  d.addEventListener('click', async (e) => {
    if (e.target.id === 'plan-operativo-registrar') {
      if (!ejercicioFiscal) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Seleccione un ejercicio fiscal',
        })
        return
      }

      // entes_planOperativo_form_card({
      //   elementToInsert: 'plan-operativo-view',
      //   ejercicioId: ejercicioFiscal ? ejercicioFiscal.id : null,
      //   reset: function () {
      //     getEntePlanOperativos(ejercicioFiscal.id).then((data) => {
      //       form_planOperativo_card({
      //         elementToInsert: 'plan-operativo-view',
      //         data,
      //         closed: false,
      //       })
      //     })
      //   },
      // })
    }

    if (e.target.dataset.detalleid) {
      // CERRAR FORMULARIO

      scroll(0, 0)

      console.log()

      let data = await getEntePlanOperativoId(e.target.dataset.detalleid)

      form_planOperativo_card({
        elementToInsert: 'form-plan-operativo-view',
        data,
        closed: true,
      })
    }

    if (e.target.dataset.ejercicioid) {
      // QUITAR CARD SI SE CAMBIA EL AÑO FISCAL
      form_planOperativo_card({
        elementToInsert: 'form-plan-operativo-view',
        data: null,
        closed: true,
        close: true,
      })

      loadPlanOperativo({ id_ejercicio: e.target.dataset.ejercicioid })

      ejercicioFiscal = await validarEjercicioActual({
        ejercicioTarget: e.target,
      })
    }
  })
}
