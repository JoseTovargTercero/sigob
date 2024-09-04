import { nomTasaCard } from '../components/nom_tasa_card.js'

const d = document
export function validateTasaActual() {
  const tasaViewElement = d.getElementById('tasa-view')
  if (!tasaViewElement) return

  nomTasaCard({ elementToInsert: 'tasa-card-body' })

  return
}
