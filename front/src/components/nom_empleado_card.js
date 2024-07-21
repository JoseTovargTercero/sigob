import {
  getDependencyData,
  getEmployeeData,
  getJobData,
  getProfessionData,
} from '../api/empleados.js'

export async function employeeCard({ id, elementToInsert }) {
  const container = document.getElementById(elementToInsert)

  let employeedeData = await getEmployeeData(id)

  //   let dataExample = {
  //     id_empleado: 48,
  //     cedula: '454512154',
  //     nombres: 'jesusajdkajsd',
  //     tipo_nomina: 0,
  //     id_dependencia: 12,
  //     dependencia: 'qwedsa',
  //     nacionalidad: 'E',
  //     cod_empleado: null,
  //     fecha_ingreso: '2024-05-19',
  //     otros_años: 2,
  //     status: '',
  //     observacion: null,
  //     cod_cargo: '35123',
  //     banco: 'VENEZUELA',
  //     cuenta_bancaria: '21312431',
  //     hijos: 2,
  //     instruccion_academica: 3,
  //     discapacidades: 0,
  //     tipo_cuenta: 0,
  //   }

  let id_empleado = employeedeData.id_empleado,
    nombres = employeedeData.nombres,
    cedula = employeedeData.cedula,
    nacionalidad = employeedeData.nacionalidad,
    fecha_ingreso = employeedeData.fecha_ingreso,
    otros_años = employeedeData.otros_años,
    status = employeedeData.status,
    banco = employeedeData.banco,
    cuenta_bancaria = employeedeData.cuenta_bancaria,
    hijos = employeedeData.hijos,
    instruccion_academica = employeedeData.instruccion_academica,
    cargo = employeedeData.cod_cargo,
    dependencia = employeedeData.dependencia,
    id_dependencia = employeedeData.id_dependencia,
    discapacidades = employeedeData.discapacidades,
    tipo_nomina = employeedeData.tipo_nomina,
    observacion = employeedeData.observacion

  const getCargo = async () => {
    let cargos = await getJobData()
    let cargoEncontrado = cargos.find((el) => el.id == cargo)
    return cargoEncontrado ? cargoEncontrado.name : 'Cargo no disponible'
  }

  const getIntrusccionAcademica = async () => {
    let profesiones = await getProfessionData()
    let profesionEncontrada = profesiones.find(
      (el) => el.id == instruccion_academica
    )
    return profesionEncontrada
      ? profesionEncontrada.name
      : 'Profesión no disponible'
  }

  // const getDependencia = async () => {
  //   let dependencias = await getDependencyData()
  //   console.log()

  //   return dependencias.find((el) => el.id == dependencia)[0].name
  // }

  const calcularAniosLaborales = (fechaIngreso, otrosAnios) => {
    // Crear objetos Date para la fecha de ingreso y la fecha actual
    let fechaIngresoObj = new Date(fechaIngreso)
    let fechaActual = new Date()

    // Calcular la diferencia en milisegundos entre las dos fechas
    let diferenciaMilisegundos = fechaActual - fechaIngresoObj

    // Convertir la diferencia de milisegundos a años y meses
    let aniosDiferencia = Math.floor(diferenciaMilisegundos / 31536000000) // 1000 * 60 * 60 * 24 * 365.25
    let mesesDiferencia = Math.floor(
      (diferenciaMilisegundos % 31536000000) / 2628000000
    ) // 1000 * 60 * 60 * 24 * 30.44

    // Generar el texto de salida
    let textoSalida = `${aniosDiferencia + otrosAnios} años ${
      mesesDiferencia && 'y'
    } ${mesesDiferencia || ''} meses.`

    return textoSalida
  }

  let employeeCardElement = `
    <div class='modal-window slide-up-animation' id='modal-employee'>
      <div class='modal-box card w-90 h-80 overflow-auto'>
        <div class='row'>
          <div class='modal-box-header'>
            <h2 class='card-title'>Perfil de Empleado</h2>
            <button
              id='btn-close-employee-card'
              type='button'
              class='btn btn-danger'
              aria-label='Close'
            >
              &times;
            </button>
          </div>
        </div>

        <div class='card-body'>
          <div class='row'>
            <div class='col'>
              <h3>${nombres}</h3>
              <p>Cargo: ${await getCargo()}</p>
              <p>Fecha de Ingreso: ${fecha_ingreso}</p>
              <p>Cédula: ${cedula}</p>
              <p>
                Nacionalidad: ${
                  nacionalidad === 'V' ? 'Venezolano' : 'Extranjero'
                }
              </p>
            </div>
            <div class='col-md-6'>
              <h4>Información Personal</h4>
              <p>Hijos: ${hijos} hijo/as</p>
              <p>Educación: ${await getIntrusccionAcademica()} </p>
              <p>
                Discapacidad: ${discapacidades === 0 ? 'No posee' : 'Si posee'}
              </p>
            </div>
          </div>
          <div class='row'>
            <div class='col-md-6'>
              <h4>Información Laboral</h4>
              <p>
                Experiencia laboral: ${calcularAniosLaborales(
                  fecha_ingreso,
                  otros_años
                )}
              </p>
              <p>Dependencia laboral: ${dependencia}</p>
              <p>
                Banco: ${banco} - ${cuenta_bancaria}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  `

  container.insertAdjacentHTML('beforeend', employeeCardElement)
  return
}

{
  /* <div class='card-footer text-center'>
<button class='btn btn-secondary'>Guardar</button>
<button class='btn btn-info'>Imprimir</button>
</div> */
}
