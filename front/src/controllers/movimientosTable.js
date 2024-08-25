const d = document
import {
  getMovimiento,
  getMovimientos,
  getRegConMovimientos,
} from '../api/movimientos.js'
import { movimientoCard } from '../components/movimientoCard.js'
import {
  closeModal,
  confirmNotification,
  openModal,
  toastNotification,
  validateModal,
} from '../helpers/helpers.js'
import { NOTIFICATIONS_TYPES } from '../helpers/types.js'

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
let movimientosTableRegCon

export const loadMovimientosTable = async ({ id_nomina, elementToInsert }) => {
  let cardElement = d.getElementById('movimientos-table')
  if (cardElement) cardElement.remove()

  let tableHtml = `  <div class='card rounded' id='movimientos-table'>
      <div class='card-header py-2 pb-2'>
        <h5 class='card-title mb-0 text-center'>Movimientos</h5>
        <small class='d-block mt-0 text-center text-muted'>
          Visualice movimientos realizados a los empleados de está petición:
        </small>
      </div>
      <div class='row mx-0'>
        <table
          id='movimientos-table-regcon'
          class='table table-xs table-striped'
          style='width:100%;'
        >
          <thead>
            <th>id_empleado</th>
            <th>NOMBRES</th>
            <th>cedula</th>
            <th>accion</th>
            <th>campo</th>
            <th>valor_anterior</th>
            <th>valor_nuevo</th>
            <th>fecha_movimiento</th>
            <th>usuario</th>
            <th>descripción</th>
            <th>acciones</th>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>`

  d.getElementById(elementToInsert).insertAdjacentHTML('afterbegin', tableHtml)

  let movimientosRegConElement = d.getElementById('movimientos-table-regcon')
  let movimientosNominaElement = d.getElementById('movimientos-table-nomina')

  // if (movimientosNominaElement) {
  //   let employeeTable = new DataTable('#movimientos-table-nomina', {
  //     responsive: true,
  //     scrollY: 300,
  //     language: tableLanguage,
  //     layout: {
  //       topEnd: employeeFormButton,
  //       topStart: { search: { placeholder: 'Buscar...' } },
  //       bottomStart: 'info',
  //       bottomEnd: 'paging',
  //     },
  //     columns: [
  //       { data: 'nombres' },
  //       { data: 'cedula' },
  //       { data: 'dependencia' },
  //       { data: 'nomina' },
  //       { data: 'acciones' },
  //     ],
  //   })
  // }

  if (movimientosRegConElement) {
    console.log('cargando movimientos')
    movimientosTableRegCon = new DataTable('#movimientos-table-regcon', {
      responsive: true,
      scrollY: 200,
      language: tableLanguage,
      layout: {
        topEnd: '',
        topStart: { search: { placeholder: 'Buscar...' } },
        bottomStart: 'info',
        bottomEnd: 'paging',
      },
      columns: [
        { data: 'id_empleado' },
        { data: 'nombres' },
        { data: 'cedula' },
        { data: 'accion' },
        { data: 'campo' },
        { data: 'valor_anterior' },
        { data: 'valor_nuevo' },
        { data: 'fecha_movimiento' },
        { data: 'usuario' },
        { data: 'descripcion' },
        { data: 'acciones' },
      ],
    })

    movimientosTableRegCon.clear().draw()

    let movimientos = await getRegConMovimientos({ id_nomina })

    if (!Array.isArray(movimientos)) return

    let movimientosOrdenados = [...movimientos].sort((a, b) => b.id - a.id)

    let data = movimientosOrdenados.map((movimiento) => {
      return {
        id_empleado: movimiento.id_empleado,
        nombres: movimiento.nombres,
        cedula: movimiento.cedula,
        accion: movimiento.accion,
        campo: movimiento.campo,
        valor_anterior: movimiento.valor_anterior,
        valor_nuevo: movimiento.valor_nuevo,
        fecha_movimiento: movimiento.fecha_movimiento,
        usuario: movimiento.usuario_id,
        descripcion: movimiento.descripcion,
        acciones: `
        <button class="btn btn-info btn-sm btn-corregir" data-id="${movimiento.id}" data-nominaid="${movimiento.id_nomina}"><i class="bx bx-detail me-1"></i>Corregir</button>`,
      }
    })

    // AÑADIR FILAS A TABLAS
    movimientosTableRegCon.rows.add(data).draw()
  }
}

export const deleteRowMovimiento = (row) => {
  let filteredRows = movimientosTableRegCon.rows(function (idx, data, node) {
    return node === row
  })
  filteredRows.remove().draw()
}

export const reloadTableMovimientos = async ({ id_nomina }) => {
  movimientosTableRegCon.clear().draw()

  let movimientos = await getRegConMovimientos({ id_nomina })

  if (!Array.isArray(movimientos)) return

  toastNotification({
    type: NOTIFICATIONS_TYPES.done,
    message:
      'Se han borrado las correciones y recargado la tabla de movimientos',
  })

  let movimientosOrdenados = [...movimientos].sort((a, b) => b.id - a.id)

  let data = movimientosOrdenados.map((movimiento) => {
    return {
      id_empleado: movimiento.id_empleado,
      nombres: movimiento.nombres,
      cedula: movimiento.cedula,
      accion: movimiento.accion,
      campo: movimiento.campo,
      valor_anterior: movimiento.valor_anterior,
      valor_nuevo: movimiento.valor_nuevo,
      fecha_movimiento: movimiento.fecha_movimiento,
      usuario: movimiento.usuario_id,
      descripcion: movimiento.descripcion,
      acciones: `
      <button class="btn btn-info btn-sm btn-corregir" data-id="${movimiento.id}" data-nominaid="${movimiento.id_nomina}"><i class="bx bx-detail me-1"></i>Corregir</button>`,
    }
  })

  // AÑADIR FILAS A TABLAS
  movimientosTableRegCon.rows.add(data).draw()
}
