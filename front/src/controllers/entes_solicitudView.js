import {
  ejerciciosLista,
  validarEjercicioActual,
} from '../components/form_ejerciciosLista.js'
import {
  loadSolicitudEntesTable,
  validateSolicitudEntesTable,
} from './entes_solicitudTable.js'

const d = document
const w = window

export const validateSolicitudEntesView = () => {
  let ejercicioFiscal = ejerciciosLista({
    elementToInsert: 'ejercicios-fiscales',
  })

  validarEjercicioActual(ejercicioFiscal)

  validateSolicitudEntesTable(ejercicioFiscal ? ejercicioFiscal.id : null)

  d.addEventListener('click', async (e) => {
    if (e.target.id === 'solicitud-ente-registrar') {
      if (!ejercicioFiscal) {
        toastNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Seleccione un ejercicio fiscal',
        })
        return
      }
    }

    // if (e.target.dataset.validarid) {
    //   pre_solicitudGenerar_card({
    //     elementToInsert: 'solicitudes-dozavos-view',
    //     enteId: e.target.dataset.validarid,
    //     ejercicioId: ejercicioFiscal.id,
    //   })
    // }
    if (e.target.dataset.ejercicioid) {
      // QUITAR CARD SI SE CAMBIA EL AÑO FISCAL
      let formCard = d.getElementById('solicitud-ente-card')
      if (formCard) formCard.remove()

      loadSolicitudEntesTable(e.target.dataset.ejercicioid)
      ejercicioFiscal = await validarEjercicioActual({
        ejercicioTarget: e.target,
      })
    }
  })
}
