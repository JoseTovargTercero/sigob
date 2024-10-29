import {
  ejerciciosLista,
  validarEjercicioActual,
} from '../components/form_ejerciciosLista.js'
import { confirmNotification, toastNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'
import {
  loadGobernacionTable,
  validateGobernacionTable,
} from './form_informacionTables.js'

const d = document

export const validateGobernacionView = async () => {
  let btnNewElement = d.getElementById('gobernacion-registrar')

  validateGobernacionTable()

  d.addEventListener('click', async (e) => {
    if (e.target.id === 'distribucion-registrar') {
      toastNotification({ type: NOTIFICATIONS_TYPES.done, message: 'TEST' })
    }

    if (e.target.dataset.editarid) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: 'editartest',
      })

      loadGobernacionTable()
    }

    if (e.target.dataset.eliminarid) {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.delete,
        message: '¿Desea eliminar esta distribución?',
        successFunction: async function () {},
      })
    }
  })
}

// function validateEditButtons() {
//   d.getElementById('partida-registrar').removeAttribute('disabled')

//   let editButtons = d.querySelectorAll('[data-editarid][disabled]')

//   if (editButtons.length < 1) return

//   editButtons.forEach((btn) => {
//     if (btn.hasAttribute('disabled')) {
//       btn.removeAttribute('disabled')
//       btn.textContent = 'Editar'
//     }
//   })
// }
