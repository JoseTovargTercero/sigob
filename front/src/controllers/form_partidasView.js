import { form_partida_form_card } from '../components/form_partidas_form_card.js'
import { validatePartidasTable } from './form_partidasTable.js'

const d = document

export const validatePartidasView = () => {
  validatePartidasTable()

  d.addEventListener('click', async (e) => {
    if (e.target.id === 'partida-registrar') {
      //   gastosRegistrarCointaner.classList.add('hide')
      form_partida_form_card({ elementToInsert: 'partidas-view' })
    }
  })
}
