import { getRegConEmployeeData } from '../api/empleados.js'
import { empleadosDiferencia } from '../helpers/helpers.js'

export async function nom_comparation_employee({ actual, anterior }) {
  let { empleadosEliminados, empleadosNuevos } = empleadosDiferencia(
    anterior,
    actual
  )

  if (!empleadosEliminados.length && !empleadosNuevos.length) return false

  let empleadosEliminadosInformacion =
    empleadosEliminados.length !== 0 &&
    (await Promise.all(
      empleadosEliminados.map((id) => getRegConEmployeeData(id))
    ))

  let empleadosNuevosInformacion =
    empleadosNuevos.length !== 0 &&
    (await Promise.all(empleadosNuevos.map((id) => getRegConEmployeeData(id))))

  let tablaEliminados
  let tablaNuevos
  // let empleadosEliminadosInformacionPeticion = await Promise.all(
  //   empleadosEliminadosInformacion
  // )

  // let empleadosNuevosInformacionPeticion = await Promise.all(
  //   empleadosNuevosInformacion
  // )

  if (empleadosEliminadosInformacion) {
    let columnas = Object.keys(empleadosEliminadosInformacion[0]).map(
      (column) => {
        return `<th>${column}</th>`
      }
    )

    let filas = empleadosEliminadosInformacion.map((row) => {
      if (!row) return false
      let td = ''

      Object.values(row).forEach((el) => {
        td += `<td>${el}</td>`
      })

      return `<tr>${td}</tr>`
    })

    tablaEliminados = `<table  class='table table-xs table-responsive mx-auto'
    style='width: 100%'>
        <thead>${columnas.join('')}</thead>
        <tbody>${filas.join('')}</tbody>
      </table>`
  }

  // let thNuevos = Object.keys(empleadosNuevosInformacion[0]).map((column) => {
  //   return `<th>${column}</th>`
  // })

  console.log(tablaEliminados)

  return `<div class='card size-change-animation w-75 mx-auto' id="table-list-card">
  <div class='card-header py-2'>
    <div class='d-flex align-items-center justify-content-between'>
      <div>
        <h5 class='mb-0'>Petición de nómina a pagar</h5>
      </div>
      <button class='btn btn-danger' id='close-request-list'>
        Cerrar
      </button>
    </div>
  </div>
  <div class="card-body">
 ${tablaEliminados}
  </div>
</div>`
}
