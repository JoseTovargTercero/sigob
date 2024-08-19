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

export const loadMovimientosTable = async () => {
  let movimientosRegConElement = d.getElementById('movimientos-table-regcon')
  let movimientosNominaElement = d.getElementById('movimientos-table-nomina')

  if (movimientosNominaElement) {
    let employeeTable = new DataTable('#movimientos-table-nomina', {
      responsive: true,
      scrollY: 300,
      language: tableLanguage,
      layout: {
        topEnd: employeeFormButton,
        topStart: { search: { placeholder: 'Buscar...' } },
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
  }

  if (movimientosRegConElement) {
    console.log('cargando movimientos')
    movimientosTableRegCon = new DataTable('#movimientos-table-regcon', {
      responsive: true,
      scrollY: 300,
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

    let movimientos = await getRegConMovimientos()

    console.log(movimientos)

    let movimientosOrdenados = [...movimientos].sort((a, b) => b.id - a.id)

    let data = movimientosOrdenados.map((movimiento) => {
      return {
        id_empleado: movimiento.id_empleado,
        nombres: 'NOMBRES',
        cedula: 'CEDULA',
        accion: movimiento.accion,
        campo: movimiento.campo,
        valor_anterior: movimiento.valor_anterior,
        valor_nuevo: movimiento.valor_nuevo,
        fecha_movimiento: movimiento.fecha_movimiento,
        usuario: 'USUARIO',
        descripcion: movimiento.descripcion,
        acciones: `
        <button class="btn btn-info btn-sm btn-corregir" data-id="${movimiento.id}"><i class="bx bx-detail me-1"></i>Corregir</button>`,
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
