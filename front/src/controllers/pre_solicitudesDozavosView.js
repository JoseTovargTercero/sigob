import { getPreAsignacionEnte } from '../api/pre_entes.js'
import { getSolicitudDozavos } from '../api/pre_solicitudesDozavos.js'
import {
  ejerciciosLista,
  validarEjercicioActual,
} from '../components/form_ejerciciosLista.js'

import {
  pre_solicitudEnte_card,
  pre_solicitudGenerar_card,
} from '../components/pre_solicitudDozavoForm_card.js'
import { pre_solicitudDozavo_card } from '../components/pre_solicitudDozavo_card.js'
import { confirmNotification, toastNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import {
  loadSolicitudesDozavosTable,
  validateSolicitudesDozavosTable,
} from './pre_solicitudesDozavosTable.js'
const d = document
const w = window
export const validateSolicitudesDozavos = async () => {
  let ejercicioFiscal = await ejerciciosLista({
    elementToInsert: 'ejercicios-fiscales',
  })

  validateSolicitudesDozavosTable(ejercicioFiscal ? ejercicioFiscal.id : null)

  d.addEventListener('click', async (e) => {
    if (e.target.id === 'solicitud-registrar') {
      if (!ejercicioFiscal) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Seleccione un ejercicio fiscal',
        })
        return
      }

      pre_solicitudEnte_card({
        elementToInsert: 'solicitudes-dozavos-view',
        ejercicioId: ejercicioFiscal.id,
      })
    }

    if (e.target.dataset.validarid) {
      let asignacionEnte = await getPreAsignacionEnte(
        e.target.dataset.validarid,
        ejercicioFiscal.id
      )
      console.log(asignacionEnte)
      pre_solicitudGenerar_card({
        elementToInsert: 'solicitudes-dozavos-view',

        ejercicioId: ejercicioFiscal.id,
        asignacionEnte,
      })
    }

    if (e.target.dataset.detalleid) {
      let formCard = d.getElementById('solicitud-ente-card')
      if (formCard) formCard.remove()

      let solicitud = await getSolicitudDozavos(e.target.dataset.detalleid)
      if (!solicitud) return
      pre_solicitudDozavo_card({
        elementToInsert: 'solicitudes-dozavos-view',
        data: solicitud,
        reset: async function () {
          let ejercicioFiscalElement = d.querySelector(
            `[data-ejercicioid="${ejercicioFiscal.id}"]`
          )
          ejercicioFiscal = await validarEjercicioActual({
            ejercicioTarget: ejercicioFiscalElement,
          })

          loadSolicitudesDozavosTable(ejercicioFiscal.id)
        },
      })
    }

    if (e.target.dataset.ejercicioid) {
      // QUITAR CARD SI SE CAMBIA EL AÑO FISCAL
      let formCard = d.getElementById('solicitud-ente-card')
      if (formCard) formCard.remove()

      loadSolicitudesDozavosTable(e.target.dataset.ejercicioid)
      ejercicioFiscal = await validarEjercicioActual({
        ejercicioTarget: e.target,
      })
    }
  })
}
