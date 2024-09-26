import { form_partida_form_card } from '../components/form_partidas_form_card.js'
import { validatePartidasTable } from './form_partidasTable.js'

const d = document

export const validatePartidasView = () => {
  validatePartidasTable()

  let btnNewElement = d.getElementById('partida-registrar')

  d.addEventListener('click', async (e) => {
    if (e.target.id === 'partida-registrar') {
      form_partida_form_card({ elementToInsert: 'partidas-view', id: 1 })
    }
    if (e.target.dataset.close) {
      validateEditButtons()
    }
    if (e.target.dataset.editarid) {
      //   gastosRegistrarCointaner.classList.add('hide')
      e.target.textContent = 'Editando'
      e.target.setAttribute('disabled', true)
      btnNewElement.setAttribute('disabled', true)

      form_partida_form_card({
        elementToInsert: 'partidas-view',
        id: e.target.dataset.editarid,
      })
    }
  })
}

function validateEditButtons() {
  let editButtons = d.querySelectorAll('[data-editarid][disabled]')
  console.log(editButtons)

  editButtons.forEach((btn) => {
    if (btn.hasAttribute('disabled')) {
      btn.removeAttribute('disabled')
      btn.textContent = 'Editar'
    }
  })
}
