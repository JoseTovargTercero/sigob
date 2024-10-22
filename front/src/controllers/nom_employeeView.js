import { getCategorias } from '../api/categorias.js'
import { getDependencias } from '../api/dependencias.js'
import {
  getBankData,
  getEmployeeByCedula,
  getEmployeeData,
  getJobData,
  getProfessionData,
  sendEmployeeData,
  updateRequestEmployeeData,
} from '../api/empleados.js'
import { consultarPartida, getPartidas } from '../api/partidas.js'
import { nom_categoria_form_card } from '../components/nom_categoria_form_card.js'
import { nomCorrectionAlert } from '../components/nom_correcion_alert.js'
import { nom_dependencia_form_card } from '../components/nom_dependencia_form_card.js'
import { employeeCard } from '../components/nom_empleado_card.js'
import { nom_empleados_form_card } from '../components/nom_empleados_form_card.js'
import {
  closeModal,
  confirmNotification,
  openModal,
  toastNotification,
  validateInput,
  validateModal,
} from '../helpers/helpers.js'
import { ALERT_TYPES, NOTIFICATIONS_TYPES } from '../helpers/types.js'
import {
  confirmDeleteEmployee,
  validateEmployeeTable,
} from './empleadosTable.js'
const d = document

export const validateEmployeeView = async () => {
  validateEmployeeTable()

  d.addEventListener('click', async (e) => {
    if (e.target.classList.contains('btn-delete')) {
      let fila = e.target.closest('tr')
      console.log(fila)
      confirmDeleteEmployee({
        id: e.target.dataset.id,
        row: fila,
      })
    }

    if (e.target.classList.contains('btn-view')) {
      employeeCard({
        id: e.target.dataset.id,
        elementToInsert: 'employee-table-view',
      })
    }

    if (e.target.id === 'btn-close-employee-card') {
      d.getElementById('modal-employee').remove()
    }

    if (e.target.classList.contains('btn-edit')) {
      nom_empleados_form_card({
        elementToInset: 'employee-table-view',
        id: e.target.dataset.id,
      })
    }

    if (e.target.id === 'btn-employee-form-open') {
      nom_empleados_form_card({ elementToInset: 'employee-table-view' })
    }

    if (e.target.id === 'add-dependency') {
      nom_dependencia_form_card({
        elementToInsert: 'modal-employee-form',
        reloadSelect: loadDependencias,
      })
      //   openModal({ modalId: 'modal-dependency' })
    }

    if (e.target.id === 'add-category') {
      nom_categoria_form_card({
        elementToInsert: 'modal-employee-form',
        reloadSelect: loadCategorias,
      })
    }
  })
}
