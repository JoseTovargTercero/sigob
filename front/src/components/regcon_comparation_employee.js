import { getRegConEmployeeData } from '../api/empleados.js'
import { empleadosDiferencia } from '../helpers/helpers.js'

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
}) {
  let { empleadosEliminados, empleadosNuevos } = empleadosDiferencia(
    anterior,
    actual
  )

  if (!empleadosEliminados.length && !empleadosNuevos.length) return false

  let empleadosEliminadosTabla = new DataTable(
    '#peticion-empleados-eliminados',
    {
      columns: [
        { data: 'nombres' },
        { data: 'cedula' },
        { data: 'dependencia' },
      ],
      responsive: true,
      scrollY: 250,
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
    scrollY: 250,
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
    console.log(datosOrdenados)

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

    empleadosNuevosTabla.clear().draw()

    // console.log(datosOrdenados)
    empleadosNuevosTabla.rows.add(dataNuevos).draw()
  }

  // let thNuevos = Object.keys(empleadosNuevosInformacion[0]).map((column) => {
  //   return `<th>${column}</th>`
  // })
}
