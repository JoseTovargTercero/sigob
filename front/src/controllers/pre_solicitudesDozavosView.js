import { getSolicitudesDozavos } from '../api/pre_solicitudesDozavos.js'
import { pre_solicitudDozavo_card } from '../components/pre_solicitudDozavo_card.js'
import { validateSolicitudesDozavosTable } from './pre_solicitudesDozavosTable.js'
const d = document
const w = window
export const validateSolicitudesDozavos = async () => {
  validateSolicitudesDozavosTable()

  d.addEventListener('click', async (e) => {
    if (e.target.dataset.detalleid) {
      let solicitud = await getSolicitudesDozavos(e.target.dataset.detalleid)
      pre_solicitudDozavo_card({
        elementToInsert: 'solicitudes-dozavos-view',
        data: solicitud,
      })
    }
  })
}
