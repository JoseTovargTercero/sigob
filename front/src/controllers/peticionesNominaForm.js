import { getEmployeeData, updateEmployeeStatus } from '../api/empleados.js'
import {
  calculoNomina,
  descargarNominaTxt,
  enviarCalculoNomina,
  getComparacionNomina,
  getComparacionNomina2,
  getNominas,
  getPeticionesNomina,
} from '../api/peticionesNomina.js'
import {
  loadEmployeeList,
  nom_empleados_list_card,
} from '../components/nom_empleados_list_card.js'
import { nomReportCard } from '../components/nom_report_card.js'
import { createComparationContainer } from '../components/regcon_comparation_container.js'
import { nom_comparation_employee } from '../components/regcon_comparation_employee.js'
import {
  closeModal,
  confirmNotification,
  validateInput,
} from '../helpers/helpers.js'
import { FRECUENCY_TYPES, NOTIFICATIONS_TYPES } from '../helpers/types.js'
import { createTable, employeePayTableHTML } from './peticionesNominaTable.js'
import { loadRequestTable } from './peticionesTable.js'

const d = document

let fieldList = {
  nomina: '',
  grupo: '',
  frecuencia: '',
  identificador: '',
}

let fieldListErrors = {
  grupo: {
    value: true,
    message: 'Seleccione un grupo de nómina',
    type: 'number',
  },
  nomina: {
    value: true,
    message: 'Seleccione una nómina',
    type: 'text',
  },
  identificador: {
    value: true,
    message: 'Seleccione frecuencia a pagar',
    type: 'text',
  },
}

let employeeNewStatus = []

let nominas

let formFocus = 1

let calculoInformacion

