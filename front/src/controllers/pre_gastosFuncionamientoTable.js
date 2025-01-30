import { getPartidas } from '../api/partidas.js'
import { getGastos, getTiposGastos } from '../api/pre_gastos.js'

import { separarMiles, tableLanguage } from '../helpers/helpers.js'

const d = document
const w = window

let gastosTable, tipoGastosTable
export async function validateGastosTable(id_ejercicio) {
  gastosTable = new DataTable('#gastos-table', {
    columns: [
      { data: 'compromiso' },

      { data: 'tipo' },
      { data: 'monto' },
      { data: 'fecha' },
      { data: 'estado' },
      { data: 'acciones' },
    ],
    responsive: true,
    scrollY: 300,
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

  loadGastosTable(id_ejercicio)
}

export async function validateTiposGastosTable() {
  tipoGastosTable = new DataTable('#tipos-gastos-table', {
    columns: [
      { data: 'nombre' },

      { data: 'acciones' },
      // { data: 'partida_descripcion' },
    ],
    responsive: true,
    scrollY: 300,
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

  loadTipoGastosTable()
}

export async function loadGastosTable({ id_ejercicio }) {
  let gastos = await getGastos(id_ejercicio)

  console.log(gastos)

  if (!Array.isArray(gastos)) return

  if (!gastos || gastos.error) return

  let datosOrdenados = [...gastos].sort((a, b) => a.id - b.id)

  let data = datosOrdenados.map((gastos) => {
    // let sector_programa_proyecto = `${
    //   el.sector_informacion ? el.sector_informacion.sector : '0'
    // }.${el.programa_informacion ? el.programa_informacion.programa : '0'}.${
    //   el.proyecto_informacion == 0 ? '00' : el.proyecto_informacion.proyecto
    // }.${el.id_actividad == 0 ? '00' : el.id_actividad}`

    return {
      compromiso: gastos.correlativo || 'Pendiente',

      tipo: gastos.nombre_tipo_gasto,
      monto: `${separarMiles(gastos.monto_gasto)} Bs`,
      fecha: gastos.fecha,
      estado:
        Number(gastos.status_gasto) === 0
          ? ` <span class='btn btn-sm btn-secondary'>Pendiente</span>`
          : Number(gastos.status_gasto) === 1
          ? `<span class='btn btn-sm btn-success'>Procesado</span>`
          : `<span class='btn btn-sm btn-danger'>Rechazado</span>`,
      acciones: `<button class="btn btn-info btn-sm btn-detail" data-detallesid="${gastos.id}"></button>`,
    }
  })
  //

  // <button class="btn btn-danger btn-sm" data-rechazarid="${gastos.id}">Rechazar</button>
  // <button class="btn btn-info btn-sm" data-aceptarid="${gastos.id}">Aceptar</button>

  gastosTable.clear().draw()

  // console.log(datosOrdenados)
  gastosTable.rows.add(data).draw()
}

export async function loadTipoGastosTable() {
  let tipoGastos = await getTiposGastos()

  if (!Array.isArray(tipoGastos)) return

  if (!tipoGastos || tipoGastos.error) return

  console.log(tipoGastos)

  let datosOrdenados = [...tipoGastos].sort((a, b) => a.id - b.id)

  let data = datosOrdenados.map((gastos) => {
    return {
      nombre: gastos.nombre,

      acciones: `<button class="btn btn-danger btn-sm" data-eliminarid="${gastos.id}">Eliminar</button>`,
      // partida_descripcion: partidaEncontrada.descripcion,
    }
  })

  tipoGastosTable.clear().draw()

  // console.log(datosOrdenados)
  tipoGastosTable.rows.add(data).draw()
}

export async function deleteGasto({ id, row }) {
  gastosTable.row(row).remove().draw()
}

export async function deleteTipoGasto({ id, row }) {
  tipoGastosTable.row(row).remove().draw()
}
