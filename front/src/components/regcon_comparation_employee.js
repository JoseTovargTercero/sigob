import { getRegConEmployeeData } from '../api/empleados.js'
import { empleadosDiferencia } from '../helpers/helpers.js'

const d = document
let requestInfo

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

export async function nom_comparation_employee({
  actual,
  anterior,
  obtenerEmpleado,
  elementToInsert,
}) {
  let { empleadosEliminados, empleadosNuevos } = empleadosDiferencia(
    anterior,
    actual
  )

  let cardElement = d.getElementById('nom-comparation-employee')
  if (cardElement) cardElement.remove()

  // if (!empleadosEliminados.length && !empleadosNuevos.length) return false

  let card = ` <div class='card rounded row mx-0 justify-content-center' id="nom-comparation-employee">
      <div class='row gap-2 mx-0 request-list-container'>
        <div class='col mb-2'>
          <div class='card-header py-2 pb-2'>
            <h5 class='card-title mb-0 text-center'>
              Empleados eliminados de nomina
            </h5>
            <small class='d-block mt-0 text-center text-muted'>
              Visualice los empleados eliminados con respecto a la nomina
              anterior
            </small>
          </div>
          <table
            id='peticion-empleados-eliminados'
            class='table table-xs table-striped'
            style='width:100%;'
          >
            <thead class='w-100'>
              <th>NOMBRES</th>
              <th>CEDULA</th>
              <th>DEPENDENCIA</th>
            </thead>
            <tbody></tbody>
          </table>
        </div>
        <div class='col mb-2'>
          <div class='card-header py-2 pb-2'>
            <h5 class='card-title mb-0 text-center'>
              Empleados nuevos en nomina
            </h5>
            <small class='d-block mt-0 text-center text-muted'>
              Visualice los empleados nuevos con respecto a la nomina anterior
            </small>
          </div>
          <table
            id='peticion-empleados-nuevos'
            class='table table-xs table-striped'
            style='width:100%'
          >
            <thead class='w-100'>
              <th>NOMBRES</th>
              <th>CEDULA</th>
              <th>DEPENDENCIA</th>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', card)

  let empleadosEliminadosTabla = new DataTable(
    '#peticion-empleados-eliminados',
    {
      columns: [
        { data: 'nombres' },
        { data: 'cedula' },
        { data: 'dependencia' },
      ],
      responsive: true,
      scrollY: 100,
      language: tableLanguage,
      layout: {
        topEnd: function () {
          let toolbar = document.createElement('div')
          toolbar.innerHTML = `
        `

          return toolbar
        },
        topStart: { search: { placeholder: 'Buscar...' } },
        bottomStart: 'info',
        bottomEnd: 'paging',
      },
    }
  )

  let empleadosNuevosTabla = new DataTable('#peticion-empleados-nuevos', {
    columns: [{ data: 'nombres' }, { data: 'cedula' }, { data: 'dependencia' }],
    responsive: true,
    scrollY: 100,
    language: tableLanguage,
    layout: {
      topEnd: function () {
        let toolbar = document.createElement('div')
        toolbar.innerHTML = `
        `

        return toolbar
      },
      topStart: { search: { placeholder: 'Buscar...' } },
      bottomStart: 'info',
      bottomEnd: 'paging',
    },
  })

  // obtener datos

  let empleadosEliminadosInformacion =
    empleadosEliminados.length !== 0 &&
    (await Promise.all(empleadosEliminados.map((id) => obtenerEmpleado(id))))

  let empleadosNuevosInformacion =
    empleadosNuevos.length !== 0 &&
    (await Promise.all(empleadosNuevos.map((id) => obtenerEmpleado(id))))

  // let empleadosEliminadosInformacionPeticion = await Promise.all(
  //   empleadosEliminadosInformacion
  // )

  // let empleadosNuevosInformacionPeticion = await Promise.all(
  //   empleadosNuevosInformacion
  // )

  if (empleadosEliminadosInformacion) {
    let datosOrdenados = [...empleadosEliminadosInformacion].sort(
      (a, b) => a.id - b.id
    )

    let dataEliminados = datosOrdenados.map((empleado) => {
      console.log(empleado)
      return {
        nombres: empleado.nombres,
        cedula: empleado.cedula,
        dependencia: empleado.dependencia,
      }
    })

    empleadosEliminadosTabla.clear().draw()

    // console.log(datosOrdenados)
    empleadosEliminadosTabla.rows.add(dataEliminados).draw()
  }

  if (empleadosNuevosInformacion) {
    let datosOrdenados = [...empleadosNuevosInformacion].sort(
      (a, b) => a.id - b.id
    )

    let dataNuevos = datosOrdenados.map((empleado) => {
      return {
        nombres: empleado.nombres,
        cedula: empleado.cedula,
        dependencia: empleado.dependencia,
      }
    })

    console.log(dataNuevos)

    empleadosNuevosTabla.clear().draw()

    // console.log(datosOrdenados)
    empleadosNuevosTabla.rows.add(dataNuevos).draw()
  }

  // let thNuevos = Object.keys(empleadosNuevosInformacion[0]).map((column) => {
  //   return `<th>${column}</th>`
  // })
}