export async function validateRequestForm({
  btnNewRequestId,
  requestFormId,
  requestTableId,
  newRequestFormId,
  selectNominaId,
  selectGrupoId,
  selectFrecuenciaId,
  btnNextId,
  btnPreviusId,
}) {
  loadRequestTable()
  let requestForm = d.getElementById(requestFormId)
  let requestTable = d.getElementById(requestTableId)
  let newRequestForm = d.getElementById(newRequestFormId)
  let requestFormInformation = d.getElementById('request-form-information')
  let requestFormInformationBody = d.getElementById(
    'request-form-information-body'
  )
  let selectNomina = d.getElementById(selectNominaId)
  let selectGrupo = d.getElementById(selectGrupoId)
  let selectFrecuencia = d.getElementById(selectFrecuenciaId)

  let btnNext = d.getElementById(btnNextId)
  let btnPrevius = d.getElementById(btnPreviusId)
  let requestStepPart1 = d.getElementById('request-step-1')
  let requestStepPart2 = d.getElementById('request-step-2')
  let requestStepPart3 = d.getElementById('request-step-3')

  d.addEventListener('change', async (e) => {
    if (e.target.dataset.employeeid) {
      let id = e.target.dataset.employeeid
      let defaultValue = e.target.dataset.defaultvalue
      let cedula = e.target.dataset.cedula
      let nombres = e.target.dataset.nombres

      let oldValueIndex = employeeNewStatus.findIndex((el) => el.id === id)
      if (e.target.value === defaultValue) {
        employeeNewStatus = employeeNewStatus.filter((el) => el.id !== id)
        console.log(employeeNewStatus)
        return
      }

      if (employeeNewStatus.some((el) => el.id === id)) {
        employeeNewStatus.splice(oldValueIndex, 1, {
          id,
          value: e.target.value,
          cedula,
          nombres,
          defaultValue,
        })
      } else {
        employeeNewStatus.push({
          id,
          value: e.target.value,
          cedula,
          nombres,
          defaultValue,
        })
      }
      console.log(employeeNewStatus)
    }

    if (e.target === selectGrupo) {
      fieldList = validateInput({
        target: e.target,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[e.target.name].type,
      })

      nominas = await getNominas(e.target.value)
      console.log(nominas)

      selectNomina.innerHTML = ''

      if (nominas.length > 0)
        nominas.forEach((nomina) => {
          let option = `<option value="${nomina.nombre}">${
            nomina.nombre || 'Grupo de nómina vacío'
          }</option>`

          selectNomina.insertAdjacentHTML('beforeend', option)
        })
      else
        selectNomina.insertAdjacentHTML(
          'beforeend',
          `<option value="">Grupo de nómina vacío</option>`
        )
    }

    if (e.target === selectNomina) {
      if (!e.target.value) return
      fieldList.nomina = e.target.value

      fieldList.frecuencia = nominas.find(
        (nomina) => nomina.nombre === e.target.value
      ).frecuencia

      console.log(fieldList.frecuencia)

      selectFrecuencia.innerHTML = ''

      let identificadorOpciones = ''

      switch (fieldList.frecuencia) {
        case '1':
          FRECUENCY_TYPES[fieldList.frecuencia].forEach(
            (identificadorNomina, index) => {
              identificadorOpciones += `<option value='${identificadorNomina}'>Semana ${
                index + 1
              }</option>`
            }
          )
          break
        case '2':
          FRECUENCY_TYPES[fieldList.frecuencia].forEach(
            (identificadorNomina, index) => {
              identificadorOpciones += `<option value='${identificadorNomina}'>Quincena ${
                index + 1
              }</option>`
            }
          )
          break
        case '3':
          FRECUENCY_TYPES[fieldList.frecuencia].forEach(
            (identificadorNomina) => {
              identificadorOpciones += `<option value='${identificadorNomina}'>Mensual</option>`
            }
          )
          break
        case '4':
          FRECUENCY_TYPES[fieldList.frecuencia].forEach(
            (identificadorNomina) => {
              identificadorOpciones += `<option value='${identificadorNomina}'>Mensual</option>`
            }
          )
          break

        default:
          break
      }
      selectFrecuencia.insertAdjacentHTML('beforeend', identificadorOpciones)
    }
    if (e.target === selectFrecuencia) {
      if (!fieldList.nomina)
        return confirmNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Elija una nomina.',
        })

      if (!fieldList.grupo)
        return confirmNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: 'Elija un grupo de nomina.',
        })

      // Mostrar contenedor de información
      requestFormInformation.classList.remove('hide')

      fieldList.identificador = e.target.value

      let result = await confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        successFunction: async function () {
          console.log('CONFIRMADOOO')

          let employeePayTableCard = d.getElementById(
            'request-employee-table-card'
          )
          let card = d.getElementById('employee-new-status-card')
          let requestComparationContainer = d.getElementById(
            'request-comparation-container'
          )
          if (employeePayTableCard) employeePayTableCard.remove()
          if (card) card.remove()
          if (requestComparationContainer) requestComparationContainer.remove()

          calculoInformacion = await calculoNomina({
            nombre: fieldList.nomina,
            identificador: fieldList.identificador,
          })

          let nominaMapped = { ...calculoInformacion }

          nominaMapped.informacion_empleados =
            nominaMapped.informacion_empleados.map((el) => {
              delete el.aportes
              delete el.deducciones
              delete el.asignaciones

              return el
            })

          console.log(nominaMapped)
          let columns = Object.keys(nominaMapped.informacion_empleados[0])

          // Insertar tabla en formulario
          requestFormInformationBody.insertAdjacentHTML(
            'beforeend',
            employeePayTableHTML({ nominaData: nominaMapped, columns })
          )
          createTable({ nominaData: nominaMapped, columns })

          toast_s('success', 'Se ha realizado el cálculo')
        },
        message: `Está seguro de realizar el cálculo de la nomina ${fieldList.nomina.toLocaleUpperCase()} con frecuencia ${fieldList.identificador.toLocaleUpperCase()}`,
      })

      if (!result) {
        selectFrecuencia.value = ''
        fieldList.identificador = ''
      }
    }
  })

  d.addEventListener('click', async (e) => {
    if (e.target.id === 'btn-show-request') {
      e.preventDefault()
      let peticiones = await getPeticionesNomina()
      let peticion = peticiones.find(
        (el) => el.correlativo === e.target.dataset.correlativo
      )

      fieldList.frecuencia = peticion.frecuencia
      let reportCard = d.getElementById('modal-report')
      if (reportCard) reportCard.remove()

      requestForm.insertAdjacentHTML(
        'beforeend',
        nomReportCard({ data: peticion })
      )
    }

    if (e.target.id === 'btn-close-report') {
      let reportCard = d.getElementById('modal-report')
      reportCard.remove()
    }

    if (e.target.id === 'generar-txt') {
      let descargatxt = await descargarNominaTxt({
        identificador: e.target.dataset.identificador,
        correlativo: e.target.dataset.correlativo,
      })
    }

    if (e.target.id === btnNewRequestId) {
      if (e.target.classList.contains('active')) {
        e.target.classList.remove('active')

        e.target.textContent = 'Nueva petición'
        requestTable.classList.remove('hide')
        newRequestForm.classList.add('hide')
        requestFormInformation.classList.add('hide')

        location.reload()
      } else {
        e.target.classList.add('active')

        e.target.textContent = 'Cancelar petición'
        requestTable.classList.add('hide')
        newRequestForm.classList.remove('hide')
        // requestFormInformation.classList.remove('hide')
      }
    }

    if (e.target.id === 'btn-close-employee-list-card') {
      closeModal({ modalId: 'modal-employee-list' })
    }

    if (e.target.id === 'btn-confirm-list') {
      confirmNotification({
        message:
          'Al guardar los cambios se realizará el cálculo nuevamente ¿Desea continuar?',
        type: NOTIFICATIONS_TYPES.send,
        successFunction: async () => {
          let res = await updateEmployeeStatus({ data: employeeNewStatus })

          if (res.status === 'success') {
            toast_s('success', 'Se han actualizado los empleados')

            let card = d.getElementById('employee-new-status-card')

            if (card) {
              card.remove()
              toast_s('success', 'Se ha actualizado la tabla')
            } else {
              toast_s('success', 'Tabla con nuevos status añadida')
            }

            requestFormInformationBody.insertAdjacentHTML(
              'afterbegin',
              empleadosModificados(employeeNewStatus)
            )
            closeModal({ modalId: 'modal-employee-list' })
          } else {
            toast_s(
              'error',
              'Error al status de empleado. No se realizará el cálculo'
            )
            closeModal({ modalId: 'modal-employee-list' })
            return
          }

          // REALIZAR CÁLCULO LUEGO DE ACTUALIZAR EMPLEADO
          console.log('CONFIRMADOOO')

          let employeePayTableCard = d.getElementById(
            'request-employee-table-card'
          )
          if (employeePayTableCard) employeePayTableCard.remove()

          calculoInformacion = await calculoNomina({
            nombre: fieldList.nomina,
            identificador: fieldList.identificador,
          })

          let nominaMapped = { ...calculoInformacion }

          nominaMapped.informacion_empleados =
            nominaMapped.informacion_empleados.map((el) => {
              delete el.aportes
              delete el.deducciones
              delete el.asignaciones

              return el
            })

          console.log(nominaMapped)
          let columns = Object.keys(nominaMapped.informacion_empleados[0])

          // Insertar tabla en formulario
          requestFormInformationBody.insertAdjacentHTML(
            'beforeend',
            employeePayTableHTML({ nominaData: nominaMapped, columns })
          )
          createTable({ nominaData: nominaMapped, columns })

          toast_s('success', 'Se ha realizado el cálculo')
        },
      })

      // confirmNotification({
      //   type: NOTIFICATIONS_TYPES.send,
      //   successFunction: async function () {
      //     let res = await updateEmployeeStatus({ data: employeeNewStatus })

      //     if (res.status === 'success') {
      //       toast_s('success', 'Se han actualizado los empleados')
      //     }

      //     requestFormInformationBody.insertAdjacentHTML(
      //       'afterbegin',
      //       empleadosModificados(employeeNewStatus)
      //     )
      //   },
      // })
    }

    if (e.target.id === 'btn-send-request') {
      confirmNotification({
        type: NOTIFICATIONS_TYPES.send,
        message: 'Deseas realizar esta petición?',
        successFunction: enviarCalculoNomina,
        successFunctionParams: calculoInformacion,
        othersFunctions: [
          function () {
            location.reload()
          },
        ],
      })
    }
    if (e.target.id === 'btn-next') {
      scroll(0, 0)

      if (formFocus === 1) {
        if (Object.values(fieldList).some((el) => !el)) {
          toast_s(
            'error',
            'Advertencia: Seleccione todos los campos para realizar el cálculo'
          )
          return
        }

        requestStepPart1.classList.add('hide')
        requestStepPart2.classList.remove('hide')

        // Deshabilitar boton
        btnPrevius.removeAttribute('disabled')
        formFocus++

        validateNavPill()
        return
      }

      if (formFocus === 2) {
        // INSERTAR DATOS DE ASIGNACIONES, APORTES, DEDUCCIONES

        // ¡¡¡¡¡¡¡MODIFICAR PARA QUE SE REALICE LA PETICIÓN CON EL NOMBRE E IDENTIFICADOR
        let data = {}
        data.registro_anterior = await getComparacionNomina2({
          nombre_nomina: calculoInformacion.nombre_nomina,

          confirmBtn: false,
        })

        data.registro_actual = {
          asignaciones: calculoInformacion.suma_asignaciones,
          deducciones: calculoInformacion.suma_deducciones,
          aportes: calculoInformacion.suma_aportes,
          empleados: calculoInformacion.empleados,
          status: 'En proceso',
          total_pagar: calculoInformacion.total_pagar,
          nombre_nomina: calculoInformacion.nombre_nomina,
        }

        let requestComparationContainer = d.getElementById(
          'request-comparation-container'
        )
        if (requestComparationContainer) requestComparationContainer.remove()

        requestFormInformationBody.insertAdjacentHTML(
          'afterbegin',
          createComparationContainer({ data })
        )
        let tablaDiferencia = await nom_comparation_employee({
          anterior: data.registro_anterior.empleados,
          actual: data.registro_actual.empleados,
          obtenerEmpleado: getEmployeeData,
        })

        requestStepPart2.classList.add('hide')
        requestStepPart3.classList.remove('hide')

        //  Habilitar
        btnNext.setAttribute('disabled', '')

        formFocus++

        validateNavPill()
        return
      }
    }

    if (e.target.id === 'btn-previus') {
      scroll(0, 0)
      if (formFocus === 3) {
        requestStepPart2.classList.remove('hide')
        requestStepPart3.classList.add('hide')

        //  Habilitar
        btnNext.removeAttribute('disabled')
        formFocus--
        validateNavPill()
        return
      }

      if (formFocus === 2) {
        requestStepPart2.classList.add('hide')
        requestStepPart1.classList.remove('hide')

        // Deshabilitar
        btnPrevius.setAttribute('disabled', '')
        formFocus--
        validateNavPill()
        return
      }
    }

    if (e.target.id === 'show-employee-list') {
      let modalEmployeeList = d.getElementById('modal-employee-list')
      if (modalEmployeeList) modalEmployeeList.remove()
      newRequestForm.insertAdjacentHTML('afterbegin', nom_empleados_list_card())

      console.log(calculoInformacion.informacion_empleados)

      loadEmployeeList({
        listaEmpleados: calculoInformacion.informacion_empleados,
      })
    }
    // console.log(formFocus)
  })
}

