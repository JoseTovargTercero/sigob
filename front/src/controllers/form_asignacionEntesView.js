import { getEntesPlan } from '../api/form_entes.js'
import { form_asignacion_entes_form_card } from '../components/form_asignacion_entes_form_card.js'
import { confirmNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { validateAsignacionEntesTable } from './form_asignacionEntesTable.js'
import { validateDistribucionTable } from './form_distribucionTable.js'
const d = document

export const validateAsignacionEntesView = () => {
  validateAsignacionEntesTable()

  d.addEventListener('click', async (e) => {
    if (e.target.dataset.validarid) {
      let plan = await getEntesPlan(Number(e.target.dataset.validarid))

      form_asignacion_entes_form_card({
        elementToInset: 'asignacion-entes-view',
        plan: plan,
      })
    }
    if (e.target.id === 'asignacion-registrar') {
      // form_distribucion_entes_form_card({
      //   elementToInset: 'asignacion-entes-view',
      // })
    }
    // if (e.target.id === 'partida-registrar') {
    //   btnNewElement.setAttribute('disabled', true)
    //   form_partida_form_card({ elementToInsert: 'partidas-view' })
    // }

    // if (e.target.dataset.eliminarid) {
    //   confirmNotification({
    //     type: NOTIFICATIONS_TYPES.send,
    //     message: '¿Desea eliminar esta partida?',
    //     successFunction: async function () {
    //       let row = e.target.closest('tr')
    //       eliminarPartida(e.target.dataset.eliminarid)
    //       deletePartidaRow({ row })
    //       if (d.getElementById('partida-form-card')) {
    //         location.reload()
    //       }
    //     },
    //   })
    // }

    // if (e.target.dataset.editarid) {
    //   scroll(0, 0)
    //   //   gastosRegistrarCointaner.classList.add('hide')
    //   e.target.textContent = 'Editando'
    //   e.target.setAttribute('disabled', true)
    //   btnNewElement.setAttribute('disabled', true)

    //   form_partida_form_card({
    //     elementToInsert: 'partidas-view',
    //     id: e.target.dataset.editarid,
    //   })
    // }
  })
}

function validateEditButtons() {
  d.getElementById('partida-registrar').removeAttribute('disabled')

  let editButtons = d.querySelectorAll('[data-editarid][disabled]')

  if (editButtons.length < 1) return

  editButtons.forEach((btn) => {
    if (btn.hasAttribute('disabled')) {
      btn.removeAttribute('disabled')
      btn.textContent = 'Editar'
    }
  })
}
