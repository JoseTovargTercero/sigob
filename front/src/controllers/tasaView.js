import {
  actualizarTasa,
  crearTasa,
  obtenerTasa,
  obtenerHistorialTasa,
} from '../api/tasa..js'
import { nomTasaCard } from '../components/nom_tasa_card.js'
import { toastNotification } from '../helpers/helpers.js'
import { inicializarTasaTable, loadTasaTable } from './tasaTable.js'

const d = document
export async function validateTasaActual() {
  const tasaViewElement = d.getElementById('tasa-view')
  if (!tasaViewElement) return

  inicializarTasaTable()

  let tasaCreada
  let tasaDelDia

  tasaDelDia = await obtenerTasa()
  if (!tasaDelDia) {
    tasaCreada = crearTasa()
    nomTasaCard({ elementToInsert: 'tasa-card-body', tasaDelDia: tasaCreada })
  } else {
    nomTasaCard({ elementToInsert: 'tasa-card-body', tasaDelDia: tasaDelDia })
  }

  d.addEventListener('click', async (e) => {
    if (e.target.id === 'tasa-actualizar-manual') {
      let actualizar = await actualizarTasa({ informacion: { valor: 1000 } })
      if (!actualizar || actualizar.error) return

      let tasaActualizada = await obtenerTasa()

      loadTasaTable()

      nomTasaCard({
        elementToInsert: 'tasa-card-body',
        tasaDelDia: tasaActualizada,
      })
    }
    if (e.target.id === 'tasa-actualizar-automatico') {
      let actualizar = await actualizarTasa({ informacion: false })
      if (!actualizar || actualizar.error) return

      let tasaActualizada = await obtenerTasa()

      loadTasaTable()

      nomTasaCard({
        elementToInsert: 'tasa-card-body',
        tasaDelDia: tasaActualizada,
      })
    }
  })

  return
}