function validateNavPill() {
  d.querySelectorAll(`[data-part]`).forEach((navPill) => {
    navPill.classList.remove('active')
  })
  d.querySelectorAll(`[data-part="part${formFocus}"]`)[0].classList.add(
    'active'
  )
}

function empleadosModificados(listaEmpleados) {
  let tr = ''

  listaEmpleados.forEach((el) => {
    tr += `
      <tr>
        <td>${el.cedula}</td>
        <td>${el.nombres}</td>
        <td>${el.defaultValue}</td>
        <td>${el.value}</td>
      </tr>
    `
  })
  return ` <div
      class='card d-flex flex-row overflow-auto'
      id='employee-new-status-card'
    >
      <div class='card-header d-flex flex-column align-items-center justify-content-center'>
        <h5 class='mb-0'>Información de empleados</h5>
        <small class='text-muted m-0'>
         Últimos cambios de los empleados
        </small>
      
      </div>
      <div
        class='card-body'
        style='
      max-height: 400px;
      overflow: auto;
  '
      >
        <table
          class='table'
          style='width: 100%; min-width: 18rem; max-height: 400px'
          id='employee-change-list'
        >
          <thead>
            <tr>
              <th class='table-warning'>
                <i>Cedula</i>
              </th>
              <th class=''>Nombre</th>
              <th class=''>Anterior</th>
              <th class=''>Nuevo</th>
            </tr>
          </thead>
          <tbody>${tr}</tbody>
        </table>
      </div>
    </div>`
}
