function validateUrl() {
  let url = new URL(window.location.href)
  let protocol = url.protocol
  let host = url.host
  let pathname = url.pathname

  return `${protocol}//${host}/`
}

const isLocalhost = () => {
  return window.location.href.includes('localhost')
}

const config = {
  BASE_URL: validateUrl(),
  APP_NAME: isLocalhost() ? 'sigob/' : '',
  DIR: 'back/',
  DECRETOS: 'decretos/',
  MODULE_NAMES: {
    ENTES: 'modulo_entes/',
    GLOBAL: 'sistema_global/',
    FORMULACION: 'modulo_pl_formulacion/',
    PROYECTOS: 'modulo_proyectos/',
    EJECUCION: 'modulo_ejecucion_presupuestaria/',
    NOMINA: 'modulo_nomina/',
    REGISTRO_CONTROL: 'modulo_registro_control/',
    RELACIONES_LABORALES: 'modulo_relaciones_laborales/',
  },
}

const APP_URL = `${config.BASE_URL}${config.APP_NAME}${config.DIR}`

const DECRETOS_URL = `${config.BASE_URL}${config.APP_NAME}${config.DECRETOS}`

export { APP_URL, config, DECRETOS_URL }
