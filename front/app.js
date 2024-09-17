import { validateCategoriaForm } from './src/controllers/categoriasForm.js'
import { loadCategoriasTable } from './src/controllers/categoriasTable.js'
import { validateDependenciaForm } from './src/controllers/dependenciasForm.js'
import { loadDependenciaTable } from './src/controllers/dependenciasTable.js'
import { validateEmployeeForm } from './src/controllers/empleadosForm.js'
import { validateEmployeeTable } from './src/controllers/empleadosTable.js'
import { loadRequestTableHistorico } from './src/controllers/peticionesHistoricoTable.js'
import { validateRequestForm } from './src/controllers/peticionesNominaForm.js'
// import { validatePayNomForm } from './src/controllers/pagarNominaForm.js'
// import { validateEmployeePayForm } from './src/controllers/peticionesNominaForm.js'
// import { validateRequestForm } from './src/controllers/peticionesNominaForm2.js'
import { validateRequestNomForm } from './src/controllers/peticionesNominaReview.js'
import { validateSolicitudesDozavosTable } from './src/controllers/pre_solicitudesDozavosTable.js'
import { validateSolicitudesDozavos } from './src/controllers/pre_solicitudesDozavosView.js'
import { loadRegconRequestTable } from './src/controllers/regcon_peticionesTable.js'
import { validateTabulatorForm } from './src/controllers/tabuladorForm.js'
import { validateTasaActual } from './src/controllers/tasaView.js'
import { validateModal } from './src/helpers/helpers.js'
const d = document

// const requestForm2 = d.getElementById('request-form2')
const payNomForm = d.getElementById('pay-nom-form')

const employeeTableElement = d.getElementById('employee-table')
const tabulatorTableElement = d.getElementById('tabulator-table')
d.addEventListener('DOMContentLoaded', (e) => {
  const tabulatorForm = d.getElementById('tabulator-primary-form')
  const employeeTable = d.getElementById('employee-table-view')
  const requestNomForm = d.getElementById('request-nom-form')
  const requestForm = d.getElementById('request-form')
  const requestHistorial = d.getElementById('request-historial')
  const dependenciaTable = d.getElementById('dependencia-table')
  const categoriaTable = d.getElementById('categoria-table')
  const tasaView = d.getElementById('tasa-view')
  const solicitudesDozavosView = d.getElementById('solicitudes-dozavos-view')

  if (tabulatorForm) {
    validateTabulatorForm({
      formId: 'tabulator-primary-form',
      secondaryFormId: 'tabulator-secundary-form',
      tabulatorInputClass: 'tabulator-input',
      matrixId: 'tabulator-matrix',
      matrixRowClass: 'tabulator-matrix-row',
      matrixCellClass: 'tabulator-matrix-cell',
      matrixInputsClass: 'tabulator-matrix-cell-input',
      btnId: 'tabulator-btn',
      btnSaveId: 'tabulator-save-btn',
      fieldList: {
        nombre: 'aaaaaaaaaa',
        pasos: 0,
        grados: 0,
        aniosPasos: 0,
        tabulador: [],
      },
      fieldListErrors: {
        nombre: {
          value: true,
          message: 'Introducir un nombre válido',
          type: 'text',
        },
        pasos: {
          value: true,
          message: 'Introduzca valor numérico',
          type: 'number',
        },
        grados: {
          value: true,
          message: 'Introduzca valor numérico',
          type: 'number',
        },
        aniosPasos: {
          value: true,
          message: 'Introduzca valor numérico',
          type: 'number',
        },
      },
    })
  }

  if (employeeTable) {
    validateEmployeeTable()
    validateEmployeeForm({
      employeeInputClass: 'employee-input',
      employeeSelectClass: 'employee-select',
      btnId: 'btn-employee-save',
      selectSearchInput: 'select-search-input',
      selectSearch: ['cargo'],
      btnAddId: 'add-dependency',
    })
    return
  }

  if (dependenciaTable) {
    loadDependenciaTable()
    validateDependenciaForm()
  }

  if (categoriaTable) {
    loadCategoriasTable()
    // VALIDAR FORMULARIO DE CATEGORIA
    validateCategoriaForm()
  }

  // if (requestForm) {
  //   validateEmployeePayForm({
  //     selectIdNomina: 'nomina',
  //     selectIdGrupo: 'grupo',
  //     selectIdFrecuencia: 'frecuencia',
  //     requestSelectContainerId: 'request-employee-container',
  //     showRequestGroupBtnId: 'show-request-group',
  //     formId: 'request-form',
  //   })
  // }

  if (requestForm) {
    validateRequestForm({
      btnNewRequestId: 'btn-new-request',
      requestTableId: 'request-table',
      requestFormId: 'request-form',
      requeFormInformationId: 'request-form-information',
      newRequestFormId: 'form-request-container',
      selectGrupoId: 'grupo',
      selectNominaId: 'nomina',
      selectFrecuenciaId: 'frecuencia',
      requestEmployeeListId: 'request-employee-list',
      btnNextId: 'btn-next',
      btnPreviusId: 'btn-previus',
    })
  }

  if (requestHistorial) {
    loadRequestTableHistorico()
  }

  if (requestNomForm) {
    loadRegconRequestTable()
    validateRequestNomForm()
  }

  if (tasaView) {
    validateTasaActual()
  }

  if (solicitudesDozavosView) {
    validateSolicitudesDozavos()
  }

  // if (payNomForm) {
  //   validatePayNomForm({
  //     selectId: 'select-correlativo',
  //     consultBtnId: 'consultar-correlativo',
  //     formId: 'pay-nom-form',
  //   })
  // }

  // if (employeeTableElement) {
  //   loadTable()
  // }

  // if (tabulatorTableElement) {
  //   loadTabulatorTable()
  // }
})

d.addEventListener('click', (e) => {
  if (e.target.id === 'btn-close')
    validateModal({
      e: e,
      btnId: e.target.id,
      modalId: 'modal-secondary-form-tabulator',
    })
})
