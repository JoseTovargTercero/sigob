import { deleteEmployee, getEmployeesData } from '../api/empleados.js'
import { confirmNotification } from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

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
    topStart: { search: { placeholder: 'Buscar...' } },
    top2Start: function () {
      let toolbar = document.createElement('div')
      toolbar.innerHTML = `<a class="btn btn-primary"
      href="nom_empleados_registrar">Registrar Personal</a>`
      return toolbar
    },
    topEnd: 'pageLength',
    bottomStart: 'info',
    bottomEnd: 'paging',
  },
  columns: [
    { data: 'nombres' },
    { data: 'cedula' },
    { data: 'dependencia' },
    { data: 'nomina' },
    { data: 'acciones' },
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
      <button class="btn btn-info btn-sm btn-view" data-empleado-id=${empleado.id_empleado}>MOSTRAR</button>
      <button class="btn btn-warning btn-sm btn-edit" data-empleado-id=${empleado.id_empleado}>EDITAR</button>
      <button class="btn btn-danger btn-sm btn-delete" data-empleado-id=${empleado.id_empleado}>ELIMINAR</button>`,
    }
  })

  employeeTable.rows.add(data).draw()
}

export const confirmDeleteEmployee = ({ id }) => {
  confirmNotification({
    type: NOTIFICATIONS_TYPES.delete,
    successFunction: deleteEmployee,
    successFunctionParams: id,
  }).then((res) => {
    if (res) {
      console.log(e.target)
    }
  })

  var myTable = new DataTable('#myTable')

  // PARA CANCELAAAAR
  // $('#myTable').on('click', 'tbody tr', function () {
  //   myTable.row(this).delete({
  //     buttons: [
  //       {
  //         label: 'Cancel',
  //         fn: function () {
  //           this.close()
  //         },
  //       },
  //       'Delete',
  //     ],
  //   })
  // })
}

export { loadTable }
