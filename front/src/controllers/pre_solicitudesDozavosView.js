import {
  aceptarDozavo,
  getSolicitudesDozavos,
} from '../api/pre_solicitudesDozavos.js'
import {
  ejerciciosLista,
  validarEjercicioActual,
} from '../components/form_ejerciciosLista.js'
import { pre_identificarCompromiso } from '../components/pre_identificarCompromiso.js'
import {
  pre_solicitudEnte_card,
  pre_solicitudGenerar_card,
} from '../components/pre_solicitudDozavoForm_card.js'
import { pre_solicitudDozavo_card } from '../components/pre_solicitudDozavo_card.js'
import { confirmNotification } from '../helpers/helpers.js'
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

  validateSolicitudesDozavosTable()

  d.addEventListener('click', async (e) => {
    if (e.target.dataset.detalleid) {
      let formCard = d.getElementById('solicitud-ente-card')
      if (formCard) formCard.remove()

      let solicitud = await getSolicitudesDozavos(e.target.dataset.detalleid)
      pre_solicitudDozavo_card({
        elementToInsert: 'solicitudes-dozavos-view',
        data: solicitud,
      })
    }

    if (e.target.id === 'solicitud-registrar') {
      pre_solicitudEnte_card({
        elementToInsert: 'solicitudes-dozavos-view',
      })
    }

    if (e.target.dataset.validarid) {
      pre_solicitudGenerar_card({
        elementToInsert: 'solicitudes-dozavos-view',
        enteId: e.target.dataset.validarid,
        ejercicioId: ejercicioFiscal.id,
      })
    }

    if (e.target.dataset.rechazarid) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: '¿Desea rechazar esta solicitud?',
      })
    }

    if (e.target.dataset.confirmarid) {
      pre_identificarCompromiso({
        id: e.target.dataset.confirmarid,
        elementToInsert: 'solicitudes-dozavos-view',
        acceptFunction: async function (codigo) {
          let res = await aceptarDozavo(e.target.dataset.confirmarid, codigo)
          const modalElemet = d.getElementById('card-solicitud-dozavo')
          if (modalElemet) modalElemet.remove()
          return res
        },
        reset: async function () {
          let ejercicioFiscalElement = d.querySelector(
            `[data-ejercicioid="${ejercicioFiscal.id}"]`
          )
          ejercicioFiscal = await validarEjercicioActual({
            ejercicioTarget: ejercicioFiscalElement,
          })

          loadSolicitudesDozavosTable()
        },
      })
    }

    if (e.target.dataset.ejercicioid) {
      // QUITAR CARD SI SE CAMBIA EL AÑO FISCAL
      let formCard = d.getElementById('solicitud-ente-card')
      if (formCard) formCard.remove()

      loadSolicitudesDozavosTable()
      ejercicioFiscal = await validarEjercicioActual({
        ejercicioTarget: e.target,
      })
    }
  })
}
