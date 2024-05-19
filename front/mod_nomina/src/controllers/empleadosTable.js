import { deleteEmployee, getEmployeesData } from '../api/empleados.js'
import { confirmNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

const d = document
const w = window

const tableLanguage = {
  decimal: '',
  emptyTable: 'No hay datos disponibles en la tabla',
  info: 'Mostrando _START_ a _END_ de _TOTAL_ entradas',
  infoEmpty: 'Mostrando 0 a 0 de 0 entradas',
  infoFiltered: '(filtrado de _MAX_ entradas totales)',
  infoPostFix: '',
  thousands: ',',
  lengthMenu: 'Mostrar _MENU_',
  loadingRecords: 'Cargando...',
  processing: '',
  search: 'Buscar:',
  zeroRecords: 'No se encontraron registros coincidentes',
  paginate: {
    first: 'Primera',
    last: 'Última',
    next: 'Siguiente',
    previous: 'Anterior',
  },
  aria: {
    orderable: 'Ordenar por esta columna',
    orderableReverse: 'Orden inverso de esta columna',
  },
}

let employeeTable = new DataTable('#employee-table', {
  responsive: true,
  scrollY: 300,
  language: tableLanguage,
  layout: {
    topEnd: function () {
      let toolbar = document.createElement('div')
      toolbar.innerHTML = `<a class="btn btn-primary"
      href="nom_empleados_registrar">Registrar Personal</a>`
      return toolbar
    },
    topStart: { search: { placeholder: 'Buscar...' } },
    bottomStart: 'info',
    bottomEnd: 'paging',
  },
  columns: [
    { data: 'nombres', width: '10%' },
    { data: 'cedula', width: '10%' },
    { data: 'dependencia', width: '10%' },
    { data: 'nomina', width: '10%' },
    { data: 'acciones', width: '100%' },
  ],
})

const loadTable = async () => {
  employeeTable.clear()

  let empleados = await getEmployeesData()

  let data = empleados.map((empleado) => {
    return {
      nombres: empleado.nombres,
      cedula: empleado.cedula,
      dependencia: empleado.dependencia,
      nomina: empleado.tipo_nomina,
      acciones: `
      <button class="btn btn-info btn-sm btn-view" data-id="${empleado.id_empleado}">MOSTRAR</button>
      <button class="btn btn-warning btn-sm btn-edit" data-id="${empleado.id_empleado}">EDITAR</button>
      <button class="btn btn-danger btn-sm btn-delete" data-id="${empleado.id_empleado}">ELIMINAR</button>`,
    }
  })

  employeeTable.rows.add(data).draw()

  // employeeTable.rows.delete({
  //   buttons: [
  //     {
  //       label: 'Cancel',
  //       fn: function () {
  //         this.close()
  //       },
  //     },
  //     'Delete',
  //   ],
  // })
}

export const confirmDeleteEmployee = ({ id }) => {
  confirmNotification({
    type: NOTIFICATIONS_TYPES.delete,
    successFunction: deleteEmployee,
    successFunctionParams: id,
  })
}
d.addEventListener('click', (e) => {
  if (e.target.classList.contains('btn-delete')) {
    confirmDeleteEmployee({ id: e.target.dataset.id })
  }

  if (e.target.classList.contains('btn-edit')) {
    w.location.assign(`nom_empleados_registrar.php?id=${e.target.dataset.id}`)
  }
})

export { loadTable }
