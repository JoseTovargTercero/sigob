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

  let id_empleado = employeedeData[0].id_empleado,
    nombres = employeedeData[0].nombres,
    cedula = employeedeData[0].cedula,
    nacionalidad = employeedeData[0].nacionalidad,
    fecha_ingreso = employeedeData[0].fecha_ingreso,
    otros_años = employeedeData[0].otros_años,
    status = employeedeData[0].status,
    banco = employeedeData[0].banco,
    cuenta_bancaria = employeedeData[0].cuenta_bancaria,
    hijos = employeedeData[0].hijos,
    instruccion_academica = employeedeData[0].instruccion_academica,
    cargo = employeedeData[0].cod_cargo,
    dependencia = employeedeData[0].dependencia,
    id_dependencia = employeedeData[0].id_dependencia,
    discapacidades = employeedeData[0].discapacidades,
    tipo_cuenta = employeedeData[0].banco,
    tipo_nomina = employeedeData[0].tipo_nomina,
    observacion = employeedeData[0].observacion

  const getCargo = async () => {
    let cargos = await getJobData()

    return cargos.filter((el) => el.id === cargo)[0].name
  }

  const getIntrusccionAcademica = async () => {
    let profesiones = await getProfessionData()
    return profesiones.filter((el) => el.id == instruccion_academica)[0].name
  }

  // const getDependencia = async () => {
  //   let dependencias = await getDependencyData()
  //   console.log()

  //   return dependencias.filter((el) => el.id == dependencia)[0].name
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
                Banco: ${banco} - ${tipo_cuenta === 0 ? 'Correiente' : 'Ahorro'}
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
